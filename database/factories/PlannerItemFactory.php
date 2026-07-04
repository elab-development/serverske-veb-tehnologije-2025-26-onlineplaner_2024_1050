<?php

namespace Database\Factories;

use App\Models\Planner;
use App\Models\PlannerCategory;
use App\Models\PlannerItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlannerItem>
 */
class PlannerItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = fake()->optional()->dateTimeBetween('now', '+1 month');
        $endsAt = $startsAt ? fake()->dateTimeBetween($startsAt, (clone $startsAt)->modify('+2 hours')) : null;

        return [
            'planner_id' => Planner::factory(),
            'planner_category_id' => null,
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'item_type' => fake()->randomElement([
                PlannerItem::TYPE_TASK,
                PlannerItem::TYPE_EVENT,
                PlannerItem::TYPE_HABIT,
                PlannerItem::TYPE_NOTE,
            ]),
            'status' => fake()->randomElement([
                PlannerItem::STATUS_PENDING,
                PlannerItem::STATUS_IN_PROGRESS,
                PlannerItem::STATUS_COMPLETED,
                PlannerItem::STATUS_CANCELLED,
            ]),
            'priority' => fake()->randomElement([
                PlannerItem::PRIORITY_LOW,
                PlannerItem::PRIORITY_MEDIUM,
                PlannerItem::PRIORITY_HIGH,
            ]),
            'due_date' => fake()->optional()->dateTimeBetween('now', '+1 month'),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'completed_at' => null,
            'position' => fake()->numberBetween(1, 100),
        ];
    }

    public function forCategory(PlannerCategory $category): static
    {
        return $this->state(fn (array $attributes) => [
            'planner_id' => $category->planner_id,
            'planner_category_id' => $category->id,
        ]);
    }
}
