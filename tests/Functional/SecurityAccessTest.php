<?php

namespace App\Tests\Functional;

use App\Tests\Factory\UtilisateurFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;

final class SecurityAccessTest extends WebTestCase
{
    use Factories;

    public function test_admin_dashboard_interdit_aux_anonymes(): void
    {
        // Arrange
        $client = static::createClient();

        // Act
        $client->request('GET', '/admin/dashboard');

        // Assert
        $this->assertTrue($client->getResponse()->isRedirection(), 'Devrait rediriger vers /login');
    }

    public function test_admin_dashboard_acces_admin_ok(): void
    {
        // Arrange
        $client = static::createClient();
        $admin = UtilisateurFactory::new()->asAdmin()->create();
        $client->loginUser($admin);

        // Act
        $client->request('GET', '/admin/dashboard');

        // Assert
        $this->assertTrue($client->getResponse()->isOk());
    }
}
