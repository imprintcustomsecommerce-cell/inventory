<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_items', 'color')) {
                $table->string('color')->nullable()->after('size');
            }
            if (!Schema::hasColumn('inventory_items', 'sku')) {
                $table->string('sku')->nullable()->after('color');
                // Per-variation SKU/barcode.
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            foreach (['color', 'sku'] as $col) {
                if (Schema::hasColumn('inventory_items', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
