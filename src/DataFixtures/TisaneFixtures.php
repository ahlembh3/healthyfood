<?php

namespace App\DataFixtures;

use App\Entity\Tisane;
use App\Entity\Plante;
use App\Entity\Bienfait;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class TisaneFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [PlanteFixtures::class, BienfaitFixtures::class];
    }

    /** normalisation ASCII compacte */
    private function norm(string $s): string
    {
        $s = trim(mb_strtolower($s));
        $t = iconv('UTF-8', 'ASCII//TRANSLIT', $s);
        if ($t !== false) { $s = $t; }
        return preg_replace('/[^a-z0-9]+/', '', $s);
    }

    private function imageMono(string $planteNom): string
    {
        $b = mb_strtolower($planteNom);
        $t = iconv('UTF-8', 'ASCII//TRANSLIT', $b);
        if ($t !== false) { $b = $t; }
        $b = preg_replace('/[^a-z0-9]+/', '_', $b);
        $b = trim($b, '_');
        return 'Tisane_de_' . $b . '.png';
    }

    private function imageMixFromTitle(string $title): string
    {
        $base = preg_replace('/\s*\(.*?\)\s*/u', '', $title);
        $base = str_replace(['-', '—'], ' ', $base);
        $base = preg_replace('/\s+/', ' ', $base);
        $base = trim($base);
        $base = str_replace([' ', '/', '\\', '"', "'", ':', ';', ',', '!', '?', '.'], '_', $base);
        $base = preg_replace('/_+/', '_', $base);
        return $base . '.png';
    }

    public function load(ObjectManager $em): void
    {
        $path = __DIR__ . '/data/tisanes.json';
        if (!is_file($path)) {
            throw new \RuntimeException("Fichier JSON introuvable : {$path}");
        }
        $rows = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        // ===== index plantes : nom normalisé, 1er mot, alias connus, et correspondances partielles =====
        /** @var Plante[] $plantesAll */
        $plantesAll = $em->getRepository(Plante::class)->findAll();
        $index = [];

        // alias explicites JSON -> BDD
        $alias = [
            'camomille' => 'camomillematricaire',
            'verveine'  => 'verveineodorante',
            'hibiscus'  => 'hibiscussabdariffa',
            'lavande'   => 'lavandevraie',
            'menthepoivree' => 'menthepoivree',
            'tilleul' => 'tilleul',
            'fenouil' => 'fenouil',
            'citronnelle' => 'citronnelle',
            'gingembre' => 'gingembre',
            'sauge' => 'saugeofficinale',
            'romarin' => 'romarin',
            'thym' => 'thym'
        ];

        foreach ($plantesAll as $p) {
            $nc = $this->norm($p->getNomCommun());     // ex: camomillematricaire
            $index[$nc] = $p;

            // 1er mot du nom commun (ex: "Camomille matricaire" -> "camomille")
            $first = strtok(mb_strtolower($p->getNomCommun()), " \t\n\r\0\x0B");
            if ($first) {
                $nf = $this->norm($first);
                $index[$nf] = $p;
            }

            // alias inverses : si la plante correspond à un alias cible, on mappe l'alias source
            foreach ($alias as $src => $dst) {
                if ($dst === $nc) { $index[$src] = $p; }
            }
        }

        // ===== index bienfaits =====
        $mapBienfaits = [];
        foreach ($em->getRepository(Bienfait::class)->findAll() as $b) {
            $mapBienfaits[$this->norm($b->getNom())] = $b;
        }

        $tisaneRepo = $em->getRepository(Tisane::class);
        $nbLinked = 0; $nbTisanes = 0; $missing = [];

        foreach ($rows as $r) {
            $nom = trim((string)($r['nom'] ?? ''));
            if ($nom === '') continue;

            /** @var Tisane|null $tisane */
            $tisane = $tisaneRepo->findOneBy(['nom' => $nom]) ?: new Tisane();
            $tisane->setNom($nom);

            // reset relations pour repartir propre
            foreach ($tisane->getPlantes()->toArray() as $old) { $tisane->removePlante($old); }
            foreach ($tisane->getBienfaits()->toArray() as $old) { $tisane->removeBienfait($old); }

            // champs texte
            if (!empty($r['modePreparation'])) $tisane->setModePreparation($r['modePreparation']);
            $tisane->setDosage($r['dosage'] ?? null);
            $tisane->setPrecautions($r['precautions'] ?? null);

            // ===== résolution des plantes =====
            $resolved = [];
            foreach ((array)($r['plantes'] ?? []) as $pn) {
                $k = $this->norm($pn);
                $pl = $index[$k] ?? null;

                // Si pas trouvé par nom exact, chercher par correspondance partielle
                if (!$pl) {
                    foreach ($plantesAll as $cand) {
                        $candNorm = $this->norm($cand->getNomCommun());

                        // 1. Vérifier si le nom recherché est contenu dans le nom complet
                        if (str_contains($candNorm, $k)) {
                            $pl = $cand;
                            break;
                        }

                        // 2. Vérifier si le nom complet est contenu dans le nom recherché
                        if (str_contains($k, $candNorm)) {
                            $pl = $cand;
                            break;
                        }

                        // 3. Vérifier par premier mot
                        $firstWord = strtok(mb_strtolower($cand->getNomCommun()), " \t\n\r\0\x0B");
                        $firstWordNorm = $this->norm($firstWord);
                        if ($firstWordNorm === $k) {
                            $pl = $cand;
                            break;
                        }
                    }
                }

                if ($pl && !in_array($pl, $resolved, true)) {
                    $resolved[] = $pl;
                    $tisane->addPlante($pl);
                }
            }

            if (!$resolved) {
                $missing[] = $nom . ' => [' . implode(', ', (array)($r['plantes'] ?? [])) . ']';
            } else {
                $nbLinked += count($resolved);
            }

            // bienfaits
            foreach ((array)($r['bienfaits'] ?? []) as $bn) {
                $bk = $this->norm($bn);
                if (isset($mapBienfaits[$bk])) $tisane->addBienfait($mapBienfaits[$bk]);
            }

            // image auto si vide
            if (!$tisane->getImage() || $tisane->getImage() === '') {
                $tisane->setImage(count($resolved) === 1
                    ? $this->imageMono($resolved[0]->getNomCommun())
                    : $this->imageMixFromTitle($nom)
                );
            }

            $em->persist($tisane);
            $nbTisanes++;
        }

        $em->flush();

        // petit log en sortie console (non bloquant)
        echo "[TisaneFixtures] Tisanes traitées: {$nbTisanes}, liens plantes créés: {$nbLinked}\n";
        if ($missing) {
            echo "[TisaneFixtures] ATTENTION, plantes introuvables pour:\n - " . implode("\n - ", $missing) . "\n";
        }
    }
}