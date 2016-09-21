<?php

namespace web;

use MongoDB\Client;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__.'/../vendor/autoload.php';
$mongo = require __DIR__ . '/../config/mongo.php';
$params = require __DIR__ . '/../config/params.php';

$client = new Client($mongo['uri']);
$db = $client->selectDatabase($mongo['db_name']);

$app = new Application();

$app->get('/', function () {
    return 'Hello World';
});

$app->match('/request', function(Request $request) use ($db) {

    $collection = $db->selectCollection('raw_request');
    $collection->insertOne([
        'get' => $request->query->all(),
        'post' => $request->request->all(),
        'headers' => $request->headers->all(),
        'method' => $request->getMethod(),
        'ip' => $request->getClientIp(),
    ]);


    return 'ok';
});

$app->match('/' . $params['webHook'], function(Request $request) use ($db) {

    if($body = json_decode($request->getContent(), true)) {
        $collection = $db->selectCollection('web_hook');
        $collection->insertOne([
            'get' => $request->query->all(),
            'post' => $request->request->all(),
            'headers' => $request->headers->all(),
            'method' => $request->getMethod(),
            'ip' => $request->getClientIp(),
            'body' => $body,
        ]);
    }


    return 'ok';
});

$app->run();