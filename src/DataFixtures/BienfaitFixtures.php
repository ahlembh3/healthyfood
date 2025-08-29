<?php

namespace App\DataFixtures;

use App\Entity\Bienfait;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class BienfaitFixtures extends Fixture
{
    public const GROUP = 'bienfaits';

    public function load(ObjectManager $manager): void
    {
        $path = __DIR__ . '/data/bienfaits.json';
        if (!is_file($path)) {
            throw new \RuntimeException("Fichier JSON introuvable : {$path}");
        }

        $data = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data)) {
            throw new \RuntimeException('bienfaits.json invalide');
        }

        $repo = $manager->getRepository(Bienfait::class);
        $count = 0;

        foreach ($data as $row) {
            $nom = trim((string)($row['nom'] ?? ''));
            if ($nom === '') { continue; }

            $desc = isset($row['description']) ? (string)$row['description'] : null;

            // Upsert par nom
            $bf = $repo->findOneBy(['nom' => $nom]) ?? new Bienfait();
            $bf->setNom($nom)->setDescription($desc);

            $manager->persist($bf);
            $count++;
        }

        $manager->flush();
        echo "Bienfaits importés/mis à jour : {$count}\n";
    }
}
