<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Products: add cost_price for profit tracking ──
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('cost_price', 10, 2)->nullable()->after('price');
            $table->integer('stock_qty')->nullable()->after('in_stock');
            $table->integer('low_stock_threshold')->nullable()->after('stock_qty');
        });

        // ── Users: 2FA fields ──
        Schema::table('users', function (Blueprint $table) {
            $table->string('two_factor_secret', 255)->nullable()->after('remember_token');
            $table->boolean('two_factor_enabled')->default(false)->after('two_factor_secret');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_enabled');
        });

        // ── Orders: promo code support ──
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('promo_code_id')->nullable()->after('idempotency_key');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('promo_code_id');
        });

        // ── Promo Codes ──
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('description')->nullable();
            $table->enum('type', ['percentage', 'fixed'])->default('fixed');
            $table->decimal('value', 10, 2);
            $table->decimal('min_order_amount', 10, 2)->nullable();
            $table->decimal('max_discount', 10, 2)->nullable();
            $table->integer('max_uses')->nullable();
            $table->integer('times_used')->default(0);
            $table->integer('max_uses_per_customer')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('starts_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('promo_code_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_code_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->decimal('discount_applied', 10, 2);
            $table->timestamps();
        });

        // ── Wishlists ──
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['customer_id', 'product_id']);
        });

        // ── Product Reviews ──
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->tinyInteger('rating')->unsigned();
            $table->text('comment')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->timestamps();
            $table->unique(['product_id', 'customer_id', 'order_id']);
        });

        // ── Driver Shifts & Salary ──
        Schema::create('driver_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('clock_in');
            $table->dateTime('clock_out')->nullable();
            $table->decimal('hours_worked', 6, 2)->nullable();
            $table->integer('deliveries_count')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['driver_id', 'clock_in']);
        });

        Schema::create('driver_salary_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('users')->cascadeOnDelete();
            $table->enum('pay_type', ['hourly', 'per_delivery', 'fixed_monthly'])->default('hourly');
            $table->decimal('rate', 10, 2);
            $table->decimal('overtime_rate', 10, 2)->nullable();
            $table->decimal('bonus_per_delivery', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── Order Templates ──
        Schema::create('order_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->foreignId('delivery_address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->json('items'); // [{product_id, qty}]
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ── Recurring Orders ──
        Schema::create('recurring_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_template_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('frequency', ['weekly', 'biweekly', 'monthly'])->default('monthly');
            $table->date('next_run_date');
            $table->date('last_run_date')->nullable();
            $table->foreignId('delivery_address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->json('items');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── Bulk Pricing ──
        Schema::create('bulk_pricing_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('min_qty', 10, 2);
            $table->decimal('max_qty', 10, 2)->nullable();
            $table->decimal('price', 10, 2);
            $table->timestamps();
            $table->index(['product_id', 'min_qty']);
        });

        // ── Invoice Reminders tracking ──
        Schema::create('invoice_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->integer('reminder_number')->default(1);
            $table->dateTime('sent_at');
            $table->timestamps();
        });

        // ── Payments: support manual payments & refunds ──
        Schema::table('payments', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('metadata');
            $table->string('recorded_by')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['notes', 'recorded_by']);
        });

        Schema::dropIfExists('invoice_reminders');
        Schema::dropIfExists('bulk_pricing_tiers');
        Schema::dropIfExists('recurring_orders');
        Schema::dropIfExists('order_templates');
        Schema::dropIfExists('driver_salary_configs');
        Schema::dropIfExists('driver_shifts');
        Schema::dropIfExists('product_reviews');
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('promo_code_usage');
        Schema::dropIfExists('promo_codes');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['promo_code_id', 'discount_amount']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['two_factor_secret', 'two_factor_enabled', 'two_factor_recovery_codes']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['cost_price', 'stock_qty', 'low_stock_threshold']);
        });
    }
};
