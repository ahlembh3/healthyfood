<?php

namespace App\DataFixtures;

use App\Entity\Recette;
use App\Entity\Ingredient;
use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

final class RecetteFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        // Besoin des ingrédients (et donc gènes/bienfaits en amont)
        return [IngredientFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $path = __DIR__ . '/data/recettes.json';
        if (!is_file($path)) {
            throw new \RuntimeException("Fichier JSON introuvable : {$path}");
        }

        $rows = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        // Récupère un utilisateur existant (ex: AppFixtures a créé admin@example.com)
        $userRepo = $manager->getRepository(Utilisateur::class);
        $user = $userRepo->findOneBy(['email' => 'admin@example.com']) ?? $userRepo->findOneBy([]); // n’importe lequel
        if (!$user) {
            // Sécurité: crée un utilisateur si aucun n’existe (évite l’erreur NOT NULL)
            $user = (new Utilisateur())
                ->setEmail('demo@healthyfood.local')
                ->setNom('Demo')
                ->setPrenom('User')
                ->setPassword('$2y$13$abcdefghijklmnopqrstuv') // hash bidon non utilisé
                ->setRoles(['ROLE_USER']);
            $manager->persist($user);
            $manager->flush();
        }

        $ingRepo = $manager->getRepository(Ingredient::class);

        foreach ($rows as $i => $row) {
            $titre = trim((string) ($row['titre'] ?? ''));
            if ($titre === '') {
                throw new \RuntimeException("recettes.json: entrée #{$i} sans 'titre'");
            }

            $r = (new Recette())
                ->setTitre($titre)
                ->setDescription($row['description'] ?? null)
                ->setInstructions($row['instructions'] ?? "Mélanger et servir.")
                ->setTempsPreparation($row['tempsPreparation'] ?? 10)
                ->setTempsCuisson($row['tempsCuisson'] ?? 0)
                ->setDifficulte($row['difficulte'] ?? 'Facile')
                ->setPortions($row['portions'] ?? 2)
                ->setImage($row['image'] ?? null)
                ->setValidation(true)
                ->setUtilisateur($user);

            $okCount = 0;
            foreach ((array) ($row['ingredients'] ?? []) as $ii => $line) {
                $nomIng = trim((string) ($line['nom'] ?? ''));
                $qte    = isset($line['quantite']) ? (float) $line['quantite'] : 100.0;
                if ($nomIng === '') {
                    continue;
                }
                $ing = $ingRepo->findOneBy(['nom' => $nomIng]);
                if ($ing) {
                    $r->addIngredient($ing, $qte);
                    $okCount++;
                }
            }

            // On exige au moins 2 ingrédients trouvés pour garder la recette
            if ($okCount >= 2) {
                $manager->persist($r);
            }
        }

        $manager->flush();
    }
}
