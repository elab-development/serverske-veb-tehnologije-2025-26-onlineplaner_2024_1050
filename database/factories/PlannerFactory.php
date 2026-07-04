<?php

namespace Database\Factories;

use App\Models\Planner;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Planner>
 */
class PlannerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-1 month', '+1 month');

        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'type' => fake()->randomElement([
                Planner::TYPE_DAILY,
                Planner::TYPE_WEEKLY,
                Planner::TYPE_MONTHLY,
                Planner::TYPE_YEARLY,
                Planner::TYPE_CUSTOM,
            ]),
            'start_date' => $startDate,
            'end_date' => fake()->optional()->dateTimeBetween($startDate, '+6 months'),
            'is_active' => fake()->boolean(85),
        ];
    }
}
