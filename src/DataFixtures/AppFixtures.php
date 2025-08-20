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
        $projectDir = __DIR__ . '/data/';

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

       

        // 5. Tisanes
        $tisanesData = json_decode(file_get_contents($projectDir . 'tisanes.json'), true);

        foreach ($tisanesData as $item) {
            $tisane = new Tisane();
            $tisane->setNom($item['nom']);
            $tisane->setModePreparation($item['modePreparation']);
            $tisane->setImage($item['image'] ?? null);

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


        // 1. Création des ingrédients
        $ingredientsData = json_decode(file_get_contents(__DIR__ . '/data/ingredients.json'), true);
        $ingredients = [];

        foreach ($ingredientsData as $item) {
            $ingredient = new Ingredient();
            $ingredient->setNom($item['nom'] ?? '');
            $ingredient->setType($item['type'] ?? '');
            $ingredient->setAllergenes($item['allergenes'] ?? '');
            $ingredient->setSaisonnalite($item['saisonnalite'] ?? '');
            $ingredient->setDescription($item['description'] ?? null);
            $ingredient->setUnite($item['unite'] ?? 'gramme');
            $ingredient->setCalories(self::toNullableFloat($item['calories']));
            $ingredient->setProteines(self::toNullableFloat($item['proteines']));
            $ingredient->setGlucides(self::toNullableFloat($item['glucides']));
            $ingredient->setLipides(self::toNullableFloat($item['lipides']));
            $ingredient->setOrigine($item['origine']);
            $ingredient->setBio((int) $item['bio'] === 1 ? 1 : 0); // 0/1
            $ingredient->setImage($item['image']);



            $manager->persist($ingredient);
            $ingredients[$item['nom']] = $ingredient; // clé pour accès rapide
        }

       // 3. Création d'une recette
           $recette = new Recette();
           $recette->setTitre('Salade fraîcheur citronnée');
           $recette->setDescription('Une salade simple et rafraîchissante à base de concombre et citron.');
           $recette->setInstructions('Mélanger les ingrédients dans un saladier. Servir frais.');
           $recette->setTempsPreparation(10);
           $recette->setUtilisateur($utilisateur);
           $recette->setImage('salade.jpg');
           $recette->setTempsCuisson(0); 
           if (isset($ingredients['Concombre'])) {
              $recette->addIngredient($ingredients['Concombre'], 150);
            }
           if (isset($ingredients['Citron'])){
              $recette->addIngredient($ingredients['Citron'], 20);
            }
           if (isset($ingredients['Miel'])){
              $recette->addIngredient($ingredients['Miel'], 1);
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

         
         // 8. Commentaires
        $commentaire1 = new Commentaire();
        $commentaire1->setContenu("Très bonne recette, simple et rapide !");
        $commentaire1->setDate(new \DateTimeImmutable('-1 day'));
        $commentaire1->setNote(5);
        $commentaire1->setSignaler(false);
        $commentaire1->setRecette($recette);
        $commentaire1->setType(1);
        $commentaire1->setUtilisateur($utilisateur);
        $manager->persist($commentaire1);

        $commentaire2 = new Commentaire();
        $commentaire2->setContenu("J’ai remplacé le miel par du sirop d’agave, excellent !");
        $commentaire2->setDate(new \DateTimeImmutable('-2 hours'));
        $commentaire2->setNote(4);
        $commentaire2->setSignaler(false);
        $commentaire2->setRecette($recette);
        $commentaire2->setType(1);
        $commentaire2->setUtilisateur($utilisateur);
        $manager->persist($commentaire2);

        $commentaire3 = new Commentaire();
        $commentaire3->setContenu("Article très intéressant sur les bienfaits des plantes !");
        $commentaire3->setDate(new \DateTimeImmutable('-3 hours'));
        $commentaire3->setNote(5);
        $commentaire3->setSignaler(false);
        $commentaire3->setArticle($article); 
        $commentaire3->setUtilisateur($utilisateur);
        $commentaire3->setType(2); 
        $manager->persist($commentaire3);

        // Flush final
        $manager->flush();
    
}
}
