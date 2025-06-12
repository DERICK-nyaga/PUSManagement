<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Station;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run()
    {
        $stations = Station::all();
        $positions = ['Manager', 'Assistant', 'Clerk', 'Security', 'Cleaner'];

        foreach ($stations as $station) {
            $employeeCount = rand(1, 5); // 1-5 employees per station

            for ($i = 0; $i < $employeeCount; $i++) {
                Employee::create([
                    'name' => $this->generateKenyanName(),
                    'salary' => $this->generateSalary($positions[$i % count($positions)]),
                    'station_id' => $station->id,
                ]);
            }
        }
    }

    private function generateKenyanName()
    {
        $firstNames = ['John', 'Mary', 'James', 'Elizabeth', 'Robert', 'Margaret', 'Michael', 'Susan', 'William', 'Dorothy'];
        $lastNames = ['Maina', 'Njeri', 'Kamau', 'Wambui', 'Ochieng', 'Achieng', 'Kipchoge', 'Chebet', 'Omondi', 'Atieno'];

        return $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
    }

    private function generateSalary($position)
    {
        $baseSalaries = [
            'Manager' => 45000,
            'Assistant' => 35000,
            'Clerk' => 25000,
            'Security' => 20000,
            'Cleaner' => 15000
        ];

        // Add some variation (±20%)
        $base = $baseSalaries[$position];
        return $base * (0.8 + (mt_rand(0, 40) / 100));
    }
}