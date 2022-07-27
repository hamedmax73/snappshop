<?php

namespace App\Http\Controllers\Api\V1\Report;

use App\Http\Controllers\Controller;
use App\Interfaces\BaseReportInterface;
use Illuminate\Http\Request;

class LastUserReportController extends Controller
{
    public function __construct(private BaseReportInterface $reportService)
    {
    }

    public function show()
    {
        return $this->reportService->last_transaction_with_users();
    }
}
