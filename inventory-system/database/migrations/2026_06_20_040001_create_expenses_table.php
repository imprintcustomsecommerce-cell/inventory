<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();

            $table->string('category');
            // Rent, Utilities, Salaries, Supplies, Equipment, Marketing, etc.

            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');

            $table->string('payment_method')->nullable();
            // Cash, GCash, Bank Transfer, Check

            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            // Who logged the expense.

            $table->string('reference')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
