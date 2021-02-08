<?php

declare(strict_types=1);

// 设置时区
date_default_timezone_set('PRC');

require __DIR__.'/../vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
use App\Kernel\App;

$env = new Dotenv();
$env->load(__DIR__.'/../.env');

function Env(string $env){
    return $_ENV[$env];
}

//$container = new DI\Container();
$builder = new \DI\ContainerBuilder();

$builder->addDefinitions(__DIR__.'/../config/dependence-base.php');

// 正式环境需要开启， 开发环境切勿开启
if (!define('ENV_DEVELOPMENT', true)) {
    $builder->enableCompilation(__DIR__.'/../runtime/cache');
}

$container = $builder->build();


$app = new App($container);

return $app;
