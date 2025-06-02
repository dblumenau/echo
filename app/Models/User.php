<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    const TYPE_UNAPPROVED = 'unapproved';
    const TYPE_APPROVED = 'approved';
    const TYPE_SUPERADMIN = 'superadmin';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
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
     * Get the word pairs created by the user.
     */
    public function wordPairs(): HasMany
    {
        return $this->hasMany(WordPair::class);
    }

    /**
     * Check if user can manage word pairs.
     */
    public function canManageWordPairs(): bool
    {
        return in_array($this->user_type, [self::TYPE_APPROVED, self::TYPE_SUPERADMIN]);
    }

    /**
     * Check if user is a superadmin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->user_type === self::TYPE_SUPERADMIN;
    }

    /**
     * Check if user is approved.
     */
    public function isApproved(): bool
    {
        return in_array($this->user_type, [self::TYPE_APPROVED, self::TYPE_SUPERADMIN]);
    }
}
