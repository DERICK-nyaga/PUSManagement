<?php

namespace App\Console\Commands;

use App\Models\Station;
use App\Models\InternetPayment;
use App\Models\AirtimePayment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentReminderMail;

class GeneratePaymentReminders extends Command
{
    protected $signature = 'payments:send-reminders';
    protected $description = 'Send payment reminders for upcoming and overdue payments';

    public function handle()
    {
        $today = Carbon::today();
        $reminderDate = Carbon::today()->addDays(3);

        // Get payments due in 3 days
        $upcomingInternet = InternetPayment::where('expiry_date', '<=', $reminderDate)
            ->where('expiry_date', '>=', $today)
            ->whereIn('status', ['paid', 'pending'])
            ->with('station')
            ->get();

        $upcomingAirtime = AirtimePayment::where('expected_expiry', '<=', $reminderDate)
            ->where('expected_expiry', '>=', $today)
            ->where('status', 'active')
            ->with('station')
            ->get();

        // Get overdue payments
        $overdueInternet = InternetPayment::where('expiry_date', '<', $today)
            ->whereIn('status', ['pending', 'overdue'])
            ->with('station')
            ->get();

        $overdueAirtime = AirtimePayment::where('expected_expiry', '<', $today)
            ->where('status', 'active')
            ->with('station')
            ->get();

        // Group by station for consolidated reminders
        $stationReminders = [];

        foreach ($upcomingInternet as $payment) {
            $stationId = $payment->station_id;
            if (!isset($stationReminders[$stationId])) {
                $stationReminders[$stationId] = [
                    'station' => $payment->station,
                    'upcoming' => [],
                    'overdue' => []
                ];
            }
            $stationReminders[$stationId]['upcoming'][] = [
                'type' => 'internet',
                'payment' => $payment
            ];
        }

        // Send email reminders
        foreach ($stationReminders as $reminder) {
            if ($reminder['station']->contact_email) {
                Mail::to($reminder['station']->contact_email)
                    ->send(new PaymentReminderMail($reminder));
            }
        }

        $this->info("Sent reminders for " . count($stationReminders) . " stations.");

        return Command::SUCCESS;
    }
}
