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

        $this->assertTrue($client->getResponse()->isRedirection());
    }

    public function test_admin_dashboard_acces_admin_ok(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        $admin = (new Utilisateur())
            ->setEmail('admin+'.uniqid().'@test.fr')
            ->setPassword('dummy')
            ->setNom('Admin')->setPrenom('Super')
            ->setRoles(['ROLE_ADMIN']);

        $em->persist($admin);
        $em->flush();

        $client->loginUser($admin);

        $client->request('GET', '/admin/dashboard');

        $this->assertResponseIsSuccessful();
    }
}
