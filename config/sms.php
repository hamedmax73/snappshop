<?php

return [

    'sender' => env('SMS_SENDER'),

    // 'gateway' => App\Services\ShortMessageSenders\KavenegarGateway::class
    'gateway' => App\Services\ShortMessageSenders\GhasedakGateway::class

];
