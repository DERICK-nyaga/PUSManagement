<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $positions = [
            'Manager', 'Supervisor', 'Team Lead',
            'Developer', 'Designer', 'Accountant',
            'HR Specialist', 'Sales Representative', 'Support Technician'
        ];

        $statuses = ['active', 'on_leave', 'terminated', 'retired'];

        $employees = [];

        for ($i = 1; $i <= 50; $i++) {
            $firstName = fake()->firstName();
            $lastName = fake()->lastName();
            $email = strtolower($firstName . '.' . $lastName) . '@example.com';

            $employees[] = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => fake()->phoneNumber(),
                'station_id' => rand(1, 10),
                'employee_id' => 'EMP' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'position' => $positions[array_rand($positions)],
                'salary' => rand(30000, 120000),
                'hire_date' => Carbon::today()->subDays(rand(1, 365 * 5)),
                'status' => $statuses[array_rand($statuses)],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('employees')->insert($employees);
    }
}
