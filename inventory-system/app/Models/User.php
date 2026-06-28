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
        'role',
        'department',
        'warehouse_id',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /** A staff member in the materials department (sees only Materials). */
    public function isMaterialsStaff(): bool
    {
        return !$this->isAdmin() && $this->department === 'materials';
    }

    /** Who may view the Materials department: admins and materials staff. */
    public function canSeeMaterials(): bool
    {
        return $this->isAdmin() || $this->department === 'materials';
    }

    /** Selling is for store/event staff (and admins) — not the stockroom. */
    public function canSell(): bool
    {
        return $this->isAdmin() || ($this->warehouse && $this->warehouse->sellsStock());
    }

    public function warehouse(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Whether this user may create new items/products. Admins always can;
     * staff only if their warehouse is a stockroom (not a receive-only store).
     */
    public function canCreateItems(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->warehouse && $this->warehouse->can_create_items;
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
        ];
    }
}
