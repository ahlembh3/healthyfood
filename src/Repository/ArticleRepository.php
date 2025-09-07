<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * Derniers articles validés (pour mise en avant).
     * @return Article[]
     */
    public function findLatestValidated(int $limit = 2): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.validation = :v')
            ->setParameter('v', true)
            ->orderBy('a.date', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Requête de base (paginable) : articles validés, filtre catégorie optionnel.
     */
    public function queryValidated(?string $categorie = null): Query
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.validation = true');

        if ($categorie !== null && $categorie !== '') {
            $qb->andWhere('a.categorie = :cat')->setParameter('cat', $categorie);
        }

        $qb->orderBy('a.date', 'DESC');

        return $qb->getQuery();
    }

    /**
     * Recherche approximative tolérante aux fautes :
     * - Préfiltre SQL (LIKE sur tokens)
     *  - Reclassement côté PHP (similar_text + levenshtein)
     * @return Article[] Reclassés par pertinence puis date desc.
     */
    public function fuzzySearchValidated(
        string $search,
        ?string $categorie = null,
        int $limitCandidates = 250,
        int $minScore = 20
    ): array {
        $normalized = $this->normalizeForSearch($search);
        $tokens     = preg_split('/\s+/', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $qb = $this->createQueryBuilder('a')
            ->where('a.validation = true');

        if ($categorie !== null && $categorie !== '') {
            $qb->andWhere('a.categorie = :cat')->setParameter('cat', $categorie);
        }

        if ($normalized !== '') {
            $orX = $qb->expr()->orX();
            $i   = 0;
            foreach ($tokens as $tok) {
                $param = ':t'.$i++;
                $orX->add($qb->expr()->like('LOWER(a.titre)', $param));
                $orX->add($qb->expr()->like('LOWER(a.contenu)', $param));
                $qb->setParameter(substr($param, 1), '%'.$tok.'%');
            }

            // ✅ Correction: on teste le nombre de parties de l'expression
            if (count($orX->getParts()) > 0) {
                $qb->andWhere($orX);
            }
        }

        $qb->orderBy('a.date', 'DESC')
            ->setMaxResults($limitCandidates);

        /** @var Article[] $candidats */
        $candidats = $qb->getQuery()->getResult();

        // Reclassement côté PHP
        $scored = [];
        foreach ($candidats as $a) {
            $titleNorm   = $this->normalizeForSearch((string) $a->getTitre());
            $contentNorm = $this->normalizeForSearch(strip_tags((string) ($a->getContenu() ?? '')));
            $contentNorm = mb_substr($contentNorm, 0, 800);

            $scoreTitle   = $this->similarityPercent($normalized, $titleNorm);
            $scoreContent = $this->similarityPercent($normalized, $contentNorm);
            $score        = max($scoreTitle, $scoreContent);

            // Bonus si un token est présent ou proche
            foreach ($tokens as $tok) {
                if ($tok === '') continue;

                if (str_contains($titleNorm, $tok) || str_contains($contentNorm, $tok)) {
                    $score += 10;
                } elseif (mb_strlen($tok) >= 4) {
                    $closest = $this->closestWord($titleNorm.' '.$contentNorm, $tok);
                    $dist    = levenshtein($tok, $closest);
                    if ($dist <= 2) {
                        $score += 6;
                    }
                }
            }

            $score = min(100, (float) $score);

            if ($score >= $minScore) {
                $scored[] = [$a, $score];
            }
        }

        usort($scored, static function ($x, $y) {
            $cmp = $y[1] <=> $x[1]; // score desc
            if ($cmp !== 0) return $cmp;
            $dx = $x[0]->getDate();
            $dy = $y[0]->getDate();
            return $dy <=> $dx;     // date desc
        });

        return array_map(static fn($row) => $row[0], $scored);
    }

    /**
     * Catégories disponibles (dynamiques) pour la liste publique.
     * @return string[]
     */
    public function getAvailableCategories(): array
    {
        $rows = $this->createQueryBuilder('a')
            ->select('DISTINCT a.categorie AS categorie')
            ->where('a.validation = true')
            ->andWhere('a.categorie IS NOT NULL AND a.categorie <> \'\'')
            ->orderBy('a.categorie', 'ASC')
            ->getQuery()
            ->getScalarResult();

        return array_column($rows, 'categorie');
    }

    // ----------------- Helpers privés -----------------

    private function normalizeForSearch(string $s): string
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

    private function similarityPercent(string $a, string $b): float
    {
        if ($a === '' || $b === '') return 0.0;
        similar_text($a, $b, $percent);
        return (float) $percent;
    }

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
