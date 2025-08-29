<?php

namespace App\Command;

use App\Repository\PlanteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'app:link-plants-images',
    description: 'Lie les fichiers de /public/uploads/plantes aux plantes (colonne image) par ID ou par slug du nomCommun, avec correspondances partielles.'
)]
class LinkPlantImagesCommand extends Command
{
    private string $plantesDir;

    public function __construct(
        private readonly PlanteRepository $repo,
        private readonly EntityManagerInterface $em,
        private readonly SluggerInterface $slugger,
        ParameterBagInterface $bag
    ) {
        parent::__construct();

        $this->plantesDir = $bag->has('plantes_directory')
            ? (string) $bag->get('plantes_directory')
            : rtrim((string) $bag->get('kernel.project_dir'), DIRECTORY_SEPARATOR) . '/public/uploads/plantes';
    }

    protected function configure(): void
    {
        $this
            ->addOption('mode', null, InputOption::VALUE_REQUIRED, 'id|slug', 'slug')
            ->addOption('exts', null, InputOption::VALUE_REQUIRED, 'Extensions à tester (csv)', 'jpg,jpeg,png,webp')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Aperçu sans écrire en base')
            ->addOption('overwrite', null, InputOption::VALUE_NONE, 'Écraser la valeur image déjà présente');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io        = new SymfonyStyle($input, $output);
        $mode      = strtolower((string) $input->getOption('mode'));
        $exts      = array_values(array_filter(array_map(
            fn($e) => strtolower(trim($e)),
            explode(',', (string) $input->getOption('exts'))
        )));
        $dry       = (bool) $input->getOption('dry-run');
        $overwrite = (bool) $input->getOption('overwrite');

        if (!is_dir($this->plantesDir)) {
            $io->error("Dossier introuvable : {$this->plantesDir}");
            return Command::FAILURE;
        }

        $io->title('Lien des images de plantes');
        $io->writeln("Dossier : {$this->plantesDir}");
        $io->writeln("Mode   : {$mode}");
        $io->writeln("DryRun : " . ($dry ? 'oui' : 'non'));
        $io->newLine();

        $plantes  = $this->repo->findAll();
        $updated  = 0;
        $skipped  = 0;
        $notFound = 0;

        foreach ($plantes as $plante) {
            // si image déjà présente et pas overwrite -> on ignore
            if (!$overwrite && method_exists($plante, 'getImage') && $plante->getImage()) {
                $skipped++;
                continue;
            }

            $found = null;

            if ($mode === 'id') {
                $found = $this->findById((string) $plante->getId(), $exts);
            } else {
                $name = (string) ($plante->getNomCommun() ?? '');
                $found = $this->findBySlugLike($name, $exts);
            }

            if ($found) {
                $io->writeln(sprintf('✓ %s -> %s',
                    $plante->getNomCommun() ?: ('#' . $plante->getId()),
                    $found
                ));
                if (!$dry) {
                    $plante->setImage($found);
                }
                $updated++;
            } else {
                $io->writeln(sprintf('… %s -> aucune image trouvée',
                    $plante->getNomCommun() ?: ('#' . $plante->getId())
                ));
                $notFound++;
            }
        }

        if (!$dry) {
            $this->em->flush();
        }

        $io->success(sprintf('Terminé. MAJ: %d | ignorées: %d | introuvables: %d', $updated, $skipped, $notFound));
        return Command::SUCCESS;
    }

    /** Recherche stricte par ID : {id}.{ext} */
    private function findById(string $id, array $exts): ?string
    {
        foreach ($exts as $ext) {
            $file = $id . '.' . $ext;
            if (is_file($this->plantesDir . DIRECTORY_SEPARATOR . $file)) {
                return $file;
            }
        }
        return null;
    }

    /**
     * Recherche par nom commun « style slug », avec variantes :
     * - slug complet (ex: menthe-poivree) + underscores
     * - premières/dernières parties (ex: menthe, poivree)
     * - combinaison premières 2 parties (ex: menthe-poivree)
     * - suffixes -1 / _1
     * - fallback fuzzy: *token*.(jpg|png…)
     */
    private function findBySlugLike(string $name, array $exts): ?string
    {
        $slug = strtolower((string) $this->slugger->slug($name));      // menthe-poivree
        $slugUs = str_replace('-', '_', $slug);                         // menthe_poivree

        // Découper en tokens (sans petits mots très fréquents)
        $stop = ['de','du','des','la','le','les','l','d','et','a','au','aux','en','pour','sur'];
        $tokens = array_values(array_filter(
            preg_split('/[-_ ]+/', $slug) ?: [],
            fn($t) => $t !== '' && !in_array($t, $stop, true)
        ));

        // 1) Candidats stricts et immédiats (les plus probables d’abord)
        $bases = [];
        if ($slug)   { $bases[] = $slug; $bases[] = $slugUs; }
        if (!empty($tokens)) {
            $bases[] = $tokens[0];                                        // premier mot
            if (count($tokens) >= 2) {
                $bases[] = $tokens[0] . '-' . $tokens[1];                 // deux premiers
                $bases[] = $tokens[0] . '_' . $tokens[1];
            }
            $bases[] = end($tokens);                                      // dernier mot
            foreach ($tokens as $t) {                                     // chaque mot seul
                $bases[] = $t;
            }
        }
        // variantes -1 / _1
        $withNum = [];
        foreach ($bases as $b) {
            $withNum[] = $b . '-1';
            $withNum[] = $b . '_1';
        }
        $bases = array_values(array_unique(array_merge($bases, $withNum)));

        // Tester les candidats exacts
        foreach ($bases as $base) {
            foreach ($exts as $ext) {
                $file = $base . '.' . $ext;
                if (is_file($this->plantesDir . DIRECTORY_SEPARATOR . $file)) {
                    return $file;
                }
            }
        }

        // 2) Fallback fuzzy : *token*.(ext) (utile quand on n’a mis qu’un seul mot)
        foreach ($tokens as $t) {
            foreach ($exts as $ext) {
                $glob = $this->plantesDir . DIRECTORY_SEPARATOR . '*' . $t . '*.' . $ext;
                $matches = glob($glob);
                if (!empty($matches)) {
                    return basename($matches[0]); // premier match suffit
                }
            }
        }

        return null;
    }
}
