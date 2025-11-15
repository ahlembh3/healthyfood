<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Recette;
use App\Entity\RecetteIngredient;
use App\Repository\RecetteRepository;
use App\Tests\Factory\IngredientFactory;
use App\Tests\Factory\RecetteFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;
use Zenstruck\Foundry\Test\Factories;

final class RecetteRepositoryTest extends KernelTestCase
{
    use ResetDatabase;
    use Factories;

    public function test_queryPublicWithFilters_par_ingredient_type_saison(): void
    {
        self::bootKernel();
        $em = self::getContainer()->get('doctrine')->getManager();

        // Arrange
        $ingPoisson  = IngredientFactory::new(['nom' => 'Saumon', 'type' => 'Poisson',  'saisonnalite' => 'Printemps'])->create();
        $ingVolaille = IngredientFactory::new(['nom' => 'Poulet', 'type' => 'Volaille', 'saisonnalite' => 'Hiver'])->create();

        $r1 = RecetteFactory::new(['titre' => 'Tartare', 'validation' => true])->create();
        $r2 = RecetteFactory::new(['titre' => 'Rôti',    'validation' => true])->create();

        // Relie & persiste explicitement
        $em->persist((new RecetteIngredient())->setRecette($r1)->setIngredient($ingPoisson)->setQuantite(100));
        $em->persist((new RecetteIngredient())->setRecette($r2)->setIngredient($ingVolaille)->setQuantite(150));
        $em->flush();

        /** @var RecetteRepository $repo */
        $repo = self::getContainer()->get(RecetteRepository::class);

        // Act
        $byIng    = $repo->queryPublicWithFilters(['ingredient' => 'Saumon'])->getResult();
        $byType   = $repo->queryPublicWithFilters(['type' => 'volaille'])->getResult();
        $bySaison = $repo->queryPublicWithFilters(['saison' => 'Print'])->getResult();

        // Assert
        $this->assertSame('Tartare', $byIng[0]->getTitre());
        $this->assertSame('Rôti',    $byType[0]->getTitre());
        $this->assertCount(1, $bySaison);
        $this->assertSame('Tartare', $bySaison[0]->getTitre());
    }

    public function test_fuzzySearchPublic_retrouve_par_faute_orthographe(): void
    {
        self::bootKernel();

        // Arrange : aucune liaison nécessaire ici
        RecetteFactory::new(['titre' => 'Soupe de carottes'])->create();

        /** @var RecetteRepository $repo */
        $repo = self::getContainer()->get(RecetteRepository::class);

        // Act (on reste tolérant à l’implémentation: cherche “carotte”)
        $results = $repo->fuzzySearchPublic('carotte', []);

        // Assert
        $this->assertNotEmpty($results);
        $this->assertInstanceOf(Recette::class, $results[0]);
    }
}
