<?php

namespace App\DataFixtures;

use App\Entity\Ingredient;
use App\Entity\Gene;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

final class IngredientFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [GeneFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $path = __DIR__ . '/data/ingredients.json';
        if (!is_file($path)) {
            throw new \RuntimeException("Fichier JSON introuvable : {$path}");
        }

        $rows = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        $geneRepo = $manager->getRepository(Gene::class);

        foreach ($rows as $i => $row) {
            $nom = trim((string) ($row['nom'] ?? ''));
            if ($nom === '') {
                throw new \RuntimeException("ingredients.json: entrée #{$i} sans 'nom'");
            }

            $ing = (new Ingredient())
                ->setNom($nom)
                ->setType($row['type'] ?? null)
                ->setAllergenes($row['allergenes'] ?? null)
                ->setSaisonnalite($row['saisonnalite'] ?? null)
                ->setDescription($row['description'] ?? null)
                ->setUnite($row['unite'] ?? 'g')
                ->setCalories($row['calories'] ?? null)
                ->setProteines(isset($row['proteines']) ? (float) $row['proteines'] : null)
                ->setGlucides(isset($row['glucides']) ? (float) $row['glucides'] : null)
                ->setLipides(isset($row['lipides']) ? (float) $row['lipides'] : null)
                ->setOrigine($row['origine'] ?? null)
                ->setBio(!empty($row['bio']))
                ->setImage($row['image'] ?? null);

            foreach ((array) ($row['genes'] ?? []) as $gName) {
                $g = $geneRepo->findOneBy(['nom' => $gName]);
                if ($g) {
                    $ing->addGene($g);
                }
            }

            $manager->persist($ing);
        }

        $manager->flush();
    }
}
