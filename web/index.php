<?php

namespace web;

use app\Logger;
use app\WebHook;
use MongoDB\Client;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../app/Telegram.php';
require_once __DIR__.'/../app/WebHook.php';
require_once __DIR__.'/../app/Logger.php';

$mongo = require __DIR__ . '/../config/mongo.php';
$params = require __DIR__ . '/../config/params.php';

$client = new Client($mongo['uri']);
$db = $client->selectDatabase($mongo['db_name']);

$app = new Application();

$app->get('/', function () {
    return 'Hello World';
});

$app->match('/request/{token}/{tag}', function($token, $tag, Request $request) use ($db) {
    new Logger($request, $db, $token, $tag);
    $response = new Response();
    $response->setStatusCode(204);
    return $response;
});

$app->match('/' . $params['webHook'], function(Request $request) use ($db) {
    new WebHook($request, $db);
    $response = new Response();
    $response->setStatusCode(204);
    return $response;
});

$app->run();