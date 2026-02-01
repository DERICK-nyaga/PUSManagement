<?php

namespace App\Console\Commands;

use App\Models\InternetPayment;
use App\Models\AirtimePayment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckPaymentExpiries extends Command
{
    protected $signature = 'payments:check-expiries';
    protected $description = 'Check for expired payments and update statuses';

    public function handle()
    {
        $today = Carbon::today();

        // Update expired internet payments
        $expiredInternet = InternetPayment::where('expiry_date', '<', $today)
            ->where('status', 'paid')
            ->update(['status' => 'overdue']);

        // Update expired airtime payments
        $expiredAirtime = AirtimePayment::where('expected_expiry', '<', $today)
            ->where('status', 'active')
            ->update(['status' => 'expired']);

        // Find payments expiring in 3 days
        $warningDate = Carbon::today()->addDays(3);
        $upcomingInternet = InternetPayment::whereBetween('expiry_date', [$today, $warningDate])
            ->where('status', 'paid')
            ->count();

        $upcomingAirtime = AirtimePayment::whereBetween('expected_expiry', [$today, $warningDate])
            ->where('status', 'active')
            ->count();

        $this->info("Updated {$expiredInternet} expired internet payments.");
        $this->info("Updated {$expiredAirtime} expired airtime payments.");
        $this->info("{$upcomingInternet} internet payments expiring soon.");
        $this->info("{$upcomingAirtime} airtime payments expiring soon.");

        Log::info('Payment expiry check completed', [
            'expired_internet' => $expiredInternet,
            'expired_airtime' => $expiredAirtime,
            'upcoming_internet' => $upcomingInternet,
            'upcoming_airtime' => $upcomingAirtime
        ]);

        return Command::SUCCESS;
    }
}
