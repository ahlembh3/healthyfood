<?php

namespace App\Command;

use App\Entity\Recette;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:recettes:link-images',
    description: 'Associe les fichiers image aux recettes selon leur titre (uploads/recettes).'
)]
class LinkRecetteImagesCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dir', null, InputOption::VALUE_REQUIRED, 'Dossier des images (relatif au projet)', 'public/uploads/recettes')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Aperçu sans enregistrer');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io  = new SymfonyStyle($input, $output);
        $dir = rtrim((string) $input->getOption('dir'), "/\\");
        $dry = (bool) $input->getOption('dry-run');

        if (!is_dir($dir)) {
            $io->error("Dossier introuvable : {$dir}");
            return Command::FAILURE;
        }

        $exts = ['jpg', 'jpeg', 'png', 'webp'];
        $recettes = $this->em->getRepository(Recette::class)->findAll();

        $matched = 0; $skipped = 0; $unmatched = 0;

        foreach ($recettes as $r) {
            $titre = (string) $r->getTitre();

            // Si déjà une image et que le fichier existe : on saute.
            if ($r->getImage()) {
                $path = $dir . DIRECTORY_SEPARATOR . $r->getImage();
                if (is_file($path)) { $skipped++; continue; }
            }

            // 1) Essais directs : plusieurs variantes de nommage
            $candidates = $this->buildCandidates($titre);
            $found = $this->findFirstExisting($dir, $candidates, $exts);

            // 2) Recherche floue : normalisation ASCII côté titre et côté fichiers du dossier
            if (!$found) {
                $found = $this->fuzzySearch($dir, $titre, $exts);
            }

            if ($found) {
                $io->writeln("• « {$titre} » → {$found}");
                if (!$dry) {
                    $r->setImage($found); // on stocke seulement le NOM du fichier
                }
                $matched++;
            } else {
                $io->warning("Aucune image pour « {$titre} »");
                $unmatched++;
            }
        }

        if (!$dry) {
            $this->em->flush();
        }

        $io->success("Terminé. Associées: {$matched}, inchangées: {$skipped}, manquantes: {$unmatched}" . ($dry ? ' (dry-run)' : ''));

        return Command::SUCCESS;
    }

    /** @return string[] basenames sans extension (ordre du plus probable au moins probable) */
    private function buildCandidates(string $title): array
    {
        $title = trim($title);

        // Accents conservés
        $keepSpaces   = preg_replace('/[^\p{L}\p{N}\s]+/u', '', $title);                // garde espaces
        $underscores1 = preg_replace('/\s+/', '_', $keepSpaces);                         // espaces -> _
        $underscores2 = preg_replace('/[^\p{L}\p{N}_]+/u', '_', $title);                 // ponctuation -> _
        $underscores2 = preg_replace('/_+/', '_', $underscores2);

        // ASCII (sans accents), minuscules
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $title);
        if ($ascii === false) { $ascii = $title; }
        $ascii = strtolower($ascii);
        $asciiUnderscore = preg_replace('/[^a-z0-9]+/', '_', $ascii);
        $asciiUnderscore = trim($asciiUnderscore, '_');

        // variantes en minuscules
        $underscores1Lower = mb_strtolower($underscores1);
        $underscores2Lower = mb_strtolower($underscores2);

        // dé-doublonnage
        $all = array_values(array_unique(array_filter([
            $keepSpaces,            // ex : "Bol protéiné poulet quinoa"
            $underscores1,          // ex : "Bol_protéiné_poulet_quinoa"
            $underscores1Lower,     // ex : "bol_protéiné_poulet_quinoa"
            $underscores2,          // ex : "Bol_protéiné_poulet_quinoa"
            $underscores2Lower,     // ex : "bol_protéiné_poulet_quinoa"
            $asciiUnderscore,       // ex : "bol_proteine_poulet_quinoa"
        ], fn($v) => $v !== '')));
        return $all;
    }

    /** Renvoie le premier fichier trouvé (basename.ext) parmi des candidats + extensions, sinon null */
    private function findFirstExisting(string $dir, array $candidates, array $exts): ?string
    {
        foreach ($candidates as $base) {
            foreach ($exts as $ext) {
                $file = $base . '.' . $ext;
                if (is_file($dir . DIRECTORY_SEPARATOR . $file)) {
                    return $file;
                }
            }
        }
        return null;
    }

    /** Recherche floue : on normalise titre et basenames des fichiers du dossier, on compare */
    private function fuzzySearch(string $dir, string $title, array $exts): ?string
    {
        $needle = $this->normalizeAscii($title);
        foreach ($exts as $ext) {
            foreach (glob($dir . DIRECTORY_SEPARATOR . '*.' . $ext) as $path) {
                $base = pathinfo($path, PATHINFO_FILENAME);
                if ($this->normalizeAscii($base) === $needle) {
                    return basename($path);
                }
            }
        }
        return null;
    }

    private function normalizeAscii(string $s): string
    {
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
        if ($ascii === false) { $ascii = $s; }
        $ascii = strtolower($ascii);
        $ascii = preg_replace('/[^a-z0-9]+/', '_', $ascii);
        return trim($ascii, '_');
    }
}
