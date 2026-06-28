<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->string('type')->default('stockroom')->after('name'); // stockroom | store | event
            $table->date('event_date')->nullable()->after('location');
        });

        // Label the seeded warehouses.
        \DB::table('warehouses')->where('name', 'Inventory')->update(['type' => 'stockroom']);
        \DB::table('warehouses')->where('name', 'Store')->update(['type' => 'store']);
    }

    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn(['type', 'event_date']);
        });
    }
};
