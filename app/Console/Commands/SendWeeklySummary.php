<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;
use App\Models\User;
use App\Mail\WeeklySummaryMail;
use Illuminate\Support\Facades\Mail;

class SendWeeklySummary extends Command
{
    protected $signature = 'send:weekly-summary';
    protected $description = 'Send weekly summary of all pending items';

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    public function handle()
    {
        $this->info('Preparing weekly summary...');

        $stats = $this->notificationService->getDashboardStats();

        // Get all admin users
        $adminUsers = User::where('role', 'admin')->get();

        foreach ($adminUsers as $user) {
            if (str_contains($user->notification_preferences, 'email')) {
                try {
                    Mail::to($user->email)->send(new WeeklySummaryMail($stats, $user));
                    $this->info("Weekly summary sent to {$user->email}");
                } catch (\Exception $e) {
                    $this->error("Failed to send to {$user->email}: " . $e->getMessage());
                }
            }
        }

        $this->info('Weekly summary completed!');

        return Command::SUCCESS;
    }
}
