<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Console\Scheduling\Schedule;

class Kernel extends HttpKernel
{
    protected function schedule(Schedule $schedule): void
    {
                // Daily checks at 9:00 AM
        $schedule->command('check:expiring-items')
                 ->dailyAt('09:00')
                 ->appendOutputTo(storage_path('logs/expiry-check.log'));

        // Additional check for overdue items at 3:00 PM
        $schedule->command('check:expiring-items --type=internet')
                 ->dailyAt('15:00')
                 ->appendOutputTo(storage_path('logs/overdue-check.log'));

        // Weekly summary on Monday at 10:00 AM
        $schedule->command('send:weekly-summary')
                 ->weeklyOn(1, '10:00');

        // Clear old notifications weekly
        $schedule->command('notifications:cleanup --days=30')
                 ->weekly();

        $schedule->command('payments:check-expiries')
            ->dailyAt('09:00');

        $schedule->command('payments:send-reminders')  // Fixed typo: semd → send
            ->dailyAt('09:00');

        $schedule->command('payments:process-recurring')
            ->monthlyOn(1, '08:00');

        $schedule->command('payments:generate-report')
            ->lastDayOfMonth('15:00');

        $schedule->command('airtime:update-expired')
            ->dailyAt('00:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        // REMOVE THIS LINE - it's causing the error
        // $this->load(__DIR__.'/Commands');

        // Load console routes
        require base_path('routes/console.php');
    }

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\CheckPaymentExpiries::class,
        \App\Console\Commands\GeneratePaymentReminders::class,
        \App\Console\Commands\ProcessRecurringPayments::class,
        \App\Console\Commands\UpdateExpiredAirtimePayments::class,
        \App\Console\Commands\CheckExpiringItems::class,
        // Add missing command if it exists:
        // \App\Console\Commands\GeneratePaymentReport::class,
    ];

    /**
     * The application's global HTTP middleware stack.
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        // \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        // \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        // REMOVE these from here - they're route middleware, not global middleware
        // 'role' => \App\Http\Middleware\CheckRole::class,
        // 'auth' => \App\Http\Middleware\CheckUserIsAuthenticated::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            // \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            // \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's middleware aliases.
     * These may be used individually on routes or in controllers.
     *
     * @var array<string, class-string|string>
     */
    protected $middlewareAliases = [
        // 'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        // 'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

        // Custom middleware
        'admin' => \App\Http\Middleware\EnsureIsAdmin::class,
        'role' => \App\Http\Middleware\CheckRole::class,
        'custom.auth' => \App\Http\Middleware\CheckUserIsAuthenticated::class, // Renamed to avoid conflict
    ];
}
