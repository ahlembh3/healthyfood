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
    private function createUser($em): Utilisateur
    {
        $u = (new Utilisateur())
            ->setEmail('u+'.uniqid().'@test.fr')
            ->setPassword('dummy')
            ->setNom('Test')->setPrenom('User')
            ->setRoles(['ROLE_USER']);
        $em->persist($u);
        return $u;
    }

    public function test_queryPublicWithFilters(): void
    {
        self::bootKernel();
        $em = static::getContainer()->get('doctrine')->getManager();

        $user = $this->createUser($em);

        $ingPoisson = (new Ingredient())->setNom("Saumon")->setType("Poisson")->setUnite("g")->setSaisonnalite("Printemps");
        $ingVolaille = (new Ingredient())->setNom("Poulet")->setType("Volaille")->setUnite("g")->setSaisonnalite("Hiver");

        $em->persist($ingPoisson);
        $em->persist($ingVolaille);

        $r1 = (new Recette())->setTitre("Tartare")->setInstructions("txt")->setValidation(true)->setUtilisateur($user);
        $r2 = (new Recette())->setTitre("Rôti")->setInstructions("txt")->setValidation(true)->setUtilisateur($user);

        $em->persist($r1);
        $em->persist($r2);

        $em->persist((new RecetteIngredient())->setRecette($r1)->setIngredient($ingPoisson)->setQuantite(100));
        $em->persist((new RecetteIngredient())->setRecette($r2)->setIngredient($ingVolaille)->setQuantite(150));

        $em->flush();
        $em->clear();

        /** @var RecetteRepository $repo */
        $repo = static::getContainer()->get(RecetteRepository::class);

        $byIng    = $repo->queryPublicWithFilters(['ingredient' => 'Saumon'])->getResult();
        $byType   = $repo->queryPublicWithFilters(['type' => 'volaille'])->getResult();
        $bySaison = $repo->queryPublicWithFilters(['saison' => 'Print'])->getResult();

        $this->assertSame("Tartare", $byIng[0]->getTitre());
        $this->assertSame("Rôti",    $byType[0]->getTitre());
        $this->assertSame("Tartare", $bySaison[0]->getTitre());
    }

    public function test_fuzzySearchPublic(): void
    {
        self::bootKernel();
        $em = static::getContainer()->get('doctrine')->getManager();

        $user = $this->createUser($em);

        $r = (new Recette())
            ->setTitre("Soupe de carottes")
            ->setInstructions("txt")
            ->setValidation(true)
            ->setUtilisateur($user);

        $em->persist($r);
        $em->flush();
        $em->clear();

        /** @var RecetteRepository $repo */
        $repo = static::getContainer()->get(RecetteRepository::class);

        $res = $repo->fuzzySearchPublic("carotte", []);

        $this->assertNotEmpty($res);
        $this->assertInstanceOf(Recette::class, $res[0]);
    }
}
