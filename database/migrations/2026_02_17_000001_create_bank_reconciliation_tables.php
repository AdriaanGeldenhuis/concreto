<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_name');
            $table->string('bank_name');
            $table->text('account_number_encrypted')->nullable();
            $table->string('account_number_last4', 4)->nullable();
            $table->string('branch_code')->nullable();
            $table->enum('account_type', ['cheque', 'savings', 'credit'])->default('cheque');
            $table->string('currency', 3)->default('ZAR');
            $table->decimal('opening_balance', 12, 2)->default(0);
            $table->decimal('current_balance', 12, 2)->default(0);
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('csv_column_map')->nullable();
            $table->timestamps();
        });

        Schema::create('bank_import_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->integer('rows_imported')->default(0);
            $table->integer('rows_skipped')->default(0);
            $table->integer('rows_auto_matched')->default(0);
            $table->date('period_from')->nullable();
            $table->date('period_to')->nullable();
            $table->string('imported_by');
            $table->timestamps();
        });

        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->cascadeOnDelete();
            $table->date('transaction_date');
            $table->string('description');
            $table->string('reference')->nullable();
            $table->decimal('amount', 12, 2);
            $table->decimal('running_balance', 12, 2)->nullable();
            $table->enum('type', ['credit', 'debit']);
            $table->string('category')->nullable();
            $table->string('hash')->unique();
            $table->foreignId('import_batch_id')->constrained('bank_import_batches')->cascadeOnDelete();
            $table->enum('reconciliation_status', ['unmatched', 'matched', 'excluded', 'manually_reconciled'])->default('unmatched');
            $table->timestamp('matched_at')->nullable();
            $table->string('matched_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['bank_account_id', 'transaction_date']);
            $table->index('reconciliation_status');
        });

        Schema::create('bank_reconciliation_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_transaction_id')->unique()->constrained('bank_transactions')->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->enum('match_type', ['auto_reference', 'auto_amount_date', 'manual', 'split']);
            $table->decimal('confidence', 5, 2)->nullable();
            $table->string('matched_by');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('bank_reconciliation_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->string('name');
            $table->enum('match_field', ['description', 'reference', 'amount']);
            $table->enum('match_type', ['contains', 'starts_with', 'exact', 'regex']);
            $table->string('match_value');
            $table->string('category');
            $table->enum('auto_action', ['categorise', 'exclude', 'match_customer']);
            $table->foreignId('target_customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->integer('times_applied')->default(0);
            $table->timestamps();
        });

        // Add columns to payments table
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('bank_transaction_id')->nullable()->after('metadata')->constrained('bank_transactions')->nullOnDelete();
            $table->timestamp('reconciled_at')->nullable()->after('bank_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bank_transaction_id');
            $table->dropColumn('reconciled_at');
        });

        Schema::dropIfExists('bank_reconciliation_rules');
        Schema::dropIfExists('bank_reconciliation_matches');
        Schema::dropIfExists('bank_transactions');
        Schema::dropIfExists('bank_import_batches');
        Schema::dropIfExists('bank_accounts');
    }
};
