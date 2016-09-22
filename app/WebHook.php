<?php

namespace app;

use MongoDB\Database;
use Symfony\Component\HttpFoundation\Request;

class WebHook
{
    private $request;
    private $db;

    public function __construct(Request $request, Database $db)
    {
        $this->request = $request;
        $this->db = $db;

        $this->parse();
    }

    private function commands()
    {
        return [
            '/^\/start$/' => 'start'
        ];
    }

    private function parse()
    {
        if($body = json_decode($this->request->getContent(), true)) {
            if(isset($body['message']) && isset($body['message']['text'])) {
                $message = $body['message']['text'];
                foreach($this->commands() as $command => $method) {
                    if(preg_match($command, $message)) {
                        $this->$method($body);
                        break;
                    }
                }
            }
        }
    }

    private function start($body)
    {
        $token = sha1(mt_rand(0, 100) . time());
        $collection = $this->db->selectCollection('user');
        if($user = $collection->findOne(['user_id' => $body['message']['from']['id']])) {
            $collection->updateOne([
                'user_id' => $body['message']['from']['id'],
            ], [
               '$set' => [
                   'token' => $token,
                   'chat_id' => $body['message']['chat']['id'],
                   'updated_at' => new \MongoDB\BSON\UTCDateTime(time()),
               ],
            ]);
        } else {
            $collection->insertOne([
                'user_id' => $body['message']['from']['id'],
                'token' => $token,
                'chat_id' => $body['message']['chat']['id'],
                'updated_at' => new \MongoDB\BSON\UTCDateTime(time()),
                'first_name' => $body['message']['from']['first_name'],
                'last_name' => $body['message']['from']['first_name'],
            ]);
        }

        (new Telegram())->sendRequest('sendMessage', [
            'chat_id' => $body['message']['chat']['id'],
            'text' => "Your new token is **$token**",
            'parse_mode' => 'Markdown',
        ]);
    }
}