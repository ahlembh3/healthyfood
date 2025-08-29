<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class ArticleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $path = __DIR__ . '/data/articles.json';
        if (!is_file($path)) {
            throw new \RuntimeException("Fichier JSON introuvable : {$path}");
        }

        $rows = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        // Essaye de réutiliser ton admin créé par AppFixtures
        $userRepo = $manager->getRepository(Utilisateur::class);
        $user = $userRepo->findOneBy(['email' => 'admin@example.com']) ?? $userRepo->findOneBy([]);

        // Si aucun utilisateur n'existe encore, on en crée un rapidement pour satisfaire NOT NULL
        if (!$user) {
            $user = (new Utilisateur())
                ->setEmail('editor@healthyfood.local')
                ->setNom('Editor')
                ->setPrenom('Auto')
                ->setPassword('$2y$13$abcdefghijklmnopqrstuv') // hash “bidon”, non utilisé
                ->setRoles(['ROLE_ADMIN']);
            $manager->persist($user);
            $manager->flush();
        }

        $now = new \DateTimeImmutable();

        foreach ($rows as $i => $row) {
            $titre = trim((string) ($row['titre'] ?? ''));
            if ($titre === '') {
                throw new \RuntimeException("articles.json: entrée #{$i} sans 'titre'");
            }

            // Idempotence simple : si un article du même titre existe, on passe
            $exist = $manager->getRepository(Article::class)->findOneBy(['titre' => $titre]);
            if ($exist) {
                continue;
            }

            $categorie = $row['categorie'] ?? 'Autre'; // doit être dans tes Choice (Bien-être, Nutrition, Plantes, Conseils, Autre)
            $contenu   = (string) ($row['contenu'] ?? '');
            if (mb_strlen($contenu) < 20) {
                $contenu .= "\n\n(Contenu enrichi automatiquement pour respecter la contrainte de longueur.)";
            }

            // Date : ISO8601 dans le JSON, sinon on recule de $i jours
            $date = isset($row['date']) ? new \DateTimeImmutable($row['date']) : $now->modify("-{$i} days");

            $article = (new Article())
                ->setTitre($titre)
                ->setCategorie($categorie)
                ->setContenu($contenu)
                ->setDate($date)
                ->setImage($row['image'] ?? null)
                ->setValidation($row['validation'] ?? true)
                ->setUtilisateur($user);

            $manager->persist($article);
        }

        $manager->flush();
    }
}
