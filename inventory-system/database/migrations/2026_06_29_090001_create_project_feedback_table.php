<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_feedback', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->constrained('projects')
                ->onDelete('cascade');

            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('customers')
                ->nullOnDelete();

            $table->unsignedTinyInteger('rating'); // 1–5
            $table->boolean('would_recommend')->default(true);
            $table->text('comment')->nullable();
            $table->string('reviewer_name')->nullable();
            $table->date('submitted_at');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_feedback');
    }
};
