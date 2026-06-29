<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_labor', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->constrained('projects')
                ->onDelete('cascade');

            // The staff member who did the work, if they have a login.
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Free-text name for workers without an account (e.g. subcontractors).
            $table->string('worker_name')->nullable();

            $table->string('task');
            $table->decimal('hours', 8, 2)->default(0);
            $table->decimal('hourly_rate', 10, 2)->default(0);
            $table->date('logged_at');
            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_labor');
    }
};
