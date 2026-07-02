<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'warehouse_id',
        'activity_seen_at',
    ];

    // Staff roles, departments, and HR were removed. The app now runs on a
    // single login with full access; these helpers stay permissive so the
    // permission checks scattered across the app keep working.

    public function isAdmin(): bool
    {
        return true;
    }

    public function isMaterialsStaff(): bool
    {
        return false;
    }

    public function canSeeMaterials(): bool
    {
        return true;
    }

    public function canSell(): bool
    {
        return true;
    }

    public function canCreateItems(): bool
    {
        return true;
    }

    public function warehouse(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

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
            'activity_seen_at' => 'datetime',
        ];
    }
}
