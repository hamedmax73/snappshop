<?php

namespace App\Exceptions;

use Exception;

class TransactionException extends Exception
{
    public $transaction_id;

    public function __construct($transaction_id, $message, $code, Exception $previous = NULL)
    {
        parent::__construct($message, $code, $previous);
        $this->transaction_id = $transaction_id;
    }

    public function render($request)
    {
        $status = $this->getCode();
        $error = $this->getMessage();
        return response(["message" => $error, "transaction_id" => $this->transaction_id], $status);
    }
}
