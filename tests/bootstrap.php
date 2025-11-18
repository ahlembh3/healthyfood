<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

/*
 * Charge les variables d'environnement pour les tests.
 * On privilégie .env.test s'il existe, sinon .env
 */
$dotenv = new Dotenv();

if (file_exists(dirname(__DIR__) . '/.env.test')) {
    $dotenv->bootEnv(dirname(__DIR__) . '/.env.test');
} elseif (file_exists(dirname(__DIR__) . '/.env')) {
    $dotenv->bootEnv(dirname(__DIR__) . '/.env');
}
