<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Map legacy role/department data onto the new 5-role enum
     * (admin, store, inventory, materials, events). Data-only — no schema change.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'role')) {
            return;
        }

        // Materials department → materials role.
        if (Schema::hasColumn('users', 'department')) {
            DB::table('users')->where('department', 'materials')->update(['role' => 'materials']);
        }

        // Anyone not already on a valid new role (and not admin) is mapped by the
        // type of warehouse they belong to.
        $valid = ['admin', 'store', 'inventory', 'materials', 'events'];

        $warehouseType = [];
        if (Schema::hasTable('warehouses')) {
            $warehouseType = DB::table('warehouses')->pluck('type', 'id')->all();
        }

        foreach (DB::table('users')->get() as $u) {
            if (in_array($u->role, $valid, true)) {
                continue;
            }

            $type = $u->warehouse_id ? ($warehouseType[$u->warehouse_id] ?? null) : null;
            $role = match ($type) {
                'store' => 'store',
                'event' => 'events',
                default => 'inventory',
            };

            DB::table('users')->where('id', $u->id)->update(['role' => $role]);
        }
    }

    public function down(): void
    {
        // No-op: legacy role values are not restored.
    }
};
