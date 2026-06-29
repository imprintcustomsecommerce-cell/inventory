<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_deliveries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->constrained('projects')
                ->onDelete('cascade');

            // Scheduled → Out for Delivery → Delivered | Failed | Returned
            $table->string('status')->default('Scheduled');

            // Pickup, Company driver, Lalamove, Grab, J&T, etc.
            $table->string('method')->nullable();
            $table->string('courier')->nullable();
            $table->string('tracking_number')->nullable();

            $table->string('recipient_name')->nullable();
            $table->string('recipient_contact')->nullable();
            $table->text('address')->nullable();

            $table->date('scheduled_date')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->string('received_by')->nullable();

            $table->decimal('fee', 10, 2)->default(0);
            $table->text('remarks')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_deliveries');
    }
};
