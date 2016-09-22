<?php
$I = new FunctionalTester($scenario);
$token = 'c8fbec910505694bc3d4ecab38bebf65610e6d32';
$I->sendPOST("/request/$token/test", [
    'foo' => 'bar',
]);
$I->seeResponseCodeIs(204);

