<?php

namespace App\Tests\Functional;

use App\Entity\Ingredient;
use App\Entity\Recette;
use App\Entity\RecetteIngredient;
use App\Entity\Utilisateur;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RecetteListControllerTest extends WebTestCase
{
    private function createUser(\Doctrine\ORM\EntityManagerInterface $em): Utilisateur
    {
        $user = new Utilisateur();
        $user
            ->setEmail('test-recettes@example.test')
            ->setPassword('dummy')              // pas besoin d’un vrai hash pour les tests
            ->setRoles(['ROLE_USER'])
            ->setNom('Test Recettes')          // <-- IMPORTANT
            ->setPrenom('Utilisateur');        // <-- si ta colonne est NOT NULL

        $em->persist($user);

        return $user;
    }

    public function test_liste_publique_filtre_calMax_en_phpp(): void
    {
        // Toujours créer le client avant les requêtes HTTP
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        // ----- Utilisateur obligatoire pour les recettes -----
        $user = $this->createUser($em);

        // ----- Ingrédients -----
        $ingCal = (new Ingredient())
            ->setNom('Beurre')
            ->setType('Matière grasse')
            ->setUnite('g')
            ->setCalories(720);

        $ingLight = (new Ingredient())
            ->setNom('Courgette')
            ->setType('Légume')
            ->setUnite('g')
            ->setCalories(17);

        $em->persist($ingCal);
        $em->persist($ingLight);

        // ----- Recettes -----
        $rBeurre = (new Recette())
            ->setTitre('Beurrée')
            ->setInstructions('Une recette très riche en beurre.')
            ->setValidation(true)
            ->setUtilisateur($user);

        $rLight = (new Recette())
            ->setTitre('Légère')
            ->setInstructions('Une recette très légère à base de courgette.')
            ->setValidation(true)
            ->setUtilisateur($user);

        $em->persist($rBeurre);
        $em->persist($rLight);

        // ----- Liaisons Recette ↔ RecetteIngredient ↔ Ingredient -----
        $ri1 = (new RecetteIngredient())
            ->setRecette($rBeurre)
            ->setIngredient($ingCal)
            ->setQuantite(100); // 720 kcal

        $ri2 = (new RecetteIngredient())
            ->setRecette($rLight)
            ->setIngredient($ingLight)
            ->setQuantite(200); // 17 * 200 / 100 ≈ 34 kcal

        $em->persist($ri1);
        $em->persist($ri2);

        $em->flush();
        $em->clear(); // on repart propre pour la requête HTTP

        // ----- Act -----
        $client->request('GET', '/recettes/liste?calMax=50');

        // ----- Assert -----
        $this->assertResponseIsSuccessful();
        $html = $client->getResponse()->getContent();

        // “Légère” doit apparaître…
        $this->assertStringContainsString('Légère', $html);
        // …et “Beurrée” doit être filtrée.
        $this->assertStringNotContainsString('Beurrée', $html);
    }
}
