<?php

namespace App\Console\Commands;

use App\Models\PaymentSchedule;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessRecurringPayments extends Command
{
    protected $signature = 'payments:process-recurring';
    protected $description = 'Process recurring payment schedules';

    public function handle()
    {
        $today = Carbon::today();

        $schedules = PaymentSchedule::where('scheduled_date', '<=', $today)
            ->where('status', 'scheduled')
            ->where('is_recurring', true)
            ->with('station')
            ->get();

        foreach ($schedules as $schedule) {
            // Process payment based on type
            switch ($schedule->payment_type) {
                case 'internet':
                    // Logic to create internet payment
                    $this->createInternetPaymentFromSchedule($schedule);
                    break;
                case 'airtime':
                    // Logic to create airtime payment
                    $this->createAirtimePaymentFromSchedule($schedule);
                    break;
            }

            // Update schedule for next occurrence
            $this->updateNextScheduleDate($schedule);

            $schedule->update(['status' => 'completed']);
        }

        $this->info("Processed {$schedules->count()} recurring payments.");

        return Command::SUCCESS;
    }

    private function createInternetPaymentFromSchedule($schedule)
    {
        // Implementation for creating internet payment
        // This would create a new InternetPayment record
    }

    private function createAirtimePaymentFromSchedule($schedule)
    {
        // Implementation for creating airtime payment
        // This would create a new AirtimePayment record
    }

    private function updateNextScheduleDate($schedule)
    {
        $nextDate = match($schedule->frequency) {
            'monthly' => Carbon::parse($schedule->scheduled_date)->addMonth(),
            'quarterly' => Carbon::parse($schedule->scheduled_date)->addMonths(3),
            'yearly' => Carbon::parse($schedule->scheduled_date)->addYear(),
            default => null,
        };

        if ($nextDate) {
            $schedule->update(['next_schedule_date' => $nextDate]);
        }
    }
}
