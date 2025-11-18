<?php

namespace App\Tests\Unit;

use App\Entity\Ingredient;
use App\Entity\Recette;
use App\Entity\RecetteIngredient;
use App\Entity\Utilisateur;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RecetteValidationTest extends TestCase
{
    private function validator(): ValidatorInterface
    {
        return Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    private function createUser(): Utilisateur
    {
        $u = new Utilisateur();
        $u->setEmail('test@example.com');
        $u->setPassword('password123');
        $u->setRoles(['ROLE_USER']);
        $u->setNom('Test');
        $u->setPrenom('User');
        return $u;
    }

    private function createIngredient(string $nom = 'Carotte'): Ingredient
    {
        $i = new Ingredient();
        $i->setNom($nom);
        $i->setType('Légume');       // valeur obligatoire
        $i->setUnite('g');           // valeur obligatoire
        return $i;
    }

    public function test_recette_exige_titre_instructions_et_un_ingredient(): void
    {
        // Arrange
        $recette = (new Recette())
            ->setTitre('Soupe')
            ->setInstructions('Préparer... assez long.')
            ->setUtilisateur($this->createUser())
            ->setValidation(false);

        // Act : validation SANS ingrédient
        $violationsSansIng = $this->validator()->validate($recette);

        // Ajout d'un ingrédient valide
        $ri = (new RecetteIngredient())
            ->setIngredient($this->createIngredient())
            ->setQuantite(100)
            ->setRecette($recette);
        $recette->addRecetteIngredient($ri);

        // Validation AVEC ingrédient
        $violationsAvecIng = $this->validator()->validate($recette);

        // Assert
        self::assertGreaterThan(
            0,
            count($violationsSansIng),
            'La recette doit exiger >= 1 ingrédient.'
        );

        self::assertCount(
            0,
            $violationsAvecIng,
            'La recette devient valide avec 1 ingrédient.'
        );
    }

    public function test_temps_prepa_cuisson_non_negatifs(): void
    {
        // Arrange
        $recette = (new Recette())
            ->setTitre('Soupe')
            ->setInstructions('Préparer... assez long.')
            ->setUtilisateur($this->createUser())
            ->setTempsPreparation(-3)
            ->setTempsCuisson(-5);

        // Ajout d'un ingrédient valide (sinon la validation échoue pour une autre raison)
        $ri = (new RecetteIngredient())
            ->setIngredient($this->createIngredient('Courgette'))
            ->setQuantite(50)
            ->setRecette($recette);
        $recette->addRecetteIngredient($ri);

        // Act
        $violations = $this->validator()->validate($recette);

        // Assert
        self::assertNotEmpty(
            $violations,
            'Les temps négatifs doivent générer des violations.'
        );
    }
}
