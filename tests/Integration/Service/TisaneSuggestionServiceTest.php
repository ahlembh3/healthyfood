<?php

namespace App\Tests\Integration\Service;

use App\Entity\RecetteIngredient;
use App\Entity\AccordAromatique;
use App\Repository\TisaneRepository;
use App\Service\TisaneSuggestionService;
use App\Tests\Factory\IngredientFactory;
use App\Tests\Factory\PlanteFactory;
use App\Tests\Factory\RecetteFactory;
use App\Tests\Factory\TisaneFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class TisaneSuggestionServiceTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    public function test_findSuggestions_prend_en_compte_bienfaits_et_accords_aromatiques(): void
    {
        // Arrange
        self::bootKernel();
        $em = self::getContainer()->get('doctrine')->getManager();

        $recette = RecetteFactory::new()->create();
        $ingSaumon = IngredientFactory::new(['nom' => 'Saumon', 'type' => 'Poisson'])->create();

        $ri = new RecetteIngredient();
        $ri->setRecette($recette)->setIngredient($ingSaumon)->setQuantite(100);
        $recette->addRecetteIngredient($ri);
        $em->persist($recette);

        $plante = PlanteFactory::new()->create();
        $tisane = TisaneFactory::new(['nom' => 'Détox Marine'])->create();
        $tisane->addPlante($plante);
        $em->persist($tisane);

        $accord = new AccordAromatique();
        $accord->setPlante($plante)->setIngredientType('Poisson')->setScore(2.5);
        $em->persist($accord);

        $em->flush();

        /** @var TisaneRepository $tisaneRepo */
        $tisaneRepo = self::getContainer()->get(TisaneRepository::class);
        $svc = new TisaneSuggestionService($tisaneRepo, $em);

        // Act
        $rows = $svc->suggestForRecette($recette, limit: 3, wB: 2.0, wA: 1.0);

        // Assert
        $this->assertNotEmpty($rows);
        $this->assertSame('Détox Marine', $rows[0]['tisane']->getNom());
        $this->assertGreaterThan(0, $rows[0]['scoreA'], 'Score aromatique doit être > 0 grâce à l’accord sur Poisson.');
    }
}
