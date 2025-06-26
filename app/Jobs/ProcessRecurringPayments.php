<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Payment;
class ProcessRecurringPayments implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        //
    }
public function handle()
{
    $recurringPayments = Payment::where('is_recurring', true)
        ->where(function($query) {
            $query->whereNull('recurrence_ends_at')
                ->orWhere('recurrence_ends_at', '>=', now());
        })
        ->get();

    foreach ($recurringPayments as $payment) {
        $nextDate = match($payment->recurrence) {
            'weekly' => $payment->due_date->addWeek(),
            'monthly' => $payment->due_date->addMonth(),
            'yearly' => $payment->due_date->addYear(),
        };

        if ($payment->recurrence_ends_at && $nextDate->gt($payment->recurrence_ends_at)) {
            continue;
        }

        Payment::create([
            'station_id' => $payment->station_id,
            'vendor_id' => $payment->vendor_id,
            'title' => $payment->title,
            'description' => $payment->description,
            'amount' => $payment->amount,
            'due_date' => $nextDate,
            'type' => $payment->type,
            'is_recurring' => true,
            'recurrence' => $payment->recurrence,
            'recurrence_ends_at' => $payment->recurrence_ends_at,
            'created_by' => $payment->created_by
        ]);
    }
}
}
