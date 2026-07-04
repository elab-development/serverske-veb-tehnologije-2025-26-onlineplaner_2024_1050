<?php

namespace Database\Seeders;

use App\Models\Planner;
use App\Models\PlannerCategory;
use Illuminate\Database\Seeder;

class PlannerCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoryCases = [
            'Daily Focus Planner' => [
                ['name' => 'Work', 'color' => '#2563eb'],
                ['name' => 'Personal', 'color' => '#16a34a'],
                ['name' => 'Errands', 'color' => '#f97316'],
            ],
            'Weekly Study Planner' => [
                ['name' => 'Classes', 'color' => '#7c3aed'],
                ['name' => 'Assignments', 'color' => '#dc2626'],
                ['name' => 'Reading', 'color' => '#0891b2'],
            ],
            'Monthly Wellness Planner' => [
                ['name' => 'Training', 'color' => '#ea580c'],
                ['name' => 'Nutrition', 'color' => '#65a30d'],
                ['name' => 'Appointments', 'color' => '#0f766e'],
            ],
        ];

        foreach ($categoryCases as $plannerTitle => $categories) {
            $planner = Planner::where('title', $plannerTitle)->first();

            if (! $planner) {
                continue;
            }

            foreach ($categories as $category) {
                PlannerCategory::create([
                    'planner_id' => $planner->id,
                    ...$category,
                ]);
            }
        }

        $planners = Planner::pluck('id');

        PlannerCategory::factory(6)
            ->state(fn () => [
                'planner_id' => $planners->random(),
            ])
            ->create();
    }
}
