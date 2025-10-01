<?php

namespace App\Tests\Factory;

use App\Entity\Utilisateur;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

final class UtilisateurFactory extends PersistentObjectFactory
{
    protected function defaults(): array
    {
        return [
            'email' => self::faker()->unique()->safeEmail(),
            'password' => 'hash-not-checked-in-tests',
            'nom' => self::faker()->lastName(),
            'prenom' => self::faker()->firstName(),
            'roles' => ['ROLE_USER'],
        ];
    }

    public static function class(): string
    {
        return Utilisateur::class;
    }

    public function asAdmin(): self
    {
        // Foundry v2.0 : on utilise with(...) pour un "state"
        return $this->with(['roles' => ['ROLE_ADMIN']]);
    }
}
