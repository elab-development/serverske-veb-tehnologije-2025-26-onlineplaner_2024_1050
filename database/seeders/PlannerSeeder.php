<?php

namespace Database\Seeders;

use App\Models\Planner;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PlannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('role', User::ROLE_USER)->take(3)->get();

        if ($users->count() < 3) {
            $users = $users->merge(User::factory(3 - $users->count())->create());
        }

        $plannerCases = [
            [
                'user_id' => $users[0]->id,
                'title' => 'Daily Focus Planner',
                'description' => 'A simple daily planner for work tasks, meetings, and personal routines.',
                'type' => Planner::TYPE_DAILY,
                'start_date' => Carbon::today(),
                'end_date' => null,
                'is_active' => true,
            ],
            [
                'user_id' => $users[1]->id,
                'title' => 'Weekly Study Planner',
                'description' => 'A weekly plan for classes, assignments, reading, and exam preparation.',
                'type' => Planner::TYPE_WEEKLY,
                'start_date' => Carbon::today()->startOfWeek(),
                'end_date' => Carbon::today()->endOfWeek(),
                'is_active' => true,
            ],
            [
                'user_id' => $users[2]->id,
                'title' => 'Monthly Wellness Planner',
                'description' => 'A monthly planner for workouts, meal prep, appointments, and habits.',
                'type' => Planner::TYPE_MONTHLY,
                'start_date' => Carbon::today()->startOfMonth(),
                'end_date' => Carbon::today()->endOfMonth(),
                'is_active' => true,
            ],
        ];

        foreach ($plannerCases as $plannerCase) {
            Planner::create($plannerCase);
        }

        Planner::factory(3)->create();
    }
}
