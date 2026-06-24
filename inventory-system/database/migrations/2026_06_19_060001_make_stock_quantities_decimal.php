<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->decimal('current_stock', 12, 2)->default(0)->change();
            $table->decimal('minimum_stock', 12, 2)->default(0)->change();
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->decimal('quantity', 12, 2)->change();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->unsignedInteger('current_stock')->default(0)->change();
            $table->unsignedInteger('minimum_stock')->default(0)->change();
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->unsignedInteger('quantity')->change();
        });
    }
};
