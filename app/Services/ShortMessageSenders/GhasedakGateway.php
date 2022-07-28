<?php

namespace App\Services\ShortMessageSenders;

use Ghasedak\Laravel\GhasedakFacade;
use Exception;

class GhasedakGateway extends BaseGateway
{
    public function send(string $receptor, string $message, ...$arbitary)
    {
        try {

            return GhasedakFacade::SendSimple($receptor, $message, ...$arbitary);

        } catch (Exception $e) {

            abort($e->getCode(), $e->getMessage());
        }
    }
}
