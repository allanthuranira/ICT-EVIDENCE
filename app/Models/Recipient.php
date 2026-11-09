<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recipient extends Model
{
    protected $fillable = ['campus_id', 'name', 'email', 'role', 'active'];

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }
}
