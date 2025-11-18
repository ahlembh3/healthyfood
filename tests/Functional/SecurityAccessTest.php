<?php

namespace App\Tests\Functional;

use App\Entity\Utilisateur;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SecurityAccessTest extends WebTestCase
{
    public function test_admin_dashboard_interdit_aux_anonymes(): void
    {
        $client = static::createClient();

        $client->request('GET', '/admin/dashboard');

        $this->assertTrue(
            $client->getResponse()->isRedirection(),
            'Un anonyme doit être redirigé (vers /login).'
        );
    }

    public function test_admin_dashboard_acces_admin_ok(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        $admin = new Utilisateur();
        $admin
            ->setEmail('admin@test.local')
            ->setPassword('dummy')
            ->setRoles(['ROLE_ADMIN'])
            ->setNom('Admin Test')          // <-- IMPORTANT
            ->setPrenom('Superadmin');      // <-- idem


        $em->persist($admin);
        $em->flush();

        $client->loginUser($admin);

        $client->request('GET', '/admin/dashboard');

        $this->assertTrue($client->getResponse()->isOk());
    }
}
