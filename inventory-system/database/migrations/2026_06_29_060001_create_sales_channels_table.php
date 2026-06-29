<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_channels', function (Blueprint $table) {
            $table->id();
            $table->string('platform');          // shopee | lazada | tiktok
            $table->string('name');
            $table->string('shop_name')->nullable();
            $table->string('status')->default('disconnected'); // connected | disconnected
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });

        // Seed the three PH marketplaces as mock channels.
        $now = now();
        DB::table('sales_channels')->insert([
            ['platform' => 'shopee', 'name' => 'Shopee', 'shop_name' => 'Imprint Customs PH', 'status' => 'disconnected', 'created_at' => $now, 'updated_at' => $now],
            ['platform' => 'lazada', 'name' => 'Lazada', 'shop_name' => 'Imprint Customs', 'status' => 'disconnected', 'created_at' => $now, 'updated_at' => $now],
            ['platform' => 'tiktok', 'name' => 'TikTok Shop', 'shop_name' => 'imprintcustoms', 'status' => 'disconnected', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_channels');
    }
};
