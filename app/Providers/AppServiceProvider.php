<?php

namespace App\Providers;

use App\Policies\ReportPolicy;
use Illuminate\Support\ServiceProvider;
use App\Models\Report;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    protected $policies = [
        Report::class => ReportPolicy::class,
    ];
}
