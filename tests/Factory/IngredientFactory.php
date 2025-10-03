<?php

namespace App\Tests\Factory;

use App\Entity\Ingredient;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

final class IngredientFactory extends PersistentObjectFactory
{
    protected function defaults(): array
    {
        return [
            'nom' => self::faker()->unique()->word(),
            'unite' => 'g',
            'calories' => 400,
            'type' => self::faker()->randomElement(['Poisson','Volaille','Légume']),
        ];
    }

    public static function class(): string
    {
        return Ingredient::class;
    }
}
