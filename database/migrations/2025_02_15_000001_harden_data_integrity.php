<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add unique constraints on payments for yoco reference and checkout_id
        Schema::table('payments', function (Blueprint $table) {
            $table->unique('checkout_id');
        });

        // 2. Add created_at to driver_locations for audit trail
        Schema::table('driver_locations', function (Blueprint $table) {
            $table->timestamp('created_at')->nullable();
        });

        // 3. Add locked_total to orders (server-calculated snapshot)
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('locked_total', 12, 2)->nullable()->after('total');
        });

        // 4. Add idempotency_key to orders (prevent duplicate order creation)
        Schema::table('orders', function (Blueprint $table) {
            $table->string('idempotency_key')->nullable()->unique()->after('order_number');
        });

        // 5. Add payment_events table for raw webhook event storage
        Schema::create('payment_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->unique();
            $table->string('event_type');
            $table->foreignId('payment_id')->nullable()->constrained()->onDelete('set null');
            $table->json('payload');
            $table->string('status')->default('processed'); // processed, ignored, failed
            $table->timestamps();

            $table->index('event_type');
        });

        // 6. Add notification_templates table
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g., order_confirmed, payment_received
            $table->string('channel')->default('email'); // email, sms, whatsapp
            $table->string('subject')->nullable();
            $table->text('body');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 7. Add notification_log table
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('channel'); // email, sms
            $table->string('recipient');
            $table->string('template_key')->nullable();
            $table->string('subject')->nullable();
            $table->string('related_type')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->enum('status', ['sent', 'failed', 'pending'])->default('pending');
            $table->text('error_message')->nullable();
            $table->tinyInteger('attempts')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['related_type', 'related_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('notification_templates');
        Schema::dropIfExists('payment_events');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['idempotency_key']);
            $table->dropColumn('idempotency_key');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('locked_total');
        });

        Schema::table('driver_locations', function (Blueprint $table) {
            $table->dropColumn('created_at');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['checkout_id']);
        });
    }
};
