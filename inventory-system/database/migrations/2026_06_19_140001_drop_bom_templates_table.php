<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('bom_templates');
    }

    public function down(): void
    {
        Schema::create('bom_templates', function (Blueprint $table) {
            $table->id();
            $table->string('product_type');
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->decimal('quantity_per_unit', 10, 2)->default(0);
            $table->timestamps();
            $table->unique(['product_type', 'inventory_item_id']);
        });
    }
};
