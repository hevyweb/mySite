<?php

use Symfony\Component\Dotenv\Dotenv;

$curDir = dirname(__DIR__);

require $curDir.'/vendor/autoload.php';

if (file_exists($curDir.'/config/bootstrap.php')) {
    require $curDir.'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv($curDir.'/.env');
}
