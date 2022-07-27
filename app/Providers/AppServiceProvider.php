<?php

namespace App\Providers;

use App\Interfaces\BaseReportInterface;
use App\Interfaces\BaseTransactionServiceInterface;
use App\Services\ReportService;
use App\Services\TransactionService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->bind(BaseTransactionServiceInterface::class,TransactionService::class);
        $this->app->bind(BaseReportInterface::class,ReportService::class);

        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
