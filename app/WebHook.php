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
            '/^\/start$/' => 'start',
            '/^\/subscribe ([a-zA-Z0-9]+)$/' => 'subscribe',
            '/^\/unsubscribe ([a-zA-Z0-9]+)$/' => 'unsubscribe',
        ];
    }

    private function parse()
    {
        if($body = json_decode($this->request->getContent(), true)) {

            $collection = $this->db->selectCollection('raw_webhook');
            $collection->insertOne($body);

            if(isset($body['message']) && isset($body['message']['text'])) {
                $message = $body['message']['text'];
                foreach($this->commands() as $command => $method) {
                    $matches = [];
                    if(preg_match($command, $message, $matches)) {
                        if(method_exists($this, $method)) {
                            $this->$method($body, $matches);
                        }
                        return;
                    }
                }
            }
        }
    }

    public function subscribe($body, $matches)
    {
        $collection = $this->db->selectCollection('user');
        if($user = $collection->findOne(['user_id' => $body['message']['from']['id']])) {
            $tags = isset($user['tags']) ? (array)$user['tags'] : [];
            $tag = trim($matches[1]);
            if(!in_array($tag, $tags)) {
                $tags[] = $tag;

                $collection->updateOne(['user_id' => $body['message']['from']['id']], [
                    '$set' => [
                        'tags' => $tags,
                    ]
                ]);
            }


            (new Telegram())->sendRequest('sendMessage', [
                'chat_id' => $body['message']['chat']['id'],
                'text' => "You are subscribed to tag " . $tag,
            ]);
        }
    }

    public function unsubscribe($body, $matches)
    {
        $collection = $this->db->selectCollection('user');
        if($user = $collection->findOne(['user_id' => $body['message']['from']['id']])) {
            $tags = isset($user['tags']) ? (array)$user['tags'] : [];
            $tag = trim($matches[1]);
            if(in_array($tag, $tags)) {
                unset($tags[array_search($tag, $tags)]);
                $tags = array_values($tags);

                $collection->updateOne(['user_id' => $body['message']['from']['id']], [
                    '$set' => [
                        'tags' => $tags,
                    ]
                ]);
            }


            (new Telegram())->sendRequest('sendMessage', [
                'chat_id' => $body['message']['chat']['id'],
                'text' => "You are subscribed from tag " . $tag,
            ]);
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