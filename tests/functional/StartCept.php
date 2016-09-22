<?php
$params = require __DIR__ . '/../../config/params.php';

$I = new FunctionalTester($scenario);

$I->sendPOST('/' . $params['webHook'], json_encode([
    'update_id' => '91583764',
    'message' => [
        'message_id' => 9,
        'from' => [
            'id' => 1234,
            'first_name' => 'Mike',
            'last_name' => 'B.',
        ],
        'chat' => [
            'id' => 1234,
            'first_name' => 'Mike',
            'last_name' => 'B.',
            'type' => 'private',
        ],
        'date' => '1474491203',
        'text' => '/start',
        'entities' => [
            'type' => 'bot_command',
            'offset' => 0,
            'length' => 6
        ],
    ],
]));

$I->seeResponseCodeIs(204);

