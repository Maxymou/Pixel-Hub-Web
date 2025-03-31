<?php

use App\Core\Application;
use App\Core\Router;

require_once __DIR__.'/../vendor/autoload.php';

$app = new Application(dirname(__DIR__));

$router = new Router();

require_once __DIR__.'/../routes/web.php';

$app->run(); 