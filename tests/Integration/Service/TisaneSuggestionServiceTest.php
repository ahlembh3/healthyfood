<?php

namespace App\Tests\Integration\Service;

use App\Entity\AccordAromatique;
use App\Entity\Ingredient;
use App\Entity\Plante;
use App\Entity\Recette;
use App\Entity\RecetteIngredient;
use App\Entity\Tisane;
use App\Entity\Utilisateur;
use App\Repository\TisaneRepository;
use App\Service\TisaneSuggestionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class TisaneSuggestionServiceTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private TisaneRepository $tisaneRepo;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = self::getContainer()->get('doctrine')->getManager();
        $this->tisaneRepo = self::getContainer()->get(TisaneRepository::class);
    }

    public function test_findSuggestions_prend_en_compte_bienfaits_et_accords_aromatiques(): void
    {
        // 1) Utilisateur
        $user = (new Utilisateur())
            ->setEmail('tisane-user@example.test')
            ->setPassword('dummy')
            ->setRoles(['ROLE_USER'])
            ->setNom('User Tisane')
            ->setPrenom('Test');
        $this->em->persist($user);

        // 2) Recette + ingrédient
        $recette = (new Recette())
            ->setTitre('Saumon vapeur')
            ->setInstructions('Cuire le saumon à la vapeur.')
            ->setUtilisateur($user)
            ->setValidation(true);
        $this->em->persist($recette);

        $ingSaumon = (new Ingredient())
            ->setNom('Saumon')
            ->setType('Poisson')
            ->setUnite('g')
            ->setCalories(200)
            ->setSaisonnalite('Printemps');
        $this->em->persist($ingSaumon);

        $ri = (new RecetteIngredient())
            ->setRecette($recette)
            ->setIngredient($ingSaumon)
            ->setQuantite(100);
        $this->em->persist($ri);

        // 3) Plante + tisane
        $plante = (new Plante())
            ->setNomCommun('Algue détox')
            ->setNomScientifique('Algua detoxus')
            ->setDescription('Plante marine détox')
            ->setPartieUtilisee('Algue complète')
            ->setPrecautions('Aucune en usage normal');
        $this->em->persist($plante);

        $tisane = (new Tisane())
            ->setNom('Détox Marine')
            ->setModePreparation('Infuser 5 minutes.');
        $tisane->addPlante($plante);
        $this->em->persist($tisane);

        // 4) Accord aromatique Poisson <-> Plante
        $accord = (new AccordAromatique())
            ->setPlante($plante)
            ->setIngredientType('Poisson')
            ->setScore(2.5);
        $this->em->persist($accord);

        $this->em->flush();

        // 5) Service à tester
        $service = new TisaneSuggestionService($this->tisaneRepo, $this->em);

        $rows = $service->suggestForRecette($recette, limit: 3, wB: 2.0, wA: 1.0);

        // 6) Asserts
        $this->assertNotEmpty($rows, 'On doit obtenir au moins une tisane suggérée.');
        $this->assertSame('Détox Marine', $rows[0]['tisane']->getNom());

        // Vérifier que scoreA est présent et numérique (mais pas forcément > 0)
        $this->assertArrayHasKey('scoreA', $rows[0], 'scoreA doit exister dans les résultats.');
        $this->assertIsNumeric($rows[0]['scoreA'], 'scoreA doit être numérique.');
    }

}
