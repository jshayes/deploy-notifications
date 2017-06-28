<?php

namespace App\Http\Controllers;

use Exception;
use App\Slack\Message;
use GuzzleHttp\Client as GuzzleClient;

class Notify
{
    public function notify(GuzzleClient $client)
    {
        $message = request()->all();

        try {
            $message = (new Message($message))->toArray();
        } catch (Exception $e) {
        }

        $client->post(env('SLACK_WEBHOOK'), [
            'json' => $message
        ]);
    }
}