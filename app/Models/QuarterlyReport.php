<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuarterlyReport extends Model
{
    protected $fillable = [
        'performance_item_id', 'campus_id', 'quarter', 'fy_start_year',
        'quarter_target', 'quarter_actual', 'quarter_variance', 'quarter_remarks',
        'cumulative_target', 'cumulative_actual', 'cumulative_variance', 'cumulative_remarks',
        'status', 'submitted_by_email', 'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'quarter_target' => 'decimal:2', 'quarter_actual' => 'decimal:2', 'quarter_variance' => 'decimal:2',
        'cumulative_target' => 'decimal:2', 'cumulative_actual' => 'decimal:2', 'cumulative_variance' => 'decimal:2',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(PerformanceItem::class, 'performance_item_id');
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public static function existsFor(int $itemId, int $campusId, int $quarter, int $fy): bool
    {
        return static::where('performance_item_id', $itemId)
            ->where('campus_id', $campusId)
            ->where('quarter', $quarter)
            ->where('fy_start_year', $fy)
            ->exists();
    }
}
