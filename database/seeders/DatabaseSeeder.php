<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            StationSeeder::class,
            EmployeeSeeder::class,
            PaymentSeeder::class,
            DeductionTransactionSeeder::class,
        ]);
    }
}
