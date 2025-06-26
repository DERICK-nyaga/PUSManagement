<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();

            // Basic Information
            $table->string('name')->unique();
            $table->foreignId('category_id')->constrained('vendor_categories');

            // Contact Information
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 20)->nullable();

            // Financial Information
            $table->string('account_number', 50)->nullable();
            $table->string('tax_id', 50)->nullable();

            // Business Information
            $table->text('address')->nullable();
            $table->string('website')->nullable();

            // Contract Details
            $table->enum('payment_terms', ['net_15', 'net_30', 'net_60', 'due_on_receipt']);
            $table->string('contract_path')->nullable()->comment('Path to stored contract file');

            // Status & Notes
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('category_id');
            $table->index('is_active');
            $table->index('name');
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
