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
        'warehouse_id',
        'activity_seen_at',
    ];

    /**
     * The five staff roles. Everyone except admin is scoped to their assigned
     * warehouse.
     *   admin     — full access to every warehouse and module
     *   store     — sell/POS + receive at their Store warehouse
     *   inventory — the stockroom: products, inventory, import/transfer/purchasing
     *   materials — the Materials (raw materials) module only
     *   events    — sell/POS + receive-only at their event warehouse
     */
    public const ROLES = ['admin', 'store', 'inventory', 'materials', 'events'];

    public const ROLE_LABELS = [
        'admin' => 'Admin',
        'store' => 'Store',
        'inventory' => 'Inventory',
        'materials' => 'Raw Materials',
        'events' => 'Events',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStore(): bool
    {
        return $this->role === 'store';
    }

    public function isInventory(): bool
    {
        return $this->role === 'inventory';
    }

    public function isEvents(): bool
    {
        return $this->role === 'events';
    }

    /** Materials-department staff (sees only the Materials module). */
    public function isMaterialsStaff(): bool
    {
        return $this->role === 'materials';
    }

    public function roleLabel(): string
    {
        return self::ROLE_LABELS[$this->role] ?? ucfirst((string) $this->role);
    }

    /** Who may view the Materials module: admins and materials staff. */
    public function canSeeMaterials(): bool
    {
        return $this->isAdmin() || $this->isMaterialsStaff();
    }

    /** Selling happens at stores and events (and for admins). */
    public function canSell(): bool
    {
        return $this->isAdmin() || $this->isStore() || $this->isEvents();
    }

    /** Only the stockroom (inventory) role and admins create products/items. */
    public function canCreateItems(): bool
    {
        return $this->isAdmin() || $this->isInventory();
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
