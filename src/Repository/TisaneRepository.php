<?php

namespace App\Repository;

use App\Entity\Tisane;
use App\Entity\AccordAromatique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tisane>
 *
 * @method Tisane|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tisane|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tisane[]    findAll()
 * @method Tisane[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TisaneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tisane::class);
    }

    /**
     * Requête de base (liste publique) avec recherche simple (LIKE).
     * Retourne un QueryBuilder pour que KNP pagine en SQL.
     */
    public function queryIndex(string $q = ''): QueryBuilder
    {
        $qb = $this->createQueryBuilder('t')
            ->select('DISTINCT t')
            ->leftJoin('t.bienfaits', 'b')  // filtrage sur b.nom
            ->leftJoin('t.plantes',  'p')  // filtrage sur p.nomCommun
            ->orderBy('t.nom', 'ASC');

        $q = trim($q);
        if ($q !== '') {
            $qb->andWhere(
                'LOWER(t.nom) LIKE :q
                 OR LOWER(t.modePreparation) LIKE :q
                 OR LOWER(b.nom) LIKE :q
                 OR LOWER(p.nomCommun) LIKE :q'
            )->setParameter('q', '%'.mb_strtolower($q, 'UTF-8').'%');
        }

        return $qb;
    }

    /**
     * Recherche approximative tolérante aux fautes :
     * 1) Préfiltre SQL (LIKE) sur nom / modePreparation / bienfaits / plantes (multi tokens)
     * 2) Reclassement PHP (similar_text + levenshtein), avec seuil minimum.
     *
     * @return Tisane[] Reclassées par pertinence.
     */
    public function fuzzySearch(string $search, int $limitCandidates = 400, int $minScore = 18): array
    {
        $normalized = $this->normalize($search);
        $tokens     = preg_split('/\s+/', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        // 1) Préfiltre SQL large
        $qb = $this->createQueryBuilder('t')
            ->select('DISTINCT t')
            ->leftJoin('t.bienfaits', 'b')
            ->leftJoin('t.plantes',   'p');

        if (!empty($tokens)) {
            $or = $qb->expr()->orX();
            $i  = 0;
            foreach ($tokens as $tok) {
                $param = ':t'.$i++;
                $or->add($qb->expr()->like('LOWER(t.nom)', $param));
                $or->add($qb->expr()->like('LOWER(t.modePreparation)', $param));
                $or->add($qb->expr()->like('LOWER(b.nom)', $param));
                $or->add($qb->expr()->like('LOWER(p.nomCommun)', $param));
                $qb->setParameter(substr($param, 1), '%'.$tok.'%');
            }
            $qb->andWhere($or);
        }

        $qb->orderBy('t.nom', 'ASC')
            ->setMaxResults($limitCandidates);

        /** @var Tisane[] $candidats */
        $candidats = $qb->getQuery()->getResult();

        // 2) Reclassement PHP (tolérance fautes)
        $scored = [];
        foreach ($candidats as $tisane) {
            $nom  = $this->normalize((string) $tisane->getNom());
            $prep = $this->normalize(strip_tags((string) $tisane->getModePreparation()));

            // Concat noms des plantes & bienfaits (lazy-load ok pour ce volume)
            $plantesStr = '';
            foreach ($tisane->getPlantes() as $pl) {
                $plantesStr .= ' '.$this->normalize((string) (method_exists($pl, 'getNomCommun') ? $pl->getNomCommun() : ''));
                if (method_exists($pl, 'getNomScientifique')) {
                    $plantesStr .= ' '.$this->normalize((string) $pl->getNomScientifique());
                }
            }

            $bienfaitsStr = '';
            foreach ($tisane->getBienfaits() as $b) {
                $bienfaitsStr .= ' '.$this->normalize((string) (method_exists($b, 'getNom') ? $b->getNom() : ''));
            }

            $scoreNom   = $this->similarity($normalized, $nom);
            $scorePrep  = $this->similarity($normalized, $prep);
            $scorePlant = $this->similarity($normalized, $plantesStr);
            $scoreBf    = $this->similarity($normalized, $bienfaitsStr);
            $score      = max($scoreNom, $scorePrep, $scorePlant, $scoreBf);

            // Bonus par token trouvé (ou proche)
            $haystack = $nom.' '.$prep.' '.$plantesStr.' '.$bienfaitsStr;
            foreach ($tokens as $tok) {
                if ($tok === '') { continue; }
                if (str_contains($haystack, $tok)) {
                    $score += 10;
                } elseif (mb_strlen($tok, 'UTF-8') >= 4) {
                    $closest = $this->closestWord($haystack, $tok);
                    $dist    = levenshtein($tok, $closest);
                    if ($dist <= 2) { $score += 6; }
                }
            }

            $score = min(100.0, (float) $score);
            if ($score >= $minScore) {
                $scored[] = [$tisane, $score];
            }
        }

        usort($scored, static function (array $a, array $b): int {
            $cmp = $b[1] <=> $a[1]; // score desc
            if ($cmp !== 0) { return $cmp; }
            return strcmp((string) $a[0]->getNom(), (string) $b[0]->getNom());
        });

        return array_map(static fn(array $row) => $row[0], $scored);
    }

    /**
     * Classement des tisanes par score (= wB * nbBienfaits + wA * sommeScoresAromatiques).
     *
     * @param int[]    $bienfaitIds   Bienfaits qui soulagent les gènes des ingrédients
     * @param int[]    $ingredientIds IDs des ingrédients de la recette
     * @param string[] $types         Types d’ingrédients (ex: "Poisson", "Volaille")
     *
     * @return array<int, array{tisane: Tisane, scoreB: int, scoreA: float, scoreTotal: float}>
     */
    public function findSuggestions(
        array $bienfaitIds,
        array $ingredientIds,
        array $types,
        int $limit = 3,
        float $wB = 2.0,
        float $wA = 1.0
    ): array {
        // Dummies pour éviter IN () vide
        $ingredientIds = !empty($ingredientIds) ? array_values(array_unique(array_map('intval', $ingredientIds))) : [0];
        $types         = !empty($types) ? array_values(array_unique($types)) : ['__NONE__'];

        $qb = $this->createQueryBuilder('t')
            ->select('t AS tisane')
            ->leftJoin('t.plantes', 'p')
            // Bienfaits limités si on en a, sinon join "vide" afin que COUNT = 0
            ->leftJoin('t.bienfaits', 'b', 'WITH', !empty($bienfaitIds) ? 'b.id IN (:bids)' : '1=0')
            // Accords aromatiques: (plante -> accord)
            ->leftJoin(AccordAromatique::class, 'ap', 'WITH', 'ap.plante = p')
            // Scores
            ->addSelect('COUNT(DISTINCT b.id) AS scoreB')
            ->addSelect('COALESCE(SUM(
                CASE
                    WHEN ap.ingredient IS NOT NULL AND IDENTITY(ap.ingredient) IN (:ingIds) THEN ap.score
                    WHEN ap.ingredientType IS NOT NULL AND ap.ingredientType IN (:types) THEN ap.score
                    ELSE 0
                END
            ), 0) AS scoreA')
            ->addSelect('(:wB * COUNT(DISTINCT b.id) + :wA * COALESCE(SUM(
                CASE
                    WHEN ap.ingredient IS NOT NULL AND IDENTITY(ap.ingredient) IN (:ingIds) THEN ap.score
                    WHEN ap.ingredientType IS NOT NULL AND ap.ingredientType IN (:types) THEN ap.score
                    ELSE 0
                END
            ), 0)) AS scoreTotal')
            ->setParameter('ingIds', $ingredientIds)
            ->setParameter('types',  $types)
            ->setParameter('wB', $wB)
            ->setParameter('wA', $wA)
            ->groupBy('t.id')
            ->orderBy('scoreTotal', 'DESC')
            ->setMaxResults($limit);

        if (!empty($bienfaitIds)) {
            $qb->setParameter('bids', $bienfaitIds);
        }

        $rows = $qb->getQuery()->getResult();

        // Cast propre des scalaires
        foreach ($rows as &$r) {
            $r['scoreB']     = (int)   $r['scoreB'];
            $r['scoreA']     = (float) $r['scoreA'];
            $r['scoreTotal'] = (float) $r['scoreTotal'];
        }

        return $rows;
    }

    // ---------------- Helpers ----------------

    private function normalize(string $s): string
    {
        $s = trim($s);
        if ($s === '') { return ''; }

        if (class_exists(\Transliterator::class)) {
            $tr = \Transliterator::create('Any-Latin; Latin-ASCII; Lower()');
            $s  = $tr ? $tr->transliterate($s) : mb_strtolower($s, 'UTF-8');
        } else {
            $s = mb_strtolower($s, 'UTF-8');
            $conv = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
            if ($conv !== false) { $s = $conv; }
        }

        $s = preg_replace('/[^a-z0-9\s]+/u', ' ', $s) ?? $s;
        $s = preg_replace('/\s+/u', ' ', $s) ?? $s;
        return trim($s);
    }

    private function similarity(string $a, string $b): float
    {
        if ($a === '' || $b === '') { return 0.0; }
        similar_text($a, $b, $percent);
        return (float) $percent;
    }

    private function closestWord(string $haystack, string $needle): string
    {
        $bestWord = '';
        $best = PHP_INT_MAX;
        foreach (preg_split('/\s+/', $haystack, -1, PREG_SPLIT_NO_EMPTY) as $w) {
            $d = levenshtein($needle, $w);
            if ($d < $best) { $best = $d; $bestWord = $w; }
            if ($best === 0) { break; }
        }
        return $bestWord;
    }
}
