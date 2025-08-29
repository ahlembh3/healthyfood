<?php
namespace App\Command;

use App\Entity\Tisane;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'app:tisane:link-images',
    description: 'Associe les fichiers du dossier public/uploads/tisanes au champ image des tisanes (gère underscores, accents, parenthèses…).'
)]
class LinkTisaneImagesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private KernelInterface $kernel
    ){
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dir', null, InputOption::VALUE_REQUIRED, 'Dossier des images', null)
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Prévisualisation sans écriture BDD');
    }

    private static function noAccents(string $s): string
    {
        $t = iconv('UTF-8','ASCII//TRANSLIT',$s);
        return $t !== false ? $t : $s;
    }

    private static function normalize(string $s, string $sep = '-'): string
    {
        // supprimer le contenu entre parenthèses pour matcher "Après_repas_apaisant"
        $s = preg_replace('/\s*\(.*\)\s*/u', '', $s);
        // unifier espaces et séparateurs
        $s = trim(mb_strtolower($s));
        $s = self::noAccents($s);
        $s = strtr($s, [
            '&' => ' et ',
            '/' => $sep, '\\' => $sep, '—' => '-', '–' => '-', '’' => '', "'" => '',
        ]);
        // enlever "tisane de/du/d’" optionnel (on gardera aussi une variante avec)
        $sSansPrefix = preg_replace('/^tisane\s*d[eu’]\s*/u', '', $s);
        // réduire au charset voulu
        $cleanup = fn(string $x) => trim(preg_replace('/[^a-z0-9]+/',$sep, $x), $sep);
        return $cleanup($sSansPrefix);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io  = new SymfonyStyle($input, $output);
        $dir = $input->getOption('dir') ?: $this->kernel->getProjectDir().'/public/uploads/tisanes';
        $dry = (bool) $input->getOption('dry-run');

        if (!is_dir($dir)) {
            $io->error("Dossier introuvable : $dir");
            return Command::FAILURE;
        }

        // Index des fichiers: on crée plusieurs clés par fichier pour matcher facilement
        $allowed = ['png','jpg','jpeg','webp','avif'];
        $fileMap = []; // key => filename.ext

        foreach (scandir($dir) as $f) {
            if ($f === '.' || $f === '..') continue;
            $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed, true)) continue;

            $baseRaw = pathinfo($f, PATHINFO_FILENAME);
            $keys = [];

            // clés possibles (toutes en lowercase)
            $keys[] = mb_strtolower($baseRaw);                          // ex: "tisane_de_camomille"
            $keys[] = mb_strtolower(self::noAccents($baseRaw));         // ex: "apres_repas_apaisant"
            $keys[] = self::normalize($baseRaw, '_');                   // ex: "apres_repas_apaisant"
            $keys[] = self::normalize($baseRaw, '-');                   // ex: "apres-repas-apaisant"

            // variante sans le préfixe "tisane_de_"
            $tmp = preg_replace('/^tisane[_\- ]*d[eu]_/','', mb_strtolower(self::noAccents($baseRaw)));
            if ($tmp !== null && $tmp !== '') $keys[] = $tmp;

            foreach (array_unique($keys) as $k) {
                if (!isset($fileMap[$k])) {
                    $fileMap[$k] = $f;
                }
            }
        }

        if (!$fileMap) {
            $io->warning('Aucune image trouvée dans '.$dir);
            return Command::SUCCESS;
        }

        $repo = $this->em->getRepository(Tisane::class);
        $tisanes = $repo->findAll();

        $set=0; $same=0; $miss=[];

        foreach ($tisanes as $t) {
            $name = (string) $t->getNom();

            // candidats basés sur ton conventionnement
            $beforeParen = preg_replace('/\s*\(.*\)\s*/u', '', $name); // "Après-repas apaisant"
            $cand = [];

            // 1) forme underscore façon fichiers fournis
            $cand[] = mb_strtolower(self::noAccents(str_replace(' ', '_', $beforeParen))); // "apres-repas apaisant" -> "apres-repas_apaisant"
            $cand[] = mb_strtolower(self::noAccents(str_replace([' ', '-'], '_', $beforeParen)));
            // 2) avec préfixe "Tisane_de_xxx" pour les mono-plante
            $cand[] = mb_strtolower(self::noAccents('tisane_de_'.preg_replace('/^tisane\s*d[eu’]\s*/iu','', str_replace([' ', '-'], '_', $name))));
            // 3) normalisations plus génériques
            $cand[] = self::normalize($name, '_');
            $cand[] = self::normalize($name, '-');

            // rechercher une correspondance
            $found = null;
            foreach (array_unique($cand) as $k) {
                if (isset($fileMap[$k])) { $found = $fileMap[$k]; break; }
                // tolérance "début de mot"
                foreach ($fileMap as $kk => $file) {
                    if (str_starts_with($kk, $k) || str_starts_with($k, $kk)) { $found = $file; break; }
                }
                if ($found) break;
            }

            if ($found) {
                if ($t->getImage() !== $found) {
                    if (!$dry) $t->setImage($found);
                    $io->writeln("• {$name}  →  {$found}");
                    $set++;
                } else {
                    $same++;
                }
            } else {
                $miss[] = $name;
            }
        }

        if (!$dry) $this->em->flush();

        $io->success("Mises à jour: $set, inchangées: $same, introuvables: ".count($miss));
        if ($miss) {
            $io->section("Non trouvées (renomme les fichiers pour correspondre) :");
            foreach ($miss as $m) $io->writeln(" - $m");
        }

        return Command::SUCCESS;
    }
}
