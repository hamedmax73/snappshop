<?php

namespace App\Http\Controllers\Api\V1\Report;

use App\Http\Controllers\Controller;
use App\Interfaces\BaseReportInterface;
use Illuminate\Http\Request;

class LastUserReportController extends Controller
{
    public function __construct(private BaseReportInterface $report_interface)
    {
    }

    public function show()
    {
        return $this->report_interface->last_transaction_with_users();
    }
}
