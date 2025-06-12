<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Station;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run()
    {
        $stations = Station::all();
        $paymentTypes = ['Salary', 'Utility', 'Rent', 'Maintenance', 'Supplier'];

        foreach ($stations as $station) {
            // Create 2-5 payments per station
            $paymentCount = rand(2, 5);

            for ($i = 0; $i < $paymentCount; $i++) {
                $type = $paymentTypes[array_rand($paymentTypes)];
                $daysFromNow = rand(-10, 30); // Some past and future dates

                Payment::create([
                    'amount' => $this->generatePaymentAmount($type),
                    'type' => $type,
                    'due_date' => Carbon::now()->addDays($daysFromNow)->toDateTimeString(),
                    'recipient' => $type === 'Salary' ? null : $this->generateRecipientName(),
                    'station_id' => $station->id,
                ]);
            }
        }
    }

    private function generatePaymentAmount($type)
    {
        $baseAmounts = [
            'Salary' => 150000,
            'Utility' => 20000,
            'Rent' => 50000,
            'Maintenance' => 30000,
            'Supplier' => 40000
        ];

        // Add some variation (±30%)
        $base = $baseAmounts[$type];
        return $base * (0.7 + (mt_rand(0, 60) / 100));
    }

    private function generateRecipientName()
    {
        $companies = [
            'Safaricom', 'KPLC', 'JTL', 'Water Services', 'Office Suppliers',
            'Security Firm', 'Cleaning Services', 'Internet Provider'
        ];

        return $companies[array_rand($companies)] . ' Ltd';
    }
}