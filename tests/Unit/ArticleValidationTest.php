<?php

namespace App\Tests\Unit;

use App\Entity\Article;
use App\Entity\Utilisateur;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ArticleValidationTest extends TestCase
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
        $u->setEmail('user@example.com');
        $u->setPassword('password123');
        $u->setRoles(['ROLE_USER']);
        $u->setNom('Doe');
        $u->setPrenom('John');

        return $u;
    }

    public function test_titre_obligatoire_et_longueur(): void
    {
        // Article SANS titre, mais avec tout le reste valide
        $article = (new Article())
            ->setContenu('Contenu suffisant pour passer le minimum de 20 caractères.')
            ->setDate(new \DateTimeImmutable())
            ->setValidation(true)
            ->setCategorie('Bien-être') // on suppose que cette catégorie est valide
            ->setUtilisateur($this->createUser());

        // 1) Validation sans titre
        $violationsSansTitre = $this->validator()->validate($article);

        // 2) Titre trop long (> 255)
        $article->setTitre(str_repeat('x', 256));
        $violationsTitreLong = $this->validator()->validate($article);

        self::assertGreaterThan(
            0,
            \count($violationsSansTitre),
            'Le titre doit être requis (violations attendues sans titre).'
        );

        self::assertGreaterThan(
            0,
            \count($violationsTitreLong),
            'Un titre de plus de 255 caractères doit violer la contrainte de longueur.'
        );
    }

    public function test_contenu_min_20_et_categorie_valide(): void
    {
        // Contenu trop court + catégorie invalide
        $article = (new Article())
            ->setTitre('OK')
            ->setContenu('trop court')      // < 20 caractères
            ->setDate(new \DateTimeImmutable())
            ->setValidation(true)
            ->setCategorie('Bidule')        // on suppose que cette valeur n’est pas dans les choix autorisés
            ->setUtilisateur($this->createUser());

        $violations = $this->validator()->validate($article);

        self::assertNotEmpty(
            $violations,
            'Contenu trop court et/ou catégorie invalide doivent générer des violations.'
        );
    }
}
