<?php

namespace App\Tests\Factory;

use App\Entity\Plante;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

final class PlanteFactory extends PersistentObjectFactory
{
    protected function defaults(): array
    {
        return [
            'nomCommun' => self::faker()->unique()->words(2, true),
            'nomScientifique' => self::faker()->words(2, true),
            'description' => self::faker()->paragraph(),
            'partieUtilisee' => 'Feuilles',
            'precautions' => 'Déconseillé aux enfants.',
        ];
    }

    public static function class(): string
    {
        return Plante::class;
    }
}
