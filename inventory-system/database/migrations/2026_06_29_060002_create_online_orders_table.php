<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sales_channel_id')
                ->constrained('sales_channels')
                ->onDelete('cascade');

            $table->string('external_ref');      // marketplace order number
            $table->string('buyer_name');
            $table->string('buyer_contact')->nullable();
            $table->string('item_label');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('amount', 12, 2)->default(0);

            $table->string('order_type')->default('stock'); // stock | custom
            $table->string('status')->default('New');       // New | Routed | Ignored

            // What this order was turned into, if routed.
            $table->string('routed_type')->nullable(); // sale | project
            $table->unsignedBigInteger('routed_id')->nullable();

            $table->text('notes')->nullable();
            $table->timestamp('ordered_at');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_orders');
    }
};
