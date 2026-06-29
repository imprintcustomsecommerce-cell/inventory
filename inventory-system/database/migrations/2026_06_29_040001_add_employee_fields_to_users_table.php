<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('position')->nullable()->after('department');
            $table->string('phone')->nullable()->after('position');
            $table->decimal('hourly_rate', 10, 2)->nullable()->after('phone');
            $table->date('hire_date')->nullable()->after('hourly_rate');
            $table->string('employment_status')->default('Active')->after('hire_date');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['position', 'phone', 'hourly_rate', 'hire_date', 'employment_status']);
        });
    }
};
