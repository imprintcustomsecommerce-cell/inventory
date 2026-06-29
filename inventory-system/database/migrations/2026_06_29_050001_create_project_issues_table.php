<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_issues', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->constrained('projects')
                ->onDelete('cascade');

            // Defect | Reprint | Return | Complaint
            $table->string('type')->default('Defect');

            // Open | In Progress | Resolved | Rejected
            $table->string('status')->default('Open');

            // Short reason category, e.g. Misprint, Wrong size, Color mismatch.
            $table->string('reason')->nullable();
            $table->text('description')->nullable();

            $table->unsignedInteger('quantity_affected')->default(0);
            $table->decimal('rework_cost', 10, 2)->default(0);

            $table->foreignId('reported_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->date('reported_at');

            $table->foreignId('resolved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_issues');
    }
};
