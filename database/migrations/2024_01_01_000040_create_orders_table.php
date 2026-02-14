<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->enum('status', [
                'DRAFT',
                'PENDING_PAYMENT',
                'PAID',
                'PLACED',
                'ASSIGNED',
                'ACCEPTED',
                'LOADED',
                'IN_TRANSIT',
                'ARRIVED',
                'DELIVERED_PENDING_SIGNATURE',
                'DELIVERED',
                'CANCELLED',
                'REFUNDED',
            ])->default('DRAFT');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('vat', 10, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->foreignId('delivery_address_id')->nullable()->constrained('addresses')->onDelete('set null');
            $table->date('scheduled_date')->nullable();
            $table->string('scheduled_time_window')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('driver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('order_number')->unique()->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
