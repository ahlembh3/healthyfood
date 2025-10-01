<?php

namespace App\Tests\Integration\Repository;

use App\Repository\ArticleRepository;
use App\Tests\Factory\ArticleFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class ArticleRepositoryTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    public function test_findLatestValidated_ne_retourne_que_valides_ordonnees_par_date_desc(): void
    {
        // Arrange
        ArticleFactory::new(['validation' => true,  'date' => new \DateTimeImmutable('-1 day')])->create();
        ArticleFactory::new(['validation' => false, 'date' => new \DateTimeImmutable('now')])->create();
        ArticleFactory::new(['validation' => true,  'date' => new \DateTimeImmutable('now')])->create();

        self::bootKernel();
        $repo = self::getContainer()->get(ArticleRepository::class);

        // Act
        $rows = $repo->findLatestValidated(2);

        // Assert
        $this->assertCount(2, $rows);
        $this->assertTrue($rows[0]->isValidation());
        $this->assertGreaterThanOrEqual($rows[1]->getDate(), $rows[0]->getDate());
    }

    public function test_getAvailableCategories_ignore_null_et_vide(): void
    {
        // Arrange
        ArticleFactory::new(['validation' => true,  'categorie' => 'Plantes'])->create();
        ArticleFactory::new(['validation' => true,  'categorie' => 'Nutrition'])->create();
        ArticleFactory::new(['validation' => false, 'categorie' => 'Cache'])->create();

        self::bootKernel();
        $repo = self::getContainer()->get(ArticleRepository::class);

        // Act
        $cats = $repo->getAvailableCategories();

        // Assert
        $this->assertContains('Plantes', $cats);
        $this->assertContains('Nutrition', $cats);
        $this->assertNotContains('Cache', $cats);
    }
}
