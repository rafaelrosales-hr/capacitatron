<?php
// app/Models/CourseSignature.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseSignature extends Model
{
    protected $fillable = [
        'folio',
        'user_id',
        'hash',
        'course_ids',
        'ip_address',
        'user_agent',
        'signed_at',
    ];

    protected $casts = [
        'course_ids' => 'array',
        'signed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Recalcula el hash esperado para verificar integridad.
     */
    public function isValid(): bool
    {
        $expectedHash = self::buildHash(
            $this->user_id,
            $this->course_ids,
            $this->signed_at->timestamp
        );

        return hash_equals($expectedHash, $this->hash);
    }

    public static function buildHash(int $userId, array $courseIds, int $timestamp): string
    {
        sort($courseIds);

        $payload = $userId . '|' . implode(',', $courseIds) . '|' . $timestamp;

        return hash_hmac('sha256', $payload, config('app.key'));
    }
}
