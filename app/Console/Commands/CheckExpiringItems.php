<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;

class CheckExpiringItems extends Command
{
    protected $signature = 'check:expiring-items {--type=all : Specific type to check (airtime, internet, schedules)}';
    protected $description = 'Check for expiring items and send notifications';

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    public function handle()
    {
        $type = $this->option('type');

        $this->info("Checking for expiring items ({$type})...");

        switch ($type) {
            case 'airtime':
                $result = $this->notificationService->checkExpiringAirtime();
                $this->info("Found " . $result->count() . " expiring airtime payments");
                break;

            case 'internet':
                $due = $this->notificationService->checkExpiringInternet();
                $overdue = $this->notificationService->checkOverdueInternetPayments();
                $this->info("Found " . $due->count() . " due internet payments and " . $overdue->count() . " overdue payments");
                break;

            case 'schedules':
                $result = $this->notificationService->checkUpcomingSchedules();
                $this->info("Found " . $result->count() . " upcoming payment schedules");
                break;

            case 'all':
            default:
                $results = $this->notificationService->checkAllExpiries();
                $this->info("=== Expiry Check Results ===");
                $this->info("Airtime expiring soon: " . $results['airtime']->count());
                $this->info("Internet due soon: " . $results['internet']->count());
                $this->info("Internet overdue: " . $results['overdue_payments']->count());
                $this->info("Upcoming schedules: " . $results['upcoming_schedules']->count());
                $this->info("===========================");
                break;
        }

        $this->info('Expiry check completed!');

        return Command::SUCCESS;
    }
}
