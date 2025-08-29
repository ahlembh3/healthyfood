<?php
namespace App\DataFixtures;

use App\Entity\AccordAromatique;
use App\Entity\Plante;
use App\Entity\Ingredient;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

final class AccordAromatiqueFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [PlanteFixtures::class, IngredientFixtures::class];
    }

    private static function norm(string $s): string
    {
        $s = trim(mb_strtolower($s));
        $t = iconv('UTF-8', 'ASCII//TRANSLIT', $s);
        if ($t !== false) { $s = $t; }
        return preg_replace('/[^a-z0-9]+/', '', $s);
    }

    public function load(ObjectManager $em): void
    {
        $path = __DIR__ . '/data/accords.json';
        if (!is_file($path)) {
            throw new \RuntimeException("Fichier JSON introuvable : {$path}");
        }
        $rows = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        // index Plantes par nom commun normalisé
        $plIndex = [];
        foreach ($em->getRepository(Plante::class)->findAll() as $p) {
            $plIndex[self::norm($p->getNomCommun())] = $p;
        }

        // index Ingrédients par nom normalisé
        $ingIndex = [];
        foreach ($em->getRepository(Ingredient::class)->findAll() as $i) {
            $ingIndex[self::norm($i->getNom())] = $i;
        }

        $repo = $em->getRepository(AccordAromatique::class);
        $created = 0; $updated = 0; $skipped = 0;

        foreach ($rows as $r) {
            $plNom = trim((string)($r['plante'] ?? ''));
            if ($plNom === '') { $skipped++; continue; }
            $pl = $plIndex[self::norm($plNom)] ?? null;
            if (!$pl) { $skipped++; continue; }

            $score = isset($r['score']) ? (float)$r['score'] : 1.0;

            // accords par ingrédients précis
            foreach ((array)($r['ingredients'] ?? []) as $ingNom) {
                $ingNom = trim((string)$ingNom);
                $ing = $ingIndex[self::norm($ingNom)] ?? null;
                if (!$ing) { continue; }

                $acc = $repo->findOneBy(['plante' => $pl, 'ingredient' => $ing]) ?? new AccordAromatique();
                $isNew = $acc->getId() === null;

                $acc->setPlante($pl)
                    ->setIngredient($ing)
                    ->setIngredientType(null)
                    ->setScore($score);
                $em->persist($acc);
                $isNew ? $created++ : $updated++;
            }

            // accords par types d’ingrédients
            foreach ((array)($r['types'] ?? []) as $type) {
                $type = trim((string)$type);
                if ($type === '') { continue; }

                $acc = $repo->findOneBy(['plante' => $pl, 'ingredientType' => $type]) ?? new AccordAromatique();
                $isNew = $acc->getId() === null;

                $acc->setPlante($pl)
                    ->setIngredient(null)
                    ->setIngredientType($type)
                    ->setScore($score);
                $em->persist($acc);
                $isNew ? $created++ : $updated++;
            }
        }

        $em->flush();
        echo "[AccordAromatiqueFixtures] created={$created}, updated={$updated}, skipped={$skipped}\n";
    }
}
