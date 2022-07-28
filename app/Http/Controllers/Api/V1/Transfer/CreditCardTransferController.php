<?php

namespace App\Http\Controllers\Api\V1\Transfer;

use App\Exceptions\TransactionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Transfer\CreditTransferRequest;
use App\Interfaces\BaseRepositoryInterface;
use App\Interfaces\BaseTransactionServiceInterface;
use App\Repositories\User\UserRepository;
use Illuminate\Http\Request;

class CreditCardTransferController extends Controller
{

    public function __construct(private BaseTransactionServiceInterface $transactionService)
    {

    }

    public function store(CreditTransferRequest $request)
    {
        try {
            $result = $this->transactionService->send_money($request->sender, $request->receiver, $request->amount);
            $message = [
                'success' => true,
                'message' => 'تراکنش با موفقیت انجام شد.',
                'reference_id' => $result
            ];
            return response($message, 201);
        } catch (TransactionException $e) {
            $message = [
                'success' => false,
                'message' => $e->getMessage(),
                'reference_id' => $e->transaction_id
            ];
            return response($message, $e->getCode());
        } catch (\Exception $e) {
            $message = [
                'success' => false,
                'message' => $e->getMessage(),
                'reference_id' => null,
            ];
            $error_code = $e->getCode();
            if (empty($error_code) || $error_code == 0) $error_code = 500;
            return response($message,$error_code);
        }
    }

}
