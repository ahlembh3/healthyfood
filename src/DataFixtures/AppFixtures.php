<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Bienfait;
use App\Entity\Commentaire;
use App\Entity\Ingredient;
use App\Entity\Plante;
use App\Entity\Recette;
use App\Entity\Tisane;
use App\Entity\Utilisateur;
use App\Entity\RecetteIngredient;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $projectDir = __DIR__ . '/json/';

        // 1. Utilisateur
        $utilisateur = new Utilisateur();
        $utilisateur->setEmail('admin@example.com');
        $utilisateur->setRoles(['ROLE_ADMIN']);
        $utilisateur->setNom('Admin');
        $utilisateur->setPrenom('Principal');
        $utilisateur->setPassword($this->passwordHasher->hashPassword($utilisateur, 'password'));
        $manager->persist($utilisateur);

        // 2. Bienfaits
        $bienfaitsData = json_decode(file_get_contents($projectDir . 'bienfaits.json'), true);
        $bienfaits = [];

        foreach ($bienfaitsData as $item) {
            $bienfait = new Bienfait();
            $bienfait->setNom($item['nom']);
            $bienfait->setDescription($item['description'] ?? null);
            $manager->persist($bienfait);
            $bienfaits[] = $bienfait;
        }

        // 3. Plantes
        $plantesData = json_decode(file_get_contents($projectDir . 'plantes.json'), true);
        $plantes = [];

        foreach ($plantesData as $item) {
            $plante = new Plante();
            $plante->setNomCommun($item['nomCommun']);
            $plante->setNomScientifique($item['nomScientifique']);
            $plante->setDescription($item['description'] ?? null);
            $plante->setPartieUtilisee($item['partieUtilisee'] ?? null);
            $plante->setPrecautions($item['precautions'] ?? null);
            $plante->setImage($item['image'] ?? null);
            $manager->persist($plante);
            $plantes[] = $plante;
        }

        // 4. Ingrédients
        $ingredientsData = json_decode(file_get_contents($projectDir . 'ingredients.json'), true);
        $ingredients = [];

        foreach ($ingredientsData as $item) {
            $ingredient = new Ingredient();
            $ingredient->setNom($item['nom']);
            $ingredient->setDescription($item['description'] ?? null);
            $manager->persist($ingredient);
            $ingredients[] = $ingredient;
        }

        // 5. Tisanes
        $tisanesData = json_decode(file_get_contents($projectDir . 'tisanes.json'), true);

        foreach ($tisanesData as $item) {
            $tisane = new Tisane();
            $tisane->setNom($item['nom']);
            $tisane->setModePreparation($item['modePreparation']);

            foreach ($item['bienfaits'] as $index) {
                if (isset($bienfaits[$index - 1])) {
                    $tisane->addBienfait($bienfaits[$index - 1]);
                }
            }

            foreach ($item['plantes'] as $index) {
                if (isset($plantes[$index - 1])) {
                    $tisane->addPlante($plantes[$index - 1]);
                }
            }

            $manager->persist($tisane);
        }

// 6. Recette : Salade detox
$recette = new Recette();
$recette->setTitre('Salade detox');
$recette->setDescription('Une salade légère et détoxifiante.');
$recette->setImage('salade.jpg');
$recette->setInstructions('1. Couper les légumes. 2. Assaisonner. 3. Servir frais.');
$recette->setUtilisateur($utilisateur);
$recette->setTempsPreparation(15);
$recette->setDifficulte('Facile');
$recette->setValidation(true);
$recette->setPortions(2);
$recette->setValeursNutrition("Faible en calories, riche en fibres");

// Ajout des ingrédients via la méthode personnalisée
foreach (array_slice($ingredients, 0, 2) as $ingredient) {
    $recette->addIngredient($ingredient, '100g');
}

// Persistance de la recette et des RecetteIngredient associés
foreach ($recette->getRecetteIngredients() as $ri) {
    $manager->persist($ri);
}

$manager->persist($recette);



        // 7. Articles
        $article = new Article();
        $article->setTitre('Bienfaits de la tisane');
        $article->setContenu('Les tisanes ont de nombreuses vertus...');
        $article->setDate(new \DateTimeImmutable());
         $article->setImage('salade.jpg');
        $article->setUtilisateur($utilisateur);
        $manager->persist($article);

        // Flush final
        $manager->flush();
    }
}
