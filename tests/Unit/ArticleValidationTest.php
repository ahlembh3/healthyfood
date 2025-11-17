<?php

namespace App\Tests\Unit;

use App\Entity\Article;
use App\Tests\Factory\UtilisateurFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zenstruck\Foundry\Test\Factories;

final class ArticleValidationTest extends KernelTestCase
{
    use Factories;

    private function validator(): ValidatorInterface
    {
        self::bootKernel();
        return self::getContainer()->get(ValidatorInterface::class);
    }

    public function test_titre_obligatoire_et_longueur(): void
    {
        // Arrange
        $a = (new Article())
            ->setContenu('Contenu suffisant pour passer le min de 20 caractères.')
            ->setDate(new \DateTimeImmutable())
            ->setValidation(true)
            ->setCategorie('Bien-être')
            ->setUtilisateur(UtilisateurFactory::new()->create());

        // Act
        $violationsSansTitre = $this->validator()->validate($a);
        $a->setTitre(str_repeat('x', 256));
        $violationsTitreLong = $this->validator()->validate($a);

        // Assert
        $this->assertGreaterThan(0, \count($violationsSansTitre), 'Le titre doit être requis.');
        $this->assertGreaterThan(0, \count($violationsTitreLong), 'Titre > 255 devrait violer la contrainte.');
    }

    public function test_contenu_min_20_et_categorie_valide(): void
    {
        // Arrange
        $a = (new Article())
            ->setTitre('Ok')
            ->setContenu('trop court')
            ->setDate(new \DateTimeImmutable())
            ->setValidation(true)
            ->setCategorie('Bidule') // invalide
            ->setUtilisateur(UtilisateurFactory::new()->create());

        // Act
        $violations = $this->validator()->validate($a);

        // Assert
        $this->assertNotEmpty($violations, 'Contenu court + cat invalide doivent échouer.');
    }
}
