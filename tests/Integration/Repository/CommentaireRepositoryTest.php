<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Commentaire;
use App\Repository\CommentaireRepository;
use App\Tests\Factory\RecetteFactory;
use App\Tests\Factory\UtilisateurFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;
use Zenstruck\Foundry\Test\Factories;

final class CommentaireRepositoryTest extends KernelTestCase
{
    use ResetDatabase;
    use Factories;

    public function test_getMoyenneNoteParRecette_ne_compte_que_type_1_et_notes_non_nulles(): void
    {
        // 1) Boot d'abord pour initialiser le même EM partout
        self::bootKernel();
        $em = self::getContainer()->get('doctrine')->getManager();

        // 2) Crée les entités via Foundry APRÈS le boot (même EM)
        $r = RecetteFactory::new()->create();     // entité persistée
        $u = UtilisateurFactory::new()->create(); // entité persistée

        // 3) Prépare les commentaires
        $now = new \DateTimeImmutable();
        $c1 = (new Commentaire())->setUtilisateur($u)->setType(1)->setRecette($r)->setDate($now)->setNote(4);
        $c2 = (new Commentaire())->setUtilisateur($u)->setType(1)->setRecette($r)->setDate($now)->setNote(2);
        $c3 = (new Commentaire())->setUtilisateur($u)->setType(2)->setDate($now)->setNote(5); // article: ignoré
        $c4 = (new Commentaire())->setUtilisateur($u)->setType(1)->setRecette($r)->setDate($now)->setNote(null);

        foreach ([$c1, $c2, $c3, $c4] as $c) {
            $em->persist($c);
        }
        $em->flush();

        /** @var CommentaireRepository $repo */
        $repo = self::getContainer()->get(CommentaireRepository::class);

        // 4) Act
        $rows = $repo->getMoyenneNoteParRecette();

        // 5) Assert
        $this->assertNotEmpty($rows);
        $this->assertSame($r->getId(), (int) $rows[0]['recette_id']);
        $this->assertEquals(3.0, round((float) $rows[0]['moyenne'], 1));
    }
}
