<?php

namespace Database\Factories;

use App\Models\Planner;
use App\Models\PlannerCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlannerCategory>
 */
class PlannerCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'planner_id' => Planner::factory(),
            'name' => fake()->randomElement([
                'Work',
                'Personal',
                'Health',
                'Learning',
                'Finance',
                'Family',
            ]),
            'color' => fake()->hexColor(),
        ];
    }
}
