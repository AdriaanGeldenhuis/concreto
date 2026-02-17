<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->string('quote_number')->nullable()->unique()->after('id');
            $table->decimal('subtotal', 12, 2)->default(0)->after('total');
            $table->decimal('delivery_fee', 12, 2)->default(0)->after('subtotal');
            $table->decimal('vat', 12, 2)->default(0)->after('delivery_fee');
        });

        // Backfill existing quotes with quote numbers
        $quotes = \App\Models\Quote::all();
        foreach ($quotes as $quote) {
            $quote->update([
                'quote_number' => 'Q-' . str_pad($quote->id, 5, '0', STR_PAD_LEFT),
                'subtotal' => $quote->total,
                'vat' => round((float) $quote->total * 0.15, 2),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn(['quote_number', 'subtotal', 'delivery_fee', 'vat']);
        });
    }
};
