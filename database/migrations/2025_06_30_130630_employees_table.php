<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('employee_id')->unique();
            $table->string('email')->unique();
            $table->string('phone')->unique();
            $table->foreignId('station_id')->constrained()->onDelete('cascade');
            $table->string('position');
            $table->decimal('salary', 10, 2);
            $table->date('hire_date');
            $table->enum('status', ['active', 'on_leave', 'terminated'])->default('active');
            $table->decimal('deduction_balance', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
};
