<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlannerCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'planner_id',
        'name',
        'color',
    ];

    /**
     * @return BelongsTo<Planner, $this>
     */
    public function planner(): BelongsTo
    {
        return $this->belongsTo(Planner::class);
    }
}
