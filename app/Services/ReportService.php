<?php

namespace App\Services;

use App\Http\Resources\User\UserResource;
use App\Interfaces\BaseReportInterface;
use App\Repositories\Transaction\TransactionRepository;

class ReportService implements BaseReportInterface
{
    public function __construct(private TransactionRepository $transactionRepository)
    {
    }

    public function last_transaction_with_users()
    {
        return UserResource::collection($this->transactionRepository->get_recent_users_with_transactions('200', 3));
    }

}
