<?php

namespace App\Tests\Functional;

use App\Entity\RecetteIngredient;
use App\Tests\Factory\IngredientFactory;
use App\Tests\Factory\RecetteFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;
use Zenstruck\Foundry\Test\Factories;

final class RecetteListControllerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;

    public function test_liste_publique_filtre_calMax_en_phpp(): void
    {
        // Toujours créer le client avant d'utiliser Foundry en WebTestCase
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        // Arrange
        $ingCal   = IngredientFactory::new(['nom' => 'Beurre',    'unite' => 'g', 'calories' => 720])->create();
        $ingLight = IngredientFactory::new(['nom' => 'Courgette', 'unite' => 'g', 'calories' => 17])->create();

        $rBeurre = RecetteFactory::new(['titre' => 'Beurrée'])->create();
        $rLight  = RecetteFactory::new(['titre' => 'Légère'])->create();

        // ✅ Important : ajouter les liaisons via la méthode addRecetteIngredient()
        // pour tenir à jour la collection et le côté "owning" de l'association.
        $ri1 = (new RecetteIngredient())
            ->setIngredient($ingCal)
            ->setQuantite(100);
        $rBeurre->addRecetteIngredient($ri1);

        $ri2 = (new RecetteIngredient())
            ->setIngredient($ingLight)
            ->setQuantite(200);
        $rLight->addRecetteIngredient($ri2);

        // Persister (les RI ne sont pas en cascade côté Recette chez toi)
        $em->persist($ri1);
        $em->persist($ri2);
        $em->flush();
        $em->clear(); // on repart propre pour la requête HTTP

        // Act
        $client->request('GET', '/recettes/liste?calMax=50');

        // Assert
        $this->assertResponseIsSuccessful();
        $html = $client->getResponse()->getContent();

        // “Légère” (17 kcal/100g * 200g = ~34) doit être présente…
        $this->assertStringContainsString('Légère', $html);
        // …et “Beurrée” (720 kcal/100g * 100g = 720) doit être filtrée.
        $this->assertStringNotContainsString('Beurrée', $html);
    }
}
