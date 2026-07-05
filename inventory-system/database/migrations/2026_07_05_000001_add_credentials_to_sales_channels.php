<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_channels', function (Blueprint $table) {
            // Encrypted marketplace API credentials + OAuth tokens:
            // partner_id, partner_key, shop_id, access_token, refresh_token, expires_at
            $table->text('credentials')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('sales_channels', function (Blueprint $table) {
            $table->dropColumn('credentials');
        });
    }
};
