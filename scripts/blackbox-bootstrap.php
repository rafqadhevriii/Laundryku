<?php
require_once dirname(__DIR__).'/vendor/autoload.php';
\App\Support\Blackbox\Environment::prepare(dirname(__DIR__));
if(!defined('LAUNDRYKU_BLACKBOX_LAUNCHER'))define('LAUNDRYKU_BLACKBOX_LAUNCHER',true);
$app=require dirname(__DIR__).'/bootstrap/app.php';
$app->useEnvironmentPath($app->storagePath('config'))->loadEnvironmentFrom('.env.disabled');
return $app;
