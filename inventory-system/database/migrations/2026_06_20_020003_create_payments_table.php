<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            // Who recorded the payment.

            $table->decimal('amount', 12, 2);

            $table->string('method')->default('Cash');
            // Cash, GCash, Bank Transfer, Check

            $table->string('reference')->nullable();
            // GCash ref #, cheque #, etc.

            $table->date('paid_at');
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
