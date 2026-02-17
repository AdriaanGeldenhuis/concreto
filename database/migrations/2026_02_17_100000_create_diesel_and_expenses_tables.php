<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Vehicles ──
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('registration', 20)->unique();
            $table->string('make', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->integer('year')->nullable();
            $table->string('color', 50)->nullable();
            $table->string('vin', 50)->nullable();
            $table->decimal('odometer', 12, 1)->default(0)->comment('Current km reading');
            $table->decimal('tank_capacity', 6, 1)->nullable()->comment('Litres');
            $table->enum('fuel_type', ['diesel', 'petrol'])->default('diesel');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ── Expense Categories ──
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('description')->nullable();
            $table->string('color', 7)->default('#6c757d');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // ── Diesel Logs ──
        Schema::create('diesel_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->date('fill_date');
            $table->decimal('litres', 8, 2);
            $table->decimal('cost_per_litre', 8, 2);
            $table->decimal('total_cost', 10, 2);
            $table->decimal('odometer', 12, 1)->nullable()->comment('Reading at fill');
            $table->string('station', 150)->nullable();
            $table->string('receipt_reference', 100)->nullable();
            $table->boolean('full_tank')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['fill_date']);
            $table->index(['driver_id', 'fill_date']);
        });

        // ── General Expenses ──
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_category_id')->constrained('expense_categories');
            $table->date('expense_date');
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->decimal('vat_amount', 10, 2)->default(0);
            $table->string('supplier', 150)->nullable();
            $table->string('reference', 100)->nullable();
            $table->string('receipt_path')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_frequency')->nullable()->comment('monthly, weekly, etc.');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['expense_date']);
            $table->index(['expense_category_id', 'expense_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('diesel_logs');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('vehicles');
    }
};
