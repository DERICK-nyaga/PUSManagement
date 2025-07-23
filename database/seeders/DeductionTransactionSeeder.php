<?php

namespace Database\Seeders;

use App\Models\DeductionTransaction;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class DeductionTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get all employees or create some if none exist
        $employees = Employee::all();

        if ($employees->isEmpty()) {
            $employees = Employee::factory()->count(10)->create();
        }

        // Create deduction transactions for each employee
        foreach ($employees as $employee) {
            // Create between 1-5 transactions per employee
            DeductionTransaction::factory()
                ->count(rand(1, 5))
                ->for($employee)
                ->create();
        }

        // Alternatively, you could just create a set number of transactions
        // without associating with specific employees first:
        // DeductionTransaction::factory()->count(50)->create();
    }
}
