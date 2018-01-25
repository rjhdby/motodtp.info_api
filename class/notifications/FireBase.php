<?php

namespace notifications;

use core\Config;

class FireBase
{
    public static function sendBroadcast($payload, $isTest)
    {
        $headers = array(
            'Authorization: key=' . Config::get('firebaseKey'),
            'Content-Type: application/json'
        );

        $topic = $isTest ? '/topics/test' : '/topics/accidents';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, Config::get('firebaseUrl'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['data' => $payload, 'to' => $topic]));

        $result = curl_exec($ch);
//        var_dump($result);
//        var_dump($payload);
    }
}