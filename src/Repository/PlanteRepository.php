<?php

namespace App\Repository;

use App\Entity\Plante;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

class PlanteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Plante::class);
    }

    /**
     * Requête de base (toutes les plantes), tri par nom commun.
     */
    public function queryAll(): Query
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.nomCommun', 'ASC')
            ->getQuery();
    }

    /**
     * Recherche approximative tolérante aux fautes sur nom commun / scientifique / description.
     * 1) Préfiltre SQL avec LIKE sur des tokens normalisés (rapide)
     * 2) Reclassement côté PHP (similar_text + levenshtein) et seuil min.
     *
     * @return Plante[] Reclassées par pertinence puis nomCommun asc.
     */
    public function fuzzySearch(
        string $search,
        int $limitCandidates = 400,
        int $minScore = 18
    ): array {
        $normalized = $this->normalizeForSearch($search);
        $tokens     = preg_split('/\s+/', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        // --- 1) Préfiltre SQL (LIKE) ---
        $qb = $this->createQueryBuilder('p');

        if ($normalized !== '') {
            $or = $qb->expr()->orX();
            $i  = 0;

            foreach ($tokens as $tok) {
                $param = ':t'.$i++;
                // nomCommun
                $or->add($qb->expr()->like('LOWER(p.nomCommun)', $param));
                // nomScientifique
                $or->add($qb->expr()->like('LOWER(p.nomScientifique)', $param));
                // description
                $or->add($qb->expr()->like('LOWER(p.description)', $param));

                $qb->setParameter(substr($param, 1), '%'.$tok.'%');
            }

            if ($or->count() > 0) {
                $qb->andWhere($or);
            }
        }

        $qb->orderBy('p.nomCommun', 'ASC')
            ->setMaxResults($limitCandidates);

        /** @var Plante[] $candidats */
        $candidats = $qb->getQuery()->getResult();

        // --- 2) Reclassement côté PHP ---
        $scored = [];
        foreach ($candidats as $plante) {
            $nomComm = $this->normalizeForSearch((string) $plante->getNomCommun());
            $nomSci  = $this->normalizeForSearch((string) $plante->getNomScientifique());
            $desc    = $this->normalizeForSearch(strip_tags((string) ($plante->getDescription() ?? '')));
            $desc    = mb_substr($desc, 0, 800);

            $scoreNom   = max(
                $this->similarityPercent($normalized, $nomComm),
                $this->similarityPercent($normalized, $nomSci)
            );
            $scoreDesc  = $this->similarityPercent($normalized, $desc);
            $score      = max($scoreNom, $scoreDesc);

            // Bonus si un token apparaît (ou quasi) dans nom/desc
            foreach ($tokens as $tok) {
                if ($tok === '') continue;

                if (str_contains($nomComm, $tok) || str_contains($nomSci, $tok) || str_contains($desc, $tok)) {
                    $score += 10;
                } elseif (mb_strlen($tok) >= 4) {
                    // cherche un mot très proche dans le corpus de la plante
                    $closest = $this->closestWord($nomComm.' '.$nomSci.' '.$desc, $tok);
                    $dist    = levenshtein($tok, $closest);
                    if ($dist <= 2) {
                        $score += 6;
                    }
                }
            }

            $score = min(100, (float) $score);

            if ($score >= $minScore) {
                $scored[] = [$plante, $score];
            }
        }

        usort($scored, static function ($a, $b) {
            // score desc, puis nomCommun asc pour stabilité
            $cmp = $b[1] <=> $a[1];
            if ($cmp !== 0) return $cmp;
            return strcmp((string) $a[0]->getNomCommun(), (string) $b[0]->getNomCommun());
        });

        return array_map(static fn ($row) => $row[0], $scored);
    }

    // ----------------- Helpers privés -----------------

    /** Normalise un texte pour la recherche. */
    private function normalizeForSearch(string $s): string
    {
        $s = trim($s);
        if ($s === '') return '';

        if (class_exists(\Transliterator::class)) {
            $tr = \Transliterator::create('Any-Latin; Latin-ASCII; Lower()');
            if ($tr) {
                $s = $tr->transliterate($s);
            } else {
                $s = mb_strtolower($s, 'UTF-8');
            }
        } else {
            $s = mb_strtolower($s, 'UTF-8');
            $conv = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
            if ($conv !== false) {
                $s = $conv;
            }
        }

        $s = preg_replace('/[^a-z0-9\s]+/u', ' ', $s) ?? $s;
        $s = preg_replace('/\s+/u', ' ', $s) ?? $s;
        return trim($s);
    }

    /** Pourcentage de similarité (0..100) via similar_text. */
    private function similarityPercent(string $a, string $b): float
    {
        if ($a === '' || $b === '') return 0.0;
        similar_text($a, $b, $percent);
        return (float) $percent;
    }

    /** Mot du corpus le plus proche (distance de Levenshtein minimale). */
    private function closestWord(string $haystack, string $needle): string
    {
        $bestWord = '';
        $best     = PHP_INT_MAX;

        foreach (preg_split('/\s+/', $haystack, -1, PREG_SPLIT_NO_EMPTY) as $w) {
            $d = levenshtein($needle, $w);
            if ($d < $best) {
                $best = $d;
                $bestWord = $w;
            }
            if ($best === 0) break;
        }

        return $bestWord;
    }
}
