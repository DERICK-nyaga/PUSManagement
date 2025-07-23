<?php

namespace Database\Factories;

use App\Models\Station;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'station_id' => Station::factory(),
            'employee_id' => $this->faker->unique()->numerify('EMP#####'),
            'position' => $this->faker->jobTitle,
            'salary' => $this->faker->randomFloat(2, 30000, 150000),
            'hire_date' => $this->faker->dateTimeBetween('-10 years', 'now'),
            'status' => $this->faker->randomElement(['active', 'on_leave', 'terminated']),
        ];
    }
// In database/factories/EmployeeFactory.php
    public function configure()
    {
        return $this->afterCreating(function (Employee $employee) {
            $employee->deductionBalance()->create(['balance' => 0]);
        });
    }
    /**
     * Configure the model factory.
     *
     * @return $this
     */
    // public function configure()
    // {
    //     return $this->afterCreating(function ($employee) {
    //         // Create deduction balance record for the employee
    //         $employee->updateBalance();
    //     });
    // }

    /**
     * State for active employees
     */
    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'active',
            ];
        });
    }

    /**
     * State for on leave employees
     */
    public function onLeave()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'on_leave',
            ];
        });
    }

    /**
     * State for terminated employees
     */
    public function terminated()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'terminated',
            ];
        });
    }
}
