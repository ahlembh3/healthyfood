<?php

namespace App\Repository;

use App\Entity\Recette;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

class RecetteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recette::class);
    }

    /**
     * Dernières recettes validées.
     */
    public function findLatestValidated(int $limit = 3): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.validation = :v')
            ->setParameter('v', true)
            ->orderBy('r.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Requête de base (publiques/validées) avec tous les filtres possibles.
     * Retourne un QueryBuilder pour pouvoir paginer efficacement côté contrôleur.
     *
     * Filtres possibles (tous optionnels) :
     *  - q          : recherche simple (LIKE) sur titre/description
     *  - ingredient : LIKE sur nom d’ingrédient
     *  - type       : égalité (type d’ingrédient)
     *  - saison     : LIKE sur saisonnalité ingrédient
     *  - bienfait   : LIKE sur nom de bienfait (via g.bienfaits)
     */
    public function queryPublicWithFilters(array $filters = []): Query
    {
        $q        = trim((string)($filters['q'] ?? ''));
        $ingName  = trim((string)($filters['ingredient'] ?? ''));
        $type     = trim((string)($filters['type'] ?? ''));
        $saison   = trim((string)($filters['saison'] ?? ''));
        $bienfait = trim((string)($filters['bienfait'] ?? ''));

        $qb = $this->createQueryBuilder('r')
            ->select('DISTINCT r')
            ->leftJoin('r.recetteIngredients', 'ri')
            ->leftJoin('ri.ingredient', 'ing')
            ->leftJoin('ing.genes', 'g')
            ->leftJoin('g.bienfaits', 'bf')
            ->andWhere('r.validation = :valide')
            ->setParameter('valide', true);

        if ($q !== '') {
            $qb->andWhere('(LOWER(r.titre) LIKE :q OR LOWER(r.description) LIKE :q)')
                ->setParameter('q', '%'.mb_strtolower($q).'%');
        }
        if ($ingName !== '') {
            $qb->andWhere('LOWER(ing.nom) LIKE :ingName')
                ->setParameter('ingName', '%'.mb_strtolower($ingName).'%');
        }
        if ($type !== '') {
            $qb->andWhere('LOWER(ing.type) = :type')
                ->setParameter('type', mb_strtolower($type));
        }
        if ($saison !== '') {
            $qb->andWhere('LOWER(ing.saisonnalite) LIKE :saison')
                ->setParameter('saison', '%'.mb_strtolower($saison).'%');
        }
        if ($bienfait !== '') {
            $qb->andWhere('LOWER(bf.nom) LIKE :bf')
                ->setParameter('bf', '%'.mb_strtolower($bienfait).'%');
        }

        $qb->orderBy('r.titre', 'ASC');

        return $qb->getQuery();
    }

    /**
     * Recherche approximative tolérante aux fautes pour les recettes :
     * 1) Préfiltre SQL LIKE (titre/description/nom ingrédient)
     * 2) Reclassement PHP (similar_text + levenshtein), seuil min.
     *
     * Les filtres (ingredient/type/saison/bienfait) restent appliqués.
     *
     * @return Recette[] Reclassées par pertinence.
     */
    public function fuzzySearchPublic(
        string $search,
        array $filters = [],
        int $limitCandidates = 400,
        int $minScore = 18
    ): array {
        $normalized = $this->normalize($search);
        $tokens     = preg_split('/\s+/', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $ingName  = trim((string)($filters['ingredient'] ?? ''));
        $type     = trim((string)($filters['type'] ?? ''));
        $saison   = trim((string)($filters['saison'] ?? ''));
        $bienfait = trim((string)($filters['bienfait'] ?? ''));

        // 1) Préfiltre SQL
        $qb = $this->createQueryBuilder('r')
            ->select('DISTINCT r')
            ->leftJoin('r.recetteIngredients', 'ri')
            ->leftJoin('ri.ingredient', 'ing')
            ->leftJoin('ing.genes', 'g')
            ->leftJoin('g.bienfaits', 'bf')
            ->andWhere('r.validation = :valide')
            ->setParameter('valide', true);

        if (!empty($tokens)) {
            $or = $qb->expr()->orX();
            $i  = 0;
            foreach ($tokens as $tok) {
                $param = ':t'.$i++;
                $or->add($qb->expr()->like('LOWER(r.titre)', $param));
                $or->add($qb->expr()->like('LOWER(r.description)', $param));
                $or->add($qb->expr()->like('LOWER(ing.nom)', $param));
                $qb->setParameter(substr($param, 1), '%'.$tok.'%');
            }
            $qb->andWhere($or);
        }

        // Autres filtres inchangés
        if ($ingName !== '') {
            $qb->andWhere('LOWER(ing.nom) LIKE :ingName')
                ->setParameter('ingName', '%'.mb_strtolower($ingName).'%');
        }
        if ($type !== '') {
            $qb->andWhere('LOWER(ing.type) = :type')
                ->setParameter('type', mb_strtolower($type));
        }
        if ($saison !== '') {
            $qb->andWhere('LOWER(ing.saisonnalite) LIKE :saison')
                ->setParameter('saison', '%'.mb_strtolower($saison).'%');
        }
        if ($bienfait !== '') {
            $qb->andWhere('LOWER(bf.nom) LIKE :bf')
                ->setParameter('bf', '%'.mb_strtolower($bienfait).'%');
        }

        $qb->orderBy('r.titre', 'ASC')
            ->setMaxResults($limitCandidates);

        /** @var Recette[] $candidats */
        $candidats = $qb->getQuery()->getResult();

        // 2) Reclassement PHP
        $scored = [];
        foreach ($candidats as $recette) {
            $titre = $this->normalize((string)$recette->getTitre());
            $desc  = $this->normalize((string)$recette->getDescription());

            // concatène les noms d’ingrédient
            $ingStr = '';
            foreach ($recette->getRecetteIngredients() as $ri) {
                $ing = $ri->getIngredient();
                if ($ing) {
                    $ingStr .= ' '.$this->normalize((string)$ing->getNom());
                }
            }

            $scoreTitle = $this->similarity($normalized, $titre);
            $scoreDesc  = $this->similarity($normalized, $desc);
            $scoreIng   = $this->similarity($normalized, $ingStr);
            $score      = max($scoreTitle, $scoreDesc, $scoreIng);

            foreach ($tokens as $tok) {
                if ($tok === '') continue;
                if (str_contains($titre, $tok) || str_contains($desc, $tok) || str_contains($ingStr, $tok)) {
                    $score += 10;
                } elseif (mb_strlen($tok) >= 4) {
                    $closest = $this->closestWord($titre.' '.$desc.' '.$ingStr, $tok);
                    $dist    = levenshtein($tok, $closest);
                    if ($dist <= 2) $score += 6;
                }
            }

            $score = min(100, (float)$score);
            if ($score >= $minScore) {
                $scored[] = [$recette, $score];
            }
        }

        usort($scored, static function ($a, $b) {
            $cmp = $b[1] <=> $a[1]; // score desc
            if ($cmp !== 0) return $cmp;
            return strcmp((string)$a[0]->getTitre(), (string)$b[0]->getTitre()); // stabilité
        });

        return array_map(static fn($row) => $row[0], $scored);
    }

    // ---------------- Helpers privés ----------------

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
