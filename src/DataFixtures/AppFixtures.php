<?php

namespace App\DataFixtures;

use App\Entity\Utilisateur;
use App\Entity\Plante;
use App\Entity\Bienfait;
use App\Entity\Tisane;
use App\Entity\Ingredient;
use App\Entity\Recette;
use App\Entity\RecetteIngredient;
use App\Entity\Article;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        /**
         * UTILISATEUR PRINCIPAL
         */
        $user = new Utilisateur();
        $user->setEmail('ahlem@example.com');
        $user->setNom('Ben Hamouda');
        $user->setPrenom('Ahlem');
        $user->setRoles(['ROLE_USER']);
        $user->setPreferences('tisanes, bio, detox');
        $hashedPassword = $this->hasher->hashPassword($user, 'aaa');
        $user->setPassword($hashedPassword);
        $manager->persist($user);

        /**
         * PLANTES
         */
      // Plantes
$plantes = [];
for ($i = 0; $i < 10; $i++) {
    $plante = new Plante();
    $plante->setNomCommun($faker->word());
    $plante->setNomScientifique($faker->words(2, true));
    $plante->setDescription($faker->sentence());
    $plante->setPartieUtilisee($faker->randomElement(['feuilles', 'racines', 'fleurs', 'tiges']));
    $plante->setPrecautions($faker->sentence());
    $manager->persist($plante);
    $plantes[] = $plante;
}


        /**
         * BIENFAITS
         */
        $bienfaits = [];
        $nomsBienfaits = ['Digestion', 'Sommeil', 'Détox', 'Énergie', 'Anti-stress'];
        foreach ($nomsBienfaits as $nom) {
            $bienfait = new Bienfait();
            $bienfait->setNom($nom);
            $manager->persist($bienfait);
            $bienfaits[] = $bienfait;
        }

        /**
         * TISANES
         */
        for ($i = 0; $i < 5; $i++) {
            $tisane = new Tisane();
            $tisane->setNom('Tisane ' . $faker->colorName());
            $tisane->setModePreparation($faker->paragraph());

            foreach ($faker->randomElements($plantes, rand(1, 3)) as $plante) {
                $tisane->addPlante($plante);
            }

            foreach ($faker->randomElements($bienfaits, rand(1, 2)) as $bienfait) {
                $tisane->addBienfait($bienfait);
            }

            $manager->persist($tisane);
        }

        /**
         * INGREDIENTS
         */
        $ingredients = [];
        for ($i = 0; $i < 10; $i++) {
            $ingredient = new Ingredient();
            $ingredient->setNom($faker->word());
            $ingredient->setUnite($faker->randomElement(['g', 'mg', 'ml', 'cuillère', 'pincée']));
            $manager->persist($ingredient);
            $ingredients[] = $ingredient;
        }

        /**
         * RECETTES
         */
      // Recettes
for ($i = 0; $i < 5; $i++) {
    $recette = new Recette();
    $recette->setTitre('Recette ' . $faker->word());
    $recette->setDescription($faker->sentence());
    $recette->setInstructions($faker->paragraph());
    $recette->setUtilisateur($user);
    $recette->setValidation($faker->boolean());
    $recette->setDifficulte($faker->randomElement(['Facile', 'Moyen', 'Difficile']));
    $recette->setTempsPreparation($faker->numberBetween(5, 60));
    $recette->setPortions($faker->numberBetween(1, 6));
    $recette->setValeursNutrition($faker->sentence());

    foreach ($faker->randomElements($ingredients, rand(2, 4)) as $ingredient) {
        $ri = new RecetteIngredient();
        $ri->setIngredient($ingredient);
        $ri->setRecette($recette);
        $ri->setQuantite($faker->randomFloat(2, 0.1, 5.0));
        $manager->persist($ri);
        $recette->getRecetteIngredients()->add($ri);
    }

    $manager->persist($recette);
}


        /**
         * ARTICLES
         */
        $categories = ['Santé', 'Nutrition', 'Remèdes naturels', 'Bien-être', 'Tisanes'];
        for ($i = 0; $i < 5; $i++) {
            $article = new Article();
            $article->setTitre('Article ' . $faker->words(3, true));
            $article->setContenu($faker->paragraphs(3, true));
            $article->setDate(new \DateTimeImmutable());
            $article->setImage($faker->boolean(70) ? $faker->imageUrl(640, 480, 'nature') : null);
            $article->setValidation($faker->boolean(80));
            $article->setCategorie($faker->randomElement($categories));
            $article->setUtilisateur($user);

            $manager->persist($article);
        }

        /**
         * ENREGISTRER DANS LA BDD
         */
           try {
            $manager->flush();
        } catch (\Exception $e) {
            dump('Erreur lors du flush :');
            dump($e->getMessage());
            dump($e->getTraceAsString());
            throw $e;
        }
    }
    }

