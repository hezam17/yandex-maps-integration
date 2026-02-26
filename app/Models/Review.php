<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Review extends Model
{
    protected $fillable = [
        'company_setting_id',
        'external_id',
        'author',
        'rating',
        'text',
        'reviewed_at',
        'likes',
        'photos',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'rating'      => 'integer',
        'likes'       => 'integer',
        'photos'      => 'array',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function companySetting(): BelongsTo
    {
        return $this->belongsTo(CompanySetting::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeForSetting($query, int $settingId)
    {
        return $query->where('company_setting_id', $settingId);
    }

    public function scopeSortedBy($query, string $sort)
    {
        return match ($sort) {
            'rating_desc' => $query->orderByDesc('rating'),
            'rating_asc'  => $query->orderBy('rating'),
            default       => $query->orderByDesc('reviewed_at'),
        };
    }
}