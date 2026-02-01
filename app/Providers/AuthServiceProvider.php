<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerPolicies();

        // Define a gate for approving employee changes
        Gate::define('approve-employee-change', function ($user, $changeLog) {
            // Your authorization logic here
            return $user->hasRole(['hr_manager', 'department_head'])
                || $user->id === $changeLog->employee->reporting_to;
        });
    }
}
