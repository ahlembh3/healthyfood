<?php

namespace App\Tests\Unit;

use App\Entity\Recette;
use App\Entity\RecetteIngredient;
use App\Tests\Factory\IngredientFactory;
use App\Tests\Factory\UtilisateurFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zenstruck\Foundry\Test\Factories;

final class RecetteValidationTest extends KernelTestCase
{
    use Factories;

    private function validator(): ValidatorInterface
    {
        self::bootKernel();
        return self::getContainer()->get(ValidatorInterface::class);
    }

    public function test_recette_exige_titre_instructions_et_un_ingredient(): void
    {
        // Arrange
        $r = (new Recette())
            ->setTitre('Soupe')
            ->setInstructions('Préparer... assez long.')
            ->setUtilisateur(UtilisateurFactory::new()->create())
            ->setValidation(false);

        // Act
        $violationsSansIng = $this->validator()->validate($r);

        $ri = (new RecetteIngredient())
            ->setIngredient(IngredientFactory::new()->create())
            ->setQuantite(100)
            ->setRecette($r);
        $r->addRecetteIngredient($ri);

        $violationsAvecIng = $this->validator()->validate($r);

        // Assert
        $this->assertGreaterThan(0, \count($violationsSansIng), 'Doit exiger >= 1 ingrédient.');
        $this->assertCount(0, $violationsAvecIng, 'Devient valide avec 1 ingrédient.');
    }

    public function test_temps_prepa_cuisson_non_negatifs(): void
    {
        // Arrange
        $r = (new Recette())
            ->setTitre('Soupe')
            ->setInstructions('Préparer... assez long.')
            ->setUtilisateur(UtilisateurFactory::new()->create())
            ->setTempsPreparation(-3)
            ->setTempsCuisson(-5);

        // Act
        $ri = (new RecetteIngredient())
            ->setIngredient(IngredientFactory::new()->create())
            ->setQuantite(50)
            ->setRecette($r);
        $r->addRecetteIngredient($ri);

        $violations = $this->validator()->validate($r);

        // Assert
        $this->assertNotEmpty($violations, 'Temps négatifs doivent violer les contraintes.');
    }
}
