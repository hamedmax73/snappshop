<?php

namespace App\Services\ShortMessageSenders;

use Kavenegar\KavenegarApi;
use Exception;

class KavenegarGateway extends BaseGateway
{
    /**
     * send sms with Kavenegar
     *
     * @param string $receptor
     * @param string $message
     * @param mixed $arbitary
     *
     * @return mixed
     */
    public function send(string $receptor, string $message, ...$arbitary)
    {
        try {
            $kavenegar = new KavenegarApi(config('kavenegar.apikey'));
            return $kavenegar->send($this->getSender(), $receptor, $message, ...$arbitary);
        } catch (Exception $e) {
            abort($e->getCode(), $e->getMessage());
        }
    }
}
