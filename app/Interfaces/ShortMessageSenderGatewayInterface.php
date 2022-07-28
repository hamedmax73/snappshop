<?php


namespace App\Interfaces;

interface ShortMessageSenderGatewayInterface
{
    /**
     * getSender
     *
     * @return string
     */
    public function getSender();

    /**
     * setSender
     *
     * @param  string $sender
     * @return void
     */
    public function setSender(string $sender);

    /**
     * send
     *
     * @param  string $receptor
     * @param  string $message
     * @param  mixed $arbitary
     */
    public function send(string $receptor, string $message, ...$arbitary);

}
