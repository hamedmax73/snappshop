<?php

namespace App\Http\Controllers\Api\V1\Transfer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transfer\CreditTransferRequest;
use Illuminate\Http\Request;

class CreditCardTransferController extends Controller
{
    public function store(CreditTransferRequest $request)
    {
        return $request;
    }
}
