<?php

namespace App\Repository;

use App\Entity\Tisane;
use App\Entity\AccordAromatique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
     * Classe les tisanes par score (= wB * nbBienfaits + wA * sommeScoresAromatiques)
     *
     * @param int[]   $bienfaitIds    Bienfaits qui SOULAGENT la gêne (via Gene->Bienfait)
     * @param int[]   $ingredientIds  Ingrédients de la recette
     * @param string[] $types         Types d’ingrédients de la recette (ex: "Poisson", "Volaille")
     * @return array[]                Chaque ligne: ['tisane' => Tisane, 'scoreB' => int, 'scoreA' => float, 'scoreTotal' => float]
     */
    public function findSuggestions(
        array $bienfaitIds,
        array $ingredientIds,
        array $types,
        int $limit = 3,
        float $wB = 2.0,   // poids des bienfaits
        float $wA = 1.0    // poids des accords aromatiques
    ): array {
        // Dummies pour éviter IN () vide
        $ingredientIds = !empty($ingredientIds) ? $ingredientIds : [0];
        $types         = !empty($types) ? $types : ['__NONE__'];

        $qb = $this->createQueryBuilder('t')
            // on veut récupérer l’entité + des scalaires
            ->select('t AS tisane');

        // === Bienfaits qui soulagent ===
        // Si on a une liste de bienfaits "soulagants", on ne joint QUE ceux-là (sinon 0).
        if (!empty($bienfaitIds)) {
            $qb->leftJoin('t.bienfaits', 'b', 'WITH', 'b.id IN (:bids)')
                ->setParameter('bids', $bienfaitIds);
        } else {
            // Join "vide" (never true) pour que COUNT = 0
            $qb->leftJoin('t.bienfaits', 'b', 'WITH', '1=0');
        }

        // === Plantes de la tisane + accords aromatiques ===
        $qb->leftJoin('t.plantes', 'p')
            ->leftJoin(AccordAromatique::class, 'ap', 'WITH', 'ap.plante = p');

        // Score bienfaits = nombre de bienfaits qui soulagent (COUNT DISTINCT car une tisane peut avoir plusieurs plantes/bienfaits)
        $qb->addSelect('COUNT(DISTINCT b.id) AS scoreB');

        // Score arômes = somme des scores d’accords où:
        //  - ap.ingredient match l’un des ingrédients de la recette
        //  - OU ap.ingredientType match l’un des types de la recette
        $qb->addSelect('COALESCE(SUM(
            CASE
                WHEN ap.ingredient IS NOT NULL AND ap.ingredient IN (:ingIds) THEN ap.score
                WHEN ap.ingredientType IS NOT NULL AND ap.ingredientType IN (:types) THEN ap.score
                ELSE 0
            END
        ), 0) AS scoreA')
            ->setParameter('ingIds', $ingredientIds)
            ->setParameter('types', $types);

        // Score total pondéré
        $qb->addSelect('(:wB * COUNT(DISTINCT b.id) + :wA * COALESCE(SUM(
            CASE
                WHEN ap.ingredient IS NOT NULL AND ap.ingredient IN (:ingIds) THEN ap.score
                WHEN ap.ingredientType IS NOT NULL AND ap.ingredientType IN (:types) THEN ap.score
                ELSE 0
            END
        ), 0)) AS scoreTotal')
            ->setParameter('wB', $wB)
            ->setParameter('wA', $wA);

        $qb->groupBy('t.id')
            ->orderBy('scoreTotal', 'DESC')
            ->setMaxResults($limit);

        // Résultat: tableau de lignes avec l’entité + scalaires
        // Chaque row ressemble à: ['tisane' => Tisane, 'scoreB' => '2', 'scoreA' => '1.5', 'scoreTotal' => '5.5']
        $rows = $qb->getQuery()->getResult();

        // Cast propre des scalaires en float/int
        foreach ($rows as &$r) {
            $r['scoreB'] = (int) $r['scoreB'];
            $r['scoreA'] = (float) $r['scoreA'];
            $r['scoreTotal'] = (float) $r['scoreTotal'];
        }
        return $rows;
    }
}