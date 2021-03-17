<?php

namespace App\Http\Models;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class NotifySystem
{
    public static function alert($message)
    {
        $client = new Client();

        $url = 'https://apis.autotimelapse.com/notify/system/alert-code';

        try {
            $request = $client->request('PUT', $url, [
                'headers'     => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => [
                    'message' => $message,
                    'name'    => config('app.name')
                ]
            ]);

            $content = $request->getBody()->getContents();
            $content = json_decode($content, true);

            $res = $content;
        } catch (GuzzleException $e) {
            $res = [];
        }

        return $res;
    }

    public static function alertMM($message)
    {
        $client = new Client([
            'headers' => [
                'User-Agent'   => 'Web/3.0',
                'Content-Type' => 'application/json',
            ]
        ]);

        $url = config('web.mattermost.alert_code');

        $message = addslashes($message);

        try {
            $name = config('app.name');

            $request = $client->request('POST', $url, [
                'json' => array_filter([
                    'username' => "Bot {$name}",
                    'text'     => "{$name}: {$message}"
                ])
            ]);

            $content = $request->getBody()->getContents();

            $res = $content;
        } catch (GuzzleException $e) {
            $res = [];
        }

        return $res;
    }
}
