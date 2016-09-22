<?php

namespace app;

class Telegram
{
    private $token;
    private $url = 'https://api.telegram.org/bot%s/%s';
    public function __construct()
    {
        $params = require __DIR__ . '/../config/params.php';
        $this->token = $params['botToken'];
    }

    public function sendRequest($method, $params)
    {
        $url = sprintf($this->url, $this->token, $method);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $params
        ]);
        return curl_exec($ch);
    }
}