<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
    Schema::create('losses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('station_id')->constrained()->onDelete('cascade');
    $table->foreignId('employee_id')->nullable()->constrained()->onDelete('set null');
    $table->decimal('amount', 10, 2);
    $table->text('description');
    $table->enum('type', ['cash', 'inventory', 'equipment', 'other']);
    $table->date('date_occurred');
    $table->boolean('resolved')->default(false);
    $table->text('resolution_notes')->nullable();
    $table->timestamps();
});
    }

    public function down(): void
    {
        Schema::dropIfExists('losses');
    }
};
