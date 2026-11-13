<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerformanceItem extends Model
{
    protected $fillable = [
          'weight','sort_order','type', 'code', 'name', 'description', 'unit_of_measure', 'annual_target',
        'section', 'strategic_issue', 'strategic_goal', 'kra_group', 'outcomes', 'strategic_objective',
        'q1', 'q2', 'q3', 'q4', 'fy_start_year', 'active',
    ];

    protected $casts = [
    
    'weight' => 'decimal:2',
        'q1' => 'boolean', 'q2' => 'boolean', 'q3' => 'boolean', 'q4' => 'boolean',
        'active' => 'boolean', 'annual_target' => 'decimal:2',
    ];

    public function campuses(): BelongsToMany
    {
        return $this->belongsToMany(Campus::class, 'campus_performance_item')
            ->withPivot('recipient_id');
    }

    /*
     * The specific person assigned to handle this item for a given campus/HQ.
     * Returns null when no explicit assignment exists (caller falls back to
     * the campus default representative).
     */
    public function assignedRecipientForCampus(int $campusId): ?Recipient
    {
        $campus = $this->campuses->firstWhere('id', $campusId);
        $recipientId = $campus?->pivot?->recipient_id;

        if (! $recipientId) {
            return null;
        }

        $recipient = Recipient::find($recipientId);

        return ($recipient && $recipient->active) ? $recipient : null;
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(EvidenceSubmission::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(QuarterlyReport::class);
    }

    public function appliesToQuarter(int $quarter): bool
    {
        return $this->{'q' . $quarter};
    }

    public function scopePc($query) { return $query->where('type', 'pc'); }
    public function scopeWorkplan($query) { return $query->where('type', 'workplan'); }
    public function scopeActive($query) { return $query->where('active', true); }
    public function scopeForFy($query, int $fyStartYear) { return $query->where('fy_start_year', $fyStartYear); }
}

