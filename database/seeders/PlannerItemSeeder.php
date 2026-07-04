<?php

namespace Database\Seeders;

use App\Models\Planner;
use App\Models\PlannerCategory;
use App\Models\PlannerItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PlannerItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $itemCases = [
            [
                'planner' => 'Daily Focus Planner',
                'category' => 'Work',
                'title' => 'Review project requirements',
                'description' => 'Check API requirements and prepare the next implementation step.',
                'item_type' => PlannerItem::TYPE_TASK,
                'status' => PlannerItem::STATUS_IN_PROGRESS,
                'priority' => PlannerItem::PRIORITY_HIGH,
                'due_date' => Carbon::today(),
                'starts_at' => Carbon::today()->setTime(9, 0),
                'ends_at' => Carbon::today()->setTime(10, 0),
                'completed_at' => null,
                'position' => 1,
            ],
            [
                'planner' => 'Daily Focus Planner',
                'category' => 'Personal',
                'title' => 'Evening walk',
                'description' => 'Short walk after work.',
                'item_type' => PlannerItem::TYPE_HABIT,
                'status' => PlannerItem::STATUS_PENDING,
                'priority' => PlannerItem::PRIORITY_MEDIUM,
                'due_date' => Carbon::today(),
                'starts_at' => Carbon::today()->setTime(18, 30),
                'ends_at' => Carbon::today()->setTime(19, 0),
                'completed_at' => null,
                'position' => 2,
            ],
            [
                'planner' => 'Weekly Study Planner',
                'category' => 'Assignments',
                'title' => 'Finish database homework',
                'description' => 'Prepare migrations, models, resources, factories, and seeders.',
                'item_type' => PlannerItem::TYPE_TASK,
                'status' => PlannerItem::STATUS_PENDING,
                'priority' => PlannerItem::PRIORITY_HIGH,
                'due_date' => Carbon::today()->addDays(3),
                'starts_at' => null,
                'ends_at' => null,
                'completed_at' => null,
                'position' => 1,
            ],
            [
                'planner' => 'Weekly Study Planner',
                'category' => 'Reading',
                'title' => 'Read Laravel API resources chapter',
                'description' => 'Focus on resource collections and conditional relationships.',
                'item_type' => PlannerItem::TYPE_NOTE,
                'status' => PlannerItem::STATUS_COMPLETED,
                'priority' => PlannerItem::PRIORITY_MEDIUM,
                'due_date' => Carbon::today()->addDay(),
                'starts_at' => null,
                'ends_at' => null,
                'completed_at' => Carbon::now(),
                'position' => 2,
            ],
            [
                'planner' => 'Monthly Wellness Planner',
                'category' => 'Training',
                'title' => 'Strength training',
                'description' => 'Full body workout session.',
                'item_type' => PlannerItem::TYPE_EVENT,
                'status' => PlannerItem::STATUS_PENDING,
                'priority' => PlannerItem::PRIORITY_MEDIUM,
                'due_date' => Carbon::today()->addDays(2),
                'starts_at' => Carbon::today()->addDays(2)->setTime(17, 0),
                'ends_at' => Carbon::today()->addDays(2)->setTime(18, 15),
                'completed_at' => null,
                'position' => 1,
            ],
            [
                'planner' => 'Monthly Wellness Planner',
                'category' => 'Nutrition',
                'title' => 'Plan weekly meals',
                'description' => 'Prepare a balanced meal plan and grocery list.',
                'item_type' => PlannerItem::TYPE_TASK,
                'status' => PlannerItem::STATUS_PENDING,
                'priority' => PlannerItem::PRIORITY_LOW,
                'due_date' => Carbon::today()->addDays(4),
                'starts_at' => null,
                'ends_at' => null,
                'completed_at' => null,
                'position' => 2,
            ],
        ];

        foreach ($itemCases as $itemCase) {
            $planner = Planner::where('title', $itemCase['planner'])->first();

            if (! $planner) {
                continue;
            }

            $category = PlannerCategory::where('planner_id', $planner->id)
                ->where('name', $itemCase['category'])
                ->first();

            PlannerItem::create([
                'planner_id' => $planner->id,
                'planner_category_id' => $category?->id,
                'title' => $itemCase['title'],
                'description' => $itemCase['description'],
                'item_type' => $itemCase['item_type'],
                'status' => $itemCase['status'],
                'priority' => $itemCase['priority'],
                'due_date' => $itemCase['due_date'],
                'starts_at' => $itemCase['starts_at'],
                'ends_at' => $itemCase['ends_at'],
                'completed_at' => $itemCase['completed_at'],
                'position' => $itemCase['position'],
            ]);
        }

        Planner::query()->take(3)->get()->each(function (Planner $planner): void {
            PlannerItem::factory(2)
                ->state([
                    'planner_id' => $planner->id,
                    'planner_category_id' => null,
                ])
                ->create();
        });
    }
}
