<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Ingredient;
use App\Entity\Recette;
use App\Entity\RecetteIngredient;
use App\Entity\Utilisateur;
use App\Repository\RecetteRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class RecetteRepositoryTest extends KernelTestCase
{
    private function createUser(\Doctrine\ORM\EntityManagerInterface $em, string $email): Utilisateur
    {
        $u = new Utilisateur();
        $u
            ->setEmail($email)
            ->setPassword('dummy')
            ->setRoles(['ROLE_USER'])
            ->setNom('User Recette')   // <-- IMPORTANT
            ->setPrenom('Test');

        $em->persist($u);

        return $u;
    }


    public function test_queryPublicWithFilters_par_ingredient_type_saison(): void
    {
        self::bootKernel();
        $em = self::getContainer()->get('doctrine')->getManager();

        $user = $this->createUser($em, 'recette-filters@example.test');

        // Ingrédients
        $ingPoisson = (new Ingredient())
            ->setNom('Saumon')
            ->setType('Poisson')
            ->setUnite('g')
            ->setSaisonnalite('Printemps');

        $ingVolaille = (new Ingredient())
            ->setNom('Poulet')
            ->setType('Volaille')
            ->setUnite('g')
            ->setSaisonnalite('Hiver');

        $em->persist($ingPoisson);
        $em->persist($ingVolaille);

        // Recettes
        $r1 = (new Recette())
            ->setTitre('Tartare')
            ->setInstructions('Tartare de saumon.')
            ->setValidation(true)
            ->setUtilisateur($user);

        $r2 = (new Recette())
            ->setTitre('Rôti')
            ->setInstructions('Rôti de poulet.')
            ->setValidation(true)
            ->setUtilisateur($user);

        $em->persist($r1);
        $em->persist($r2);

        // Liaisons
        $em->persist(
            (new RecetteIngredient())
                ->setRecette($r1)
                ->setIngredient($ingPoisson)
                ->setQuantite(100)
        );

        $em->persist(
            (new RecetteIngredient())
                ->setRecette($r2)
                ->setIngredient($ingVolaille)
                ->setQuantite(150)
        );

        $em->flush();
        $em->clear();

        /** @var RecetteRepository $repo */
        $repo = self::getContainer()->get(RecetteRepository::class);

        $byIng    = $repo->queryPublicWithFilters(['ingredient' => 'Saumon'])->getResult();
        $byType   = $repo->queryPublicWithFilters(['type' => 'volaille'])->getResult();
        $bySaison = $repo->queryPublicWithFilters(['saison' => 'Print'])->getResult();

        $this->assertSame('Tartare', $byIng[0]->getTitre());
        $this->assertSame('Rôti',    $byType[0]->getTitre());
        $this->assertCount(1, $bySaison);
        $this->assertSame('Tartare', $bySaison[0]->getTitre());
    }

    public function test_fuzzySearchPublic_retrouve_par_faute_orthographe(): void
    {
        self::bootKernel();
        $em = self::getContainer()->get('doctrine')->getManager();

        $user = $this->createUser($em, 'recette-fuzzy@example.test');

        $r = (new Recette())
            ->setTitre('Soupe de carottes')
            ->setInstructions('Mixer les carottes.')
            ->setValidation(true)
            ->setUtilisateur($user);

        $em->persist($r);
        $em->flush();
        $em->clear();

        /** @var RecetteRepository $repo */
        $repo = self::getContainer()->get(RecetteRepository::class);

        $results = $repo->fuzzySearchPublic('carotte', []);

        $this->assertNotEmpty($results);
        $this->assertInstanceOf(Recette::class, $results[0]);
    }
}
