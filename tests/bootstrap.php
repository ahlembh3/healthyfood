<?php
// tests/bootstrap.php
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

$envFile = dirname(__DIR__).'/.env';
if (file_exists($envFile)) {
    // Charge .env et bascule en "test"
    // Signature: bootEnv(string $path, string $defaultEnv = 'dev', ?array $testEnvs = null)
    (new Dotenv())->usePutenv()->bootEnv($envFile, 'dev', ['test']);
}


// Optionnel : (re)créer le schéma si on utilise un fichier SQLite
// Laisse DAMA gérer les transactions entre tests.
$kernel = new \App\Kernel('test', false);
$kernel->boot();
$container = $kernel->getContainer();

if (str_starts_with((string) getenv('DATABASE_URL'), 'sqlite:///') && !str_contains((string) getenv('DATABASE_URL'), ':memory:')) {
    $em = $container->get('doctrine')->getManager();
    $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($em);
    $metadata = $em->getMetadataFactory()->getAllMetadata();
    if (!empty($metadata)) {
        try { $schemaTool->updateSchema($metadata, true); } catch (\Throwable $e) { /* first run */ }
    }
}

$kernel->shutdown();

