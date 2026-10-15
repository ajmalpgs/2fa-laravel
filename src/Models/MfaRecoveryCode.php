<?php

namespace TwoFA\LaravelMfa\Models;

use Illuminate\Database\Eloquent\Model;

class MfaRecoveryCode extends Model
{
    protected $fillable = ['mfa_setting_id', 'code_hash', 'used_at'];

    protected $hidden = ['code_hash'];

    protected $casts = [
        'used_at' => 'datetime',
    ];

    public function setting()
    {
        return $this->belongsTo(MfaSetting::class, 'mfa_setting_id');
    }
}
