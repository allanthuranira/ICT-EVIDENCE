<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campus extends Model
{
    protected $fillable = ['name', 'slug', 'is_hq', 'active'];

    public function recipients(): HasMany
    {
        return $this->hasMany(Recipient::class);
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(PerformanceItem::class, 'campus_performance_item');
    }

    public function activeRepresentative(): ?Recipient
    {
        return $this->recipients()->where('active', true)->where('role', 'representative')->first();
    }

    public function activeIctHead(): ?Recipient
    {
        return $this->recipients()->where('active', true)->where('role', 'ict_head')->first();
    }
}
