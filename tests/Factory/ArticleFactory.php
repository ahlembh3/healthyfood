<?php

namespace App\Tests\Factory;

use App\Entity\Article;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

final class ArticleFactory extends PersistentObjectFactory
{
    protected function defaults(): array
    {
        return [
            'titre' => self::faker()->sentence(3),
            'contenu' => '<p>'.self::faker()->paragraph(3).'</p>',
            'date' => new \DateTimeImmutable(),
            'validation' => true,
            'categorie' => self::faker()->randomElement(['Bien-être','Nutrition','Plantes','Conseils','Autre']),
            'utilisateur' => UtilisateurFactory::new(),
        ];
    }

    public static function class(): string
    {
        return Article::class;
    }
}
