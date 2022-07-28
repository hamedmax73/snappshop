<?php

namespace App\Services\ShortMessageSenders;

use Kavenegar\KavenegarApi;
use Exception;

class KavenegarGateway extends BaseGateway
{
    /**
     * send
     *
     * @param  string $receptor
     * @param  string $message
     * @param  mixed $arbitary
     *
     * @return mixed
     */
    public function send(string $receptor, string $message, ...$arbitary)
    {
        try {
        $k = new KavenegarApi('sdsdsds');

        return $k->send('09201752338', $receptor, $message, ...$arbitary);

        }catch(Exception $e) {
            abort($e->getCode(), $e->getMessage());
        }
    }
}
