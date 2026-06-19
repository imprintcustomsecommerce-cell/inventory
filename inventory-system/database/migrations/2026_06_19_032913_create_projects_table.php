<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();

            $table->string('project_name');
            $table->string('customer_name')->nullable();

            $table->string('product_type')->nullable(); 
            // Example: Jersey, Polo Shirt, Jacket/Hoodie

            $table->unsignedInteger('quantity')->default(1);

            $table->string('status')->default('Pending');
            // Pending, For Design, For Sample, For Approval, For Production, Completed, Cancelled

            $table->date('due_date')->nullable();
            $table->text('remarks')->nullable();

            $table->boolean('materials_deducted')->default(false);
            // This prevents double stock deduction

            $table->timestamp('started_production_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};