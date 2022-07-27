<?php

namespace App\Interfaces;

interface BaseReportInterface
{
    /**
     * return last 3 user that has more transaction with last 10 transactions in 10 minute recent
     * @return mixed
     */
    public function last_transaction_with_users();

}
