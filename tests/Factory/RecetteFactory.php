<?php

namespace App\Tests\Factory;

use App\Entity\Recette;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

final class RecetteFactory extends PersistentObjectFactory
{
    protected function defaults(): array
    {
        return [
            'titre'       => self::faker()->sentence(3),
            'description' => self::faker()->optional()->paragraph(),
            'instructions'=> self::faker()->paragraph(2),
            'validation'  => true,
            'createdAt'   => new \DateTimeImmutable(),
            'difficulte'  => self::faker()->randomElement(['Facile','Moyen','Difficile']),
            'portions'    => 2,
            // très important pour éviter les NOT NULL utilisateur_id
            'utilisateur' => UtilisateurFactory::new(),
        ];
    }

    public static function class(): string
    {
        return Recette::class;
    }


}
