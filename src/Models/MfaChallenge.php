<?php

namespace TwoFA\LaravelMfa\Models;

use Illuminate\Database\Eloquent\Model;

class MfaChallenge extends Model
{
    protected $fillable = [
        'mfa_setting_id',
        'user_id',
        'user_type',
        'session_id',
        'ip_address',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function setting()
    {
        return $this->belongsTo(MfaSetting::class, 'mfa_setting_id');
    }

    public function isUsable(string $sessionId): bool
    {
        return $this->used_at === null
            && $this->session_id === $sessionId
            && $this->expires_at !== null
            && $this->expires_at->isFuture();
    }
}
