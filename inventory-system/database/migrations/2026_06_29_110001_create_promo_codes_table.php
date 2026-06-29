<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('type')->default('percent'); // percent | fixed
            $table->decimal('value', 10, 2)->default(0);
            $table->decimal('min_subtotal', 12, 2)->nullable();
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->date('expires_at')->nullable();
            $table->boolean('active')->default(true);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->string('promo_code')->nullable()->after('discount');
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn('promo_code');
        });
        Schema::dropIfExists('promo_codes');
    }
};
