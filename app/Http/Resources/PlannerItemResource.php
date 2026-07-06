<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlannerItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'planner_id' => $this->planner_id,
            'planner_category_id' => $this->planner_category_id,
            'title' => $this->title,
            'description' => $this->description,
            'item_type' => $this->item_type,
            'status' => $this->status,
            'priority' => $this->priority,
            'due_date' => $this->due_date?->toDateString(),
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'position' => $this->position,
            'category' => new PlannerCategoryResource($this->whenLoaded('category')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
