<?php

namespace App\DataFixtures;

use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture implements FixtureGroupInterface
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {}

    /**
     * Permet de charger uniquement ces fixtures via --group=users
     */
    public static function getGroups(): array
    {
        return ['users'];
    }

    public function load(ObjectManager $manager): void
    {
        // Admin
        $admin = new Utilisateur();
        $admin->setEmail('admin@example.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setNom('Admin');
        $admin->setPrenom('Principal');
        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, 'password')
        );
        $manager->persist($admin);

        // Utilisateur standard 1
        $user1 = new Utilisateur();
        $user1->setEmail('user1@example.com');
        $user1->setRoles(['ROLE_USER']);
        $user1->setNom('Dupont');
        $user1->setPrenom('Alice');
        $user1->setPassword(
            $this->passwordHasher->hashPassword($user1, 'password')
        );
        $manager->persist($user1);

        // Utilisateur standard 2 (optionnel)
        $user2 = new Utilisateur();
        $user2->setEmail('user2@example.com');
        $user2->setRoles(['ROLE_USER']);
        $user2->setNom('Martin');
        $user2->setPrenom('Bob');
        $user2->setPassword(
            $this->passwordHasher->hashPassword($user2, 'password')
        );
        $manager->persist($user2);

        $manager->flush();
    }
}
