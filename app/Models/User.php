<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Koneksi database untuk users (db_central_auth)
     */
    protected $connection = 'db_central_auth';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'jabatan',
        'department',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi dengan personal access tokens
     */
    public function tokens()
    {
        return $this->morphMany(PersonalAccessToken::class, 'tokenable');
    }

    /**
     * Create token untuk user
     */
    public function createToken($name = 'auth-token', $abilities = ['*'])
    {
        $plainToken = \Illuminate\Support\Str::random(64);
        $hashedToken = hash('sha256', $plainToken);
        
        $tokenModel = PersonalAccessToken::create([
            'tokenable_type' => static::class,
            'tokenable_id' => $this->id,
            'name' => $name,
            'token' => $hashedToken,
            'abilities' => $abilities,
            'expires_at' => now()->addDays(30),
        ]);

        return new class($plainToken, $tokenModel) {
            public $accessToken;
            public $token;

            public function __construct($token, $tokenModel)
            {
                $this->token = $token;
                $this->accessToken = $tokenModel;
            }

            public function plainTextToken()
            {
                return $this->token;
            }
        };
    }
}
