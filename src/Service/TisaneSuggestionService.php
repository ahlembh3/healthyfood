<?php

namespace App\Service;

use App\Entity\Recette;
use App\Repository\TisaneRepository;
use Doctrine\ORM\EntityManagerInterface;

class TisaneSuggestionService
{
    public function __construct(
        private TisaneRepository $tisaneRepo,
        private EntityManagerInterface $em
    ) {}

    /**
     * Retourne des suggestions triées, avec les scores calculés.
     * Chaque item = ['tisane' => Tisane, 'scoreB' => int, 'scoreA' => float, 'scoreTotal' => float]
     */
    public function suggestForRecette(Recette $recette, int $limit = 3, float $wB = 2.0, float $wA = 1.0): array
    {
        // 1) Ingrédients & types de la recette
        $ingredientIds = [];
        $types = [];
        foreach ($recette->getRecetteIngredients() as $ri) {
            $ing = $ri->getIngredient();
            if (!$ing) continue;
            if ($ing->getId()) {
                $ingredientIds[] = (int) $ing->getId();
            }
            $t = trim((string)($ing->getType() ?? ''));
            if ($t !== '') {
                $types[] = $t;
            }
        }
        $ingredientIds = array_values(array_unique($ingredientIds));
        $types = array_values(array_unique($types));

        // 2) Gênes liés aux ingrédients
        $geneIds = [];
        if (!empty($ingredientIds)) {
            $rows = $this->em->createQuery(
                'SELECT DISTINCT g.id
                 FROM App\Entity\Gene g
                 JOIN g.ingredients ing
                 WHERE ing.id IN (:ids)'
            )->setParameter('ids', $ingredientIds)
                ->getScalarResult();

            $geneIds = array_map(static fn($r) => (int)$r['id'], $rows);
        }

        // 3) Bienfaits qui soulagent ces gènes
        $bienfaitIds = [];
        if (!empty($geneIds)) {
            $rows = $this->em->createQuery(
                'SELECT DISTINCT b.id
                 FROM App\Entity\Bienfait b
                 JOIN b.genes g
                 WHERE g.id IN (:gids)'
            )->setParameter('gids', $geneIds)
                ->getScalarResult();

            $bienfaitIds = array_map(static fn($r) => (int)$r['id'], $rows);
        }

        // 4) Classement des tisanes
        return $this->tisaneRepo->findSuggestions(
            bienfaitIds:   $bienfaitIds,
            ingredientIds: $ingredientIds,
            types:         $types,
            limit:         $limit,
            wB:            $wB,
            wA:            $wA
        );
    }
}
