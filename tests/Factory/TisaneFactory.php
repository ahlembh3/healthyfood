<?php

namespace App\Tests\Factory;

use App\Entity\Tisane;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

final class TisaneFactory extends PersistentObjectFactory
{
    protected function defaults(): array
    {
        return [
            'nom' => self::faker()->unique()->sentence(2),
            'modePreparation' => 'Infuser 5 minutes.',
        ];
    }

    public static function class(): string
    {
        return Tisane::class;
    }
}
