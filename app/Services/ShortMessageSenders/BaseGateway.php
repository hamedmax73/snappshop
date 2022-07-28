<?php

namespace App\Services\ShortMessageSenders;

use App\Interfaces\ShortMessageSenderGatewayInterface;

abstract class BaseGateway implements ShortMessageSenderGatewayInterface
{
    protected $sender;

    /**
     * getSender
     *
     * @return string
     */
    public function getSender() {

        if(isset($sender)) {
            return $sender;
        }

        return config('sms.sender');
    }

    /**
     * setSender
     *
     * @param  string $sender
     * @return void
     */
    public function setSender(string $sender)
    {
        $this->sender = $sender;
    }
}
