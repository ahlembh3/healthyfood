<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Commentaire;
use App\Entity\Recette;
use App\Entity\Utilisateur;
use App\Repository\CommentaireRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class CommentaireRepositoryTest extends KernelTestCase
{
    public function test_getMoyenneNoteParRecette_ne_compte_que_type_1_et_notes_non_nulles(): void
    {
        self::bootKernel();
        $em = self::getContainer()->get('doctrine')->getManager();

        $user = new Utilisateur();
        $user
            ->setEmail('commentaire-user@example.test')
            ->setPassword('dummy')
            ->setRoles(['ROLE_USER'])
            ->setNom('User Commentaire')   // <-- IMPORTANT
            ->setPrenom('Test');

        $em->persist($user);


        $r = (new Recette())
            ->setTitre('Recette notée')
            ->setInstructions('Texte')
            ->setValidation(true)
            ->setUtilisateur($user);

        $em->persist($r);

        $now = new \DateTimeImmutable();

        $c1 = (new Commentaire())
            ->setUtilisateur($user)
            ->setType(1)
            ->setRecette($r)
            ->setDate($now)
            ->setNote(4);

        $c2 = (new Commentaire())
            ->setUtilisateur($user)
            ->setType(1)
            ->setRecette($r)
            ->setDate($now)
            ->setNote(2);

        $c3 = (new Commentaire())
            ->setUtilisateur($user)
            ->setType(2) // Article : ignoré
            ->setDate($now)
            ->setNote(5);

        $c4 = (new Commentaire())
            ->setUtilisateur($user)
            ->setType(1)
            ->setRecette($r)
            ->setDate($now)
            ->setNote(null); // note null : ignorée

        foreach ([$c1, $c2, $c3, $c4] as $c) {
            $em->persist($c);
        }

        $em->flush();
        $em->clear();

        /** @var CommentaireRepository $repo */
        $repo = self::getContainer()->get(CommentaireRepository::class);
        $rows = $repo->getMoyenneNoteParRecette();

        $this->assertNotEmpty($rows);
        $this->assertSame($r->getId(), (int) $rows[0]['recette_id']);
        $this->assertEquals(3.0, round((float) $rows[0]['moyenne'], 1));
    }
}
