<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReminderLog extends Model
{
    protected $fillable = [
        'performance_item_id', 'campus_id', 'quarter', 'fy_start_year',
        'kind', 'sent_to', 'cc', 'sent_at',
    ];

    protected $casts = ['sent_at' => 'datetime'];

    public static function alreadySentToday(int $itemId, int $campusId, int $quarter, int $fy, string $kind): bool
    {
        return static::where('performance_item_id', $itemId)
            ->where('campus_id', $campusId)
            ->where('quarter', $quarter)
            ->where('fy_start_year', $fy)
            ->where('kind', $kind)
            ->whereDate('sent_at', today())
            ->exists();
    }

    public static function sentThisWeek(int $itemId, int $campusId, int $quarter, int $fy, string $kind, array $weekdays): bool
    {
        return static::where('performance_item_id', $itemId)
            ->where('campus_id', $campusId)
            ->where('quarter', $quarter)
            ->where('fy_start_year', $fy)
            ->where('kind', $kind)
            ->whereBetween('sent_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->whereIn(\DB::raw('WEEKDAY(sent_at)'), $weekdays)
            ->exists();
    }
}
