<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['COD', 'ACCOUNT'])->default('COD');
            $table->decimal('credit_limit', 12, 2)->nullable();
            $table->string('payment_terms')->nullable(); // e.g., '30_days', 'weekly'
            $table->boolean('pay_before_dispatch')->default(false);
            $table->foreignId('default_address_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
