<?php

namespace Database\Seeders;

use App\Models\Station;
use Illuminate\Database\Seeder;

class StationSeeder extends Seeder
{
    public function run()
    {
        $stations = [
            ['Nairobi CBD', -1.286389, 36.817223],
            ['Westlands', -1.2657, 36.8029],
            ['Kikuyu', -1.2464, 36.6630],
            ['Thika', -1.0392, 37.0696],
            ['Ruiru', -1.1485, 36.9634],
            ['Juja', -1.1016, 37.0124],
            ['Kiambu', -1.1714, 36.8356],
            ['Limuru', -1.1136, 36.6425],
            ['Karatina', -0.4833, 37.1333],
            ['Nyeri', -0.4201, 36.9476],
        ];

        foreach ($stations as $key => $station) {
            Station::create([
                'name' => 'Station ' . ($key + 1),
                'location' => $station[0],
                'monthly_loss' => rand(-20000, 30000), // Random profit/loss between -20,000 and 30,000
                'deductions' => rand(0, 1) ? 2000 : 0, // 50% chance of having deductions
            ]);
        }
    }
}