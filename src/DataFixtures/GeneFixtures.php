<?php

namespace App\DataFixtures;

use App\Entity\Gene;
use App\Entity\Bienfait;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

final class GeneFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        // Charge les Bienfaits d’abord (ton BienfaitFixtures)
        return [BienfaitFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $path = __DIR__ . '/data/genes.json';
        if (!is_file($path)) {
            throw new \RuntimeException("Fichier JSON introuvable : {$path}");
        }

        $rows = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        $bfRepo = $manager->getRepository(Bienfait::class);

        foreach ($rows as $i => $row) {
            $nom = trim((string) ($row['nom'] ?? ''));
            if ($nom === '') {
                throw new \RuntimeException("genes.json: entrée #{$i} sans 'nom'");
            }

            $g = (new Gene())
                ->setNom($nom)
                ->setDescription($row['description'] ?? null);

            foreach ((array) ($row['bienfaits'] ?? []) as $bfName) {
                $bf = $bfRepo->findOneBy(['nom' => $bfName]);
                if ($bf) {
                    $g->addBienfait($bf);
                }
            }

            $manager->persist($g);
        }

        $manager->flush();
    }
}
