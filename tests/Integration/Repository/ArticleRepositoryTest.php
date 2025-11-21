<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Article;
use App\Entity\Utilisateur;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ArticleRepositoryTest extends KernelTestCase
{
    private function createUser(\Doctrine\ORM\EntityManagerInterface $em, string $prefixEmail = 'user'): Utilisateur
    {
        $u = new Utilisateur();
        $email = $prefixEmail . '+' . uniqid() . '@example.test';

        $u->setEmail($email)
            ->setPassword('dummy')
            ->setRoles(['ROLE_USER'])
            ->setNom('Test')
            ->setPrenom('User');

        $em->persist($u);

        return $u;
    }



    public function test_findLatestValidated_ne_retourne_que_valides_ordonnees_par_date_desc(): void
    {
        self::bootKernel();
        $em = self::getContainer()->get('doctrine')->getManager();

        $user = $this->createUser($em, 'article-user@example.test');

        $a1 = (new Article())
            ->setTitre('Article validé hier')
            ->setContenu(str_repeat('A', 30))
            ->setDate(new \DateTimeImmutable('-1 day'))
            ->setValidation(true)
            ->setCategorie('Plantes')
            ->setUtilisateur($user);

        $a2 = (new Article())
            ->setTitre('Article non validé aujourd’hui')
            ->setContenu(str_repeat('B', 30))
            ->setDate(new \DateTimeImmutable('now'))
            ->setValidation(false)
            ->setCategorie('Cache')
            ->setUtilisateur($user);

        $a3 = (new Article())
            ->setTitre('Article validé aujourd’hui')
            ->setContenu(str_repeat('C', 30))
            ->setDate(new \DateTimeImmutable('now'))
            ->setValidation(true)
            ->setCategorie('Nutrition')
            ->setUtilisateur($user);

        foreach ([$a1, $a2, $a3] as $a) {
            $em->persist($a);
        }

        $em->flush();
        $em->clear();

        /** @var ArticleRepository $repo */
        $repo = self::getContainer()->get(ArticleRepository::class);
        $rows = $repo->findLatestValidated(2);

        $this->assertCount(2, $rows);
        $this->assertTrue($rows[0]->isValidation());
        $this->assertTrue($rows[1]->isValidation());
        $this->assertGreaterThanOrEqual($rows[1]->getDate(), $rows[0]->getDate());
    }

    public function test_getAvailableCategories_ignore_null_et_vide(): void
    {
        self::bootKernel();
        $em = self::getContainer()->get('doctrine')->getManager();

        $user = $this->createUser($em, 'article-cats@example.test');

        $a1 = (new Article())
            ->setTitre('Article Plantes')
            ->setContenu(str_repeat('P', 30))
            ->setDate(new \DateTimeImmutable())
            ->setValidation(true)
            ->setCategorie('Plantes')
            ->setUtilisateur($user);

        $a2 = (new Article())
            ->setTitre('Article Nutrition')
            ->setContenu(str_repeat('N', 30))
            ->setDate(new \DateTimeImmutable())
            ->setValidation(true)
            ->setCategorie('Nutrition')
            ->setUtilisateur($user);

        $a3 = (new Article())
            ->setTitre('Article caché')
            ->setContenu(str_repeat('C', 30))
            ->setDate(new \DateTimeImmutable())
            ->setValidation(false)
            ->setCategorie('Cache')
            ->setUtilisateur($user);

        foreach ([$a1, $a2, $a3] as $a) {
            $em->persist($a);
        }

        $em->flush();
        $em->clear();

        /** @var ArticleRepository $repo */
        $repo = self::getContainer()->get(ArticleRepository::class);
        $cats = $repo->getAvailableCategories();

        $this->assertContains('Plantes', $cats);
        $this->assertContains('Nutrition', $cats);
        $this->assertNotContains('Cache', $cats);
    }
}
