<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PersonalAccessToken extends Model
{
    protected $connection = 'db_central_auth';
    protected $table = 'personal_access_tokens';

    protected $fillable = [
        'tokenable_type',
        'tokenable_id',
        'name',
        'token',
        'abilities',
        'last_used_at',
        'expires_at',
    ];

    protected $casts = [
        'abilities' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Generate token baru
     */
    public static function generateToken($tokenable, $name = 'auth-token', $abilities = ['*'])
    {
        $token = hash('sha256', Str::random(40));
        
        return static::create([
            'tokenable_type' => get_class($tokenable),
            'tokenable_id' => $tokenable->id,
            'name' => $name,
            'token' => hash('sha256', $token),
            'abilities' => $abilities,
            'expires_at' => now()->addDays(30), // Token berlaku 30 hari
        ])->token = $token; // Return plain token untuk user
    }

    /**
     * Find token
     */
    public static function findToken($token)
    {
        return static::where('token', hash('sha256', $token))
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();
    }
}

