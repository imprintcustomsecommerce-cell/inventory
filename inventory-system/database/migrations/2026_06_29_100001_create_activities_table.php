<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('general'); // order | proof | delivery | quality | feedback | project
            $table->string('title');
            $table->string('description')->nullable();
            $table->string('url')->nullable();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();

            $table->index('created_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('activity_seen_at')->nullable()->after('employment_status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('activity_seen_at');
        });
        Schema::dropIfExists('activities');
    }
};
