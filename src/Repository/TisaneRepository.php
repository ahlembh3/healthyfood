<?php

namespace App\Repository;

use App\Entity\Tisane;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TisaneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tisane::class);
    }

    /**
     * Requête de base (liste publique) avec recherche simple (LIKE).
     * On retourne un QueryBuilder pour permettre la pagination SQL via KNP.
     */
    public function queryIndex(string $q = '')
    {
        $qb = $this->createQueryBuilder('t')
            ->select('DISTINCT t')
            ->leftJoin('t.bienfaits', 'b')  // pour filtrer sur b.nom
            ->leftJoin('t.plantes', 'p')    // pour filtrer sur p.nomCommun
            ->orderBy('t.nom', 'ASC');

        $q = trim($q);
        if ($q !== '') {
            $qb->andWhere(
                'LOWER(t.nom) LIKE :q
                 OR LOWER(t.modePreparation) LIKE :q
                 OR LOWER(b.nom) LIKE :q
                 OR LOWER(p.nomCommun) LIKE :q'
            )->setParameter('q', '%'.mb_strtolower($q).'%');
        }

        return $qb;
    }

    /**
     * Recherche approximative tolérante aux fautes :
     * 1) Préfiltre SQL (LIKE) sur nom / modePreparation / bienfaits / plantes
     * 2) Reclassement PHP (similar_text + levenshtein), avec seuil minimum.
     *
     * @return Tisane[] Reclassées par pertinence.
     */
    public function fuzzySearch(string $search, int $limitCandidates = 400, int $minScore = 18): array
    {
        $normalized = $this->normalize($search);
        $tokens     = preg_split('/\s+/', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        // 1) Préfiltre SQL
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

        // 2) Reclassement PHP
        $scored = [];
        foreach ($candidats as $tisane) {
            $nom   = $this->normalize((string)$tisane->getNom());
            $prep  = $this->normalize(strip_tags((string)$tisane->getModePreparation()));

            // Concat noms des plantes & bienfaits (lazy-load ok)
            $plantesStr = '';
            foreach ($tisane->getPlantes() as $pl) {
                $plantesStr .= ' '.$this->normalize((string)$pl->getNomCommun());
                if (method_exists($pl, 'getNomScientifique')) {
                    $plantesStr .= ' '.$this->normalize((string)$pl->getNomScientifique());
                }
            }

            $bienfaitsStr = '';
            foreach ($tisane->getBienfaits() as $b) {
                $bienfaitsStr .= ' '.$this->normalize((string)$b->getNom());
            }

            $scoreNom   = $this->similarity($normalized, $nom);
            $scorePrep  = $this->similarity($normalized, $prep);
            $scorePlant = $this->similarity($normalized, $plantesStr);
            $scoreBf    = $this->similarity($normalized, $bienfaitsStr);
            $score      = max($scoreNom, $scorePrep, $scorePlant, $scoreBf);

            // Bonus par token trouvé (ou proche)
            $haystack = $nom.' '.$prep.' '.$plantesStr.' '.$bienfaitsStr;
            foreach ($tokens as $tok) {
                if ($tok === '') continue;
                if (str_contains($haystack, $tok)) {
                    $score += 10;
                } elseif (mb_strlen($tok) >= 4) {
                    $closest = $this->closestWord($haystack, $tok);
                    $dist    = levenshtein($tok, $closest);
                    if ($dist <= 2) $score += 6;
                }
            }

            $score = min(100, (float)$score);
            if ($score >= $minScore) {
                $scored[] = [$tisane, $score];
            }
        }

        usort($scored, static function ($a, $b) {
            $cmp = $b[1] <=> $a[1]; // score desc
            if ($cmp !== 0) return $cmp;
            return strcmp((string)$a[0]->getNom(), (string)$b[0]->getNom());
        });

        return array_map(static fn($row) => $row[0], $scored);
    }

    // ---------------- Helpers ----------------

    private function normalize(string $s): string
    {
        $s = trim($s);
        if ($s === '') return '';
        if (class_exists(\Transliterator::class)) {
            $tr = \Transliterator::create('Any-Latin; Latin-ASCII; Lower()');
            $s  = $tr ? $tr->transliterate($s) : mb_strtolower($s, 'UTF-8');
        } else {
            $s = mb_strtolower($s, 'UTF-8');
            $conv = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
            if ($conv !== false) $s = $conv;
        }
        $s = preg_replace('/[^a-z0-9\s]+/u', ' ', $s) ?? $s;
        $s = preg_replace('/\s+/u', ' ', $s) ?? $s;
        return trim($s);
    }

    private function similarity(string $a, string $b): float
    {
        if ($a === '' || $b === '') return 0.0;
        similar_text($a, $b, $percent);
        return (float)$percent;
    }

    private function closestWord(string $haystack, string $needle): string
    {
        $bestWord = '';
        $best = PHP_INT_MAX;
        foreach (preg_split('/\s+/', $haystack, -1, PREG_SPLIT_NO_EMPTY) as $w) {
            $d = levenshtein($needle, $w);
            if ($d < $best) { $best = $d; $bestWord = $w; }
            if ($best === 0) break;
        }
        return $bestWord;
    }
}
