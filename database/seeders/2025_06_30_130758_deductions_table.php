<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('deduction_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 12, 2); // Positive for additions, negative for deductions
            $table->string('type'); // e.g., 'initial', 'additional', 'adjustment', 'payment'
            $table->string('reason');
            $table->text('notes')->nullable();
            $table->string('order_number')->nullable();
            $table->date('transaction_date');
            // $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Who made the change
            $table->timestamps();
        });

        Schema::create('deduction_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade')->unique();
            $table->decimal('balance', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deductions');
    }
};
