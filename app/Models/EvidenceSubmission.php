<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvidenceSubmission extends Model
{
    protected $fillable = [
        'performance_item_id', 'campus_id', 'quarter', 'fy_start_year',
        'original_filename', 'stored_filename', 'disk_path', 'drive_file_id', 'drive_link',
        'notes', 'submitted_by_email', 'submitted_at',
    ];

    protected $casts = ['submitted_at' => 'datetime'];

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
