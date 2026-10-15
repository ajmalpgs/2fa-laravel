<?php

namespace TwoFA\LaravelMfa\Models;

use Illuminate\Database\Eloquent\Model;

class MfaSetting extends Model
{
    protected $fillable = [
        'user_id',
        'user_type',
        'provider',
        'secret',
        'enabled',
        'confirmed_at',
        'last_used_timestep',
    ];

    protected $hidden = ['secret'];

    protected $casts = [
        'enabled' => 'bool',
        'confirmed_at' => 'datetime',
        'last_used_timestep' => 'int',
        'secret' => 'encrypted',
    ];

    public function user()
    {
        return $this->morphTo();
    }

    public function recoveryCodes()
    {
        return $this->hasMany(MfaRecoveryCode::class);
    }
}
