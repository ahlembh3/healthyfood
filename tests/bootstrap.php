<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

/*
 * Charge les variables d'environnement pour les tests.
 * On privilégie .env.test s'il existe, sinon .env
 */
if (file_exists(dirname(__DIR__) . '/.env.test')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env.test');
} else {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
}
