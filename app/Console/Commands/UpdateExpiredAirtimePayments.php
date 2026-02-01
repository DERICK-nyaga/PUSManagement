<?php

namespace App\Console\Commands;

use App\Models\AirtimePayment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateExpiredAirtimePayments extends Command
{
    protected $signature = 'airtime:update-expired';
    protected $description = 'Update airtime payment status to expired when past due date';

    public function handle()
    {
        $today = Carbon::today();

        // Find all active payments where expected_expiry is in the past
        $expiredPayments = AirtimePayment::where('status', 'active')
            ->where('expected_expiry', '<', $today->format('Y-m-d'))
            ->get();

        $count = 0;

        foreach ($expiredPayments as $payment) {
            $payment->status = 'expired';
            $payment->save();
            $count++;
        }

        if ($count > 0) {
            $this->info("Updated {$count} airtime payments to expired status.");
            Log::info("Updated {$count} airtime payments to expired status.");
        } else {
            $this->info("No airtime payments needed to be updated.");
        }

        return parent::SUCCESS;
    }
}
