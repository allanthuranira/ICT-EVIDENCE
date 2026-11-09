<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class SubmissionToken extends Model
{
    protected $fillable = [
        'performance_item_id', 'campus_id', 'quarter', 'fy_start_year', 'kind', 'token', 'expires_at',
    ];

    protected $casts = ['expires_at' => 'datetime'];

    // Stateless signed token: encodes item/campus/quarter/fy/kind so links work without DB rows.
    // A DB row is only used for revocation/audit.
    public static function for(PerformanceItem $item, Campus $campus, int $quarter, int $fy, string $kind): string
    {
        static::updateOrCreate(
            ['performance_item_id' => $item->id, 'campus_id' => $campus->id,
             'quarter' => $quarter, 'fy_start_year' => $fy, 'kind' => $kind],
            ['token' => Str::random(48), 'expires_at' => now()->addMonths(4)]
        );

        return Crypt::encryptString(json_encode([
            'i' => $item->id, 'c' => $campus->id, 'q' => $quarter, 'fy' => $fy, 'k' => $kind,
        ]));
    }

    public static function decode(string $token): ?array
    {
        try {
            $data = json_decode(Crypt::decryptString($token), true);
        } catch (\Throwable) {
            return null;
        }
        $valid = static::where('performance_item_id', $data['i'])
            ->where('campus_id', $data['c'])
            ->where('quarter', $data['q'])
            ->where('fy_start_year', $data['fy'])
            ->where('kind', $data['k'])
            ->where('expires_at', '>=', now())
            ->exists();

        return $valid ? $data : null;
    }
}
