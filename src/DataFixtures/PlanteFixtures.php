<?php

namespace App\DataFixtures;

use App\Entity\Plante;
use App\Entity\Bienfait;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;


final class PlanteFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        // S’assure que les Bienfaits sont chargés avant
        return [BienfaitFixtures::class];
    }

    /** Normalise un nom pour la recherche (sans accents, casse, espaces/ponctuation) */
    private static function norm(string $s): string
    {
        $s = trim(mb_strtolower($s));
        $s = iconv('UTF-8', 'ASCII//TRANSLIT', $s) ?: $s;
        return preg_replace('/[^a-z0-9]+/', '', $s);
    }

    public function load(ObjectManager $em): void
    {
        $path = __DIR__ . '/data/plantes.json';
        if (!is_file($path)) {
            throw new \RuntimeException("Fichier JSON introuvable : {$path}");
        }

        $rows = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        // Map des bienfaits existants (par NOM exact)
        $bfRepo = $em->getRepository(Bienfait::class);
        $bienfaitsAll = $bfRepo->findAll();
        $mapBienfaits = [];
        foreach ($bienfaitsAll as $bf) {
            $mapBienfaits[$bf->getNom()] = $bf;
        }

        // Map des plantes existantes (par nomCommun normalisé)
        $plRepo = $em->getRepository(Plante::class);
        $mapPlantes = [];
        foreach ($plRepo->findAll() as $p) {
            $mapPlantes[self::norm((string) $p->getNomCommun())] = $p;
        }

        foreach ($rows as $i => $row) {
            $nomCommun       = trim((string) ($row['nomCommun']       ?? ''));
            $nomScientifique = trim((string) ($row['nomScientifique'] ?? $nomCommun));
            $description     = trim((string) ($row['description']     ?? 'Plante médicinale.'));
            $partieUtilisee  = trim((string) ($row['partieUtilisee']  ?? 'Parties aériennes'));
            $precautions     = trim((string) ($row['precautions']     ?? "À consommer avec modération. Demandez l’avis d’un professionnel de santé en cas de doute."));
            $image           = $row['image'] ?? null; // nullable

            if ($nomCommun === '') {
                throw new \RuntimeException("plantes.json: entrée #{$i} sans nomCommun");
            }

            $key = self::norm($nomCommun);
            $plante = $mapPlantes[$key] ?? null;

            if (!$plante) {
                // CRÉATION
                $plante = new Plante();
                $plante->setNomCommun($nomCommun);
                $em->persist($plante);
                // mémorise tout de suite dans la map pour éviter re-créations dans la même passe
                $mapPlantes[$key] = $plante;
            }

            // MISE À JOUR (on écrase avec le JSON, plus simple et cohérent)
            $plante
                ->setNomScientifique($nomScientifique !== '' ? $nomScientifique : $plante->getNomScientifique() ?? $nomCommun)
                ->setDescription($description !== '' ? $description : ($plante->getDescription() ?? 'Plante médicinale.'))
                ->setPartieUtilisee($partieUtilisee !== '' ? $partieUtilisee : ($plante->getPartieUtilisee() ?? 'Parties aériennes'))
                ->setPrecautions($precautions !== '' ? $precautions : ($plante->getPrecautions() ?? null))
                ->setImage($image ?: $plante->getImage());

            // Bienfaits (évite les doublons)
            $wanted = (array) ($row['bienfaits'] ?? []);
            foreach ($wanted as $nomBf) {
                $nomBf = trim((string) $nomBf);
                if ($nomBf === '' || !isset($mapBienfaits[$nomBf])) {
                    continue;
                }
                $bf = $mapBienfaits[$nomBf];
                if (!$plante->getBienfaits()->contains($bf)) {
                    $plante->addBienfait($bf);
                }
            }
        }

        $em->flush();
    }
}
