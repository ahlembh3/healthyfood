<?php

namespace App\DataFixtures;

use App\Entity\Ingredient;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

class IngredientFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['ingredients']; // permettra de charger uniquement ce groupe
    }

    public function load(ObjectManager $manager): void
    {
        $path = __DIR__ . '/data/ingredients.json';
        if (!is_file($path)) {
            throw new \RuntimeException("Fichier JSON introuvable : $path");
        }

        $rows = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        $repo = $manager->getRepository(Ingredient::class);

        foreach ($rows as $i => $row) {
            // valeurs par défaut + protections
            $row = array_merge([
                'nom'          => null,
                'description'  => null,
                'unite'        => 'gramme',
                'calories'     => null,
                'proteines'    => null,
                'glucides'     => null,
                'lipides'      => null,
                'origine'      => null,
                'bio'          => 0,
                'image'        => null,
                'type'         => null,
                'allergenes'   => '',
                'saisonnalite' => null,
            ], $row);

            if (!$row['nom']) {
                throw new \RuntimeException("Ligne #$i : champ 'nom' manquant");
            }


            $ingredient = $repo->findOneBy(['nom' => $row['nom']]) ?? new Ingredient();

            $ingredient->setNom($row['nom']);
            $ingredient->setDescription($row['description']);
            $ingredient->setUnite($row['unite']);
            $ingredient->setCalories(self::toNullableFloat($row['calories']));
            $ingredient->setProteines(self::toNullableFloat($row['proteines']));
            $ingredient->setGlucides(self::toNullableFloat($row['glucides']));
            $ingredient->setLipides(self::toNullableFloat($row['lipides']));
            $ingredient->setOrigine($row['origine']);
            $ingredient->setBio((int) $row['bio'] === 1 ? 1 : 0);
            $ingredient->setImage($row['image']);
            $ingredient->setType($row['type']);
            $ingredient->setAllergenes($row['allergenes']);
            $ingredient->setSaisonnalite($row['saisonnalite']);

            $manager->persist($ingredient);
        }

        $manager->flush();
    }

    private static function toNullableFloat(mixed $v): ?float
    {
        if ($v === null || $v === '' || $v === 'NULL') return null;
        return is_numeric($v) ? (float)$v : null;
    }
}
