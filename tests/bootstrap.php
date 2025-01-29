<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

passthru('php bin/console cache:clear --env=test -q');
passthru('php bin/console doctrine:database:drop --force --env=test -q');
passthru('php bin/console doctrine:database:create --env=test -q');
passthru('php bin/console doctrine:migrations:migrate --no-interaction --env=test -q');
passthru('php bin/console doctrine:fixtures:load --no-interaction --env=test --group=tests --group=default -q');