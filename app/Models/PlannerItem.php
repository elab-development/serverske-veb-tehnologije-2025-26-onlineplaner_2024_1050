<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlannerItem extends Model
{
    use HasFactory;

    public const TYPE_TASK = 'task';

    public const TYPE_EVENT = 'event';

    public const TYPE_HABIT = 'habit';

    public const TYPE_NOTE = 'note';

    public const STATUS_PENDING = 'pending';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const PRIORITY_LOW = 'low';

    public const PRIORITY_MEDIUM = 'medium';

    public const PRIORITY_HIGH = 'high';

    protected $fillable = [
        'planner_id',
        'planner_category_id',
        'title',
        'description',
        'item_type',
        'status',
        'priority',
        'due_date',
        'starts_at',
        'ends_at',
        'completed_at',
        'position',
    ];

    /**
     * @return BelongsTo<Planner, $this>
     */
    public function planner(): BelongsTo
    {
        return $this->belongsTo(Planner::class);
    }

    /**
     * @return BelongsTo<PlannerCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PlannerCategory::class, 'planner_category_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}
