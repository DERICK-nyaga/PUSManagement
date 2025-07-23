<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeductionTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $types = ['initial', 'additional', 'adjustment', 'payment'];
        $type = $this->faker->randomElement($types);
        $amount = $this->faker->randomFloat(2, 1, 1000);

        // If it's a payment, make the amount negative
        if ($type === 'payment') {
            $amount = -abs($amount);
        } else {
            $amount = abs($amount);
        }

        return [
            'employee_id' => Employee::factory(),
            'user_id' => User::factory(),
            'amount' => $amount,
            'type' => $type,
            'reason' => $this->faker->sentence,
            'notes' => $this->faker->boolean(70) ? $this->faker->paragraph : null,
            'transaction_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => now(),
        ];
    }
}
