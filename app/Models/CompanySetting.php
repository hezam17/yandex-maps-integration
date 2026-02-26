<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanySetting extends Model
{
    protected $fillable = [
        'user_id',
        'yandex_url',
        'yandex_business_id',
        'yandex_rating',
        'yandex_total_reviews',
        'last_synced_at',
    ];

    protected $casts = [
        'yandex_rating'        => 'float',
        'yandex_total_reviews' => 'integer',
        'last_synced_at'       => 'datetime',
    ];

    // ─── Relations ───────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isStale(int $hours = 6): bool
    {
        return !$this->last_synced_at
            || $this->last_synced_at->lt(now()->subHours($hours));
    }
}