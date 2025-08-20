<?php
namespace App\DataFixtures;

use App\Entity\AccordAromatique;
use App\Entity\Bienfait;
use App\Entity\Gene;
use App\Entity\Ingredient;
use App\Entity\Plante;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class SuggestionSeedFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['suggestions']; // pour charger uniquement ce seed
    }

    public function load(ObjectManager $em): void
    {
        // --- 1) GÊNES ---
        $geneNames = ['Lactose','Gluten','Reflux','Ballonnements','Glycémie','Allergie fruits à coque'];
        $genes = [];
        foreach ($geneNames as $name) {
            $g = $em->getRepository(Gene::class)->findOneBy(['nom' => $name]) ?? (new Gene());
            $g->setNom($name);
            $em->persist($g);
            $genes[$name] = $g;
        }

        // --- 2) BIENFAITS (si manquants, on les crée) ---
        $bienfaitNames = ['Digestion','Métabolisme','Respiration','Immunité'];
        $bienfaits = [];
        foreach ($bienfaitNames as $name) {
            $b = $em->getRepository(Bienfait::class)->findOneBy(['nom' => $name]) ?? (new Bienfait());
            $b->setNom($name);
            $em->persist($b);
            $bienfaits[$name] = $b;
        }

        // --- 3) LIEN Gene ↔ Bienfait (ce qui "soulage") ---
        $geneToBienfaits = [
            'Lactose'                  => ['Digestion'],
            'Gluten'                   => ['Digestion'],
            'Ballonnements'            => ['Digestion'],
            'Reflux'                   => ['Digestion'],
            'Glycémie'                 => ['Métabolisme'],
            'Allergie fruits à coque'  => ['Respiration','Immunité'],
        ];
        foreach ($geneToBienfaits as $geneName => $bfList) {
            $g = $genes[$geneName] ?? null; if (!$g) continue;
            foreach ($bfList as $bfName) {
                $b = $bienfaits[$bfName] ?? null; if (!$b) continue;
                if (!$g->getBienfaits()->contains($b)) { $g->addBienfait($b); }
            }
            $em->persist($g);
        }

        // --- 4) LIEN Ingredient ↔ Gene (par nom d’ingrédient) ---
        // ⚠️ On suppose que ces ingrédients existent déjà (depuis ton ingredients.json)
        $ingredientToGenes = [
            'Lait de vache' => ['Lactose','Ballonnements'],
            'Blé'           => ['Gluten','Ballonnements'],
            'Ail'           => ['Reflux','Ballonnements'],
            // 'Oignon'        => ['Reflux','Ballonnements'], // ← retire si absent
            'Miel'          => ['Glycémie'],
            'Citron'        => ['Reflux'],
            'Noix de cajou' => ['Allergie fruits à coque'],
        ];
        foreach ($ingredientToGenes as $ingName => $gList) {
            $ing = $em->getRepository(Ingredient::class)->findOneBy(['nom' => $ingName]);
            if (!$ing) { continue; } // ignore s’il n’existe pas
            foreach ($gList as $gName) {
                $g = $genes[$gName] ?? null; if (!$g) continue;
                if (!$ing->getGenes()->contains($g)) { $ing->addGene($g); }
            }
            $em->persist($ing);
        }

        // --- 5) ACCORDS AROMATIQUES (Plante ↔ Ingredient OU IngredientType) ---
        // ⚠️ On suppose que ces plantes existent déjà (depuis tes données Plante)
        $pairings = [
            // plante,           ingredient,       ingredientType,                 score
            ['Menthe poivrée',   'Concombre',      null,                           1.5],
            ['Menthe poivrée',   'Citron',         null,                           1.0],
            ['Thym',             null,             'Poissons & fruits de mer',     1.5],
            ['Thym',             'Poulet',         null,                           1.2],
            ['Camomille',        'Miel',           null,                           1.3],
            ['Gingembre',        null,             'Poissons & fruits de mer',     1.3],
            ['Gingembre',        'Citron',         null,                           1.2],
            ['Tilleul',          'Citron',         null,                           1.0],
            ['Lavande',          'Miel',           null,                           1.1],
            ['Romarin',          'Poulet',         null,                           1.4],
            ['Romarin',          'Pommes de terre',null,                           1.4],
            // Optionnel : accords "verts" cohérents
            ['Ortie',            'Épinard',        null,                           1.0],
            // Supprimés car plantes absentes : ['Basilic' ↔ 'Tomate'], ['Verveine' ↔ 'Citron']
        ];

        $aaRepo = $em->getRepository(AccordAromatique::class);
        foreach ($pairings as [$planteName, $ingredientName, $type, $score]) {
            $plante = $em->getRepository(Plante::class)->findOneBy(['nomCommun' => $planteName]);
            if (!$plante) { continue; } // skip si la plante n’existe pas

            $ingredient = null;
            if ($ingredientName) {
                $ingredient = $em->getRepository(Ingredient::class)->findOneBy(['nom' => $ingredientName]);
                if (!$ingredient) { continue; } // skip si l’ingrédient n’existe pas
            }

            // éviter les doublons : on cherche par (plante, ingredient) OU (plante, ingredientType)
            if ($ingredient) {
                $exists = $aaRepo->findOneBy(['plante' => $plante, 'ingredient' => $ingredient]);
            } else {
                $exists = $aaRepo->findOneBy(['plante' => $plante, 'ingredientType' => $type]);
            }
            if ($exists) { continue; }

            $aa = new AccordAromatique();
            $aa->setPlante($plante);
            $aa->setIngredient($ingredient);
            $aa->setIngredientType($ingredient ? null : $type);
            $aa->setScore((float)$score);
            $em->persist($aa);
        }

        $em->flush();
    }
}
