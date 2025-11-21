<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Commentaire;
use App\Entity\Recette;
use App\Entity\Utilisateur;
use App\Repository\CommentaireRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class CommentaireRepositoryTest extends KernelTestCase
{
    private function uniqueEmail(): string
    {
        return 'test+'.uniqid().'@comment.fr';
    }

    public function test_getMoyenneNoteParRecette(): void
    {
        self::bootKernel();
        $em = static::getContainer()->get('doctrine')->getManager();

        $user = (new Utilisateur())
            ->setEmail($this->uniqueEmail())
            ->setPassword('dummy')
            ->setRoles(['ROLE_USER'])
            ->setNom('User')->setPrenom('Test');
        $em->persist($user);

        $recette = (new Recette())
            ->setTitre('R1')
            ->setInstructions('txt')
            ->setValidation(true)
            ->setUtilisateur($user);
        $em->persist($recette);

        $now = new \DateTimeImmutable();

        $em->persist((new Commentaire())->setUtilisateur($user)->setType(1)->setRecette($recette)->setDate($now)->setNote(4));
        $em->persist((new Commentaire())->setUtilisateur($user)->setType(1)->setRecette($recette)->setDate($now)->setNote(2));
        $em->persist((new Commentaire())->setUtilisateur($user)->setType(2)->setDate($now)->setNote(5));
        $em->persist((new Commentaire())->setUtilisateur($user)->setType(1)->setRecette($recette)->setDate($now)->setNote(null));

        $em->flush();
        $em->clear();

        /** @var CommentaireRepository $repo */
        $repo = static::getContainer()->get(CommentaireRepository::class);

        $rows = $repo->getMoyenneNoteParRecette();

        $this->assertNotEmpty($rows);
        $this->assertEquals(3.0, round((float) $rows[0]['moyenne'], 1));
    }
}
