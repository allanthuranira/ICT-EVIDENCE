<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerformanceItem extends Model
{
    protected $fillable = [
        'type', 'code', 'name', 'description', 'unit_of_measure', 'annual_target',
        'q1', 'q2', 'q3', 'q4', 'fy_start_year', 'active',
    ];

    protected $casts = [
        'q1' => 'boolean', 'q2' => 'boolean', 'q3' => 'boolean', 'q4' => 'boolean',
        'active' => 'boolean', 'annual_target' => 'decimal:2',
    ];

    public function campuses(): BelongsToMany
    {
        return $this->belongsToMany(Campus::class, 'campus_performance_item');
    }

    
    /**
     * Get the recipient explicitly assigned to this item for a campus.
     * Returns null when no explicit override exists on the pivot.
     */
    public function assignedRecipientForCampus(int $campusId): ?Recipient
    {
        // Reuse the loaded relationship when available (avoids N+1 in loops).
        $campus = $this->relationLoaded('campuses')
            ? $this->campuses->firstWhere('id', $campusId)
            : $this->campuses()->whereKey($campusId)->first();

        if (! $campus) {
            return null;
        }

        $recipientId = $campus->pivot->recipient_id ?? null;

        return $recipientId ? Recipient::find($recipientId) : null;
    }

    /**
     * Convenience: effective recipient for a campus — explicit assignment
     * first, then the campus's active representative.
     */
    public function effectiveRecipientForCampus(int $campusId): ?Recipient
    {
        return $this->assignedRecipientForCampus($campusId)
            ?? Campus::find($campusId)?->activeRepresentative();
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
