<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceNumberTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_numbers_are_unique(): void
    {
        $numbers = [];
        for ($i = 0; $i < 50; $i++) {
            $number = Invoice::generateInvoiceNumber();
            $this->assertNotContains($number, $numbers, "Duplicate invoice number: {$number}");
            $numbers[] = $number;

            // Simulate actually creating the invoice so the sequence increments
            $order = Order::factory()->create();
            Invoice::create([
                'order_id' => $order->id,
                'invoice_no' => $number,
            ]);
        }

        $this->assertCount(50, array_unique($numbers));
    }

    public function test_invoice_number_format(): void
    {
        $number = Invoice::generateInvoiceNumber();
        $this->assertMatchesRegularExpression('/^INV-\d{6}-\d{6}$/', $number);
    }

    public function test_duplicate_invoice_number_insert_fails(): void
    {
        $order1 = Order::factory()->create();
        $order2 = Order::factory()->create();
        $invoiceNo = Invoice::generateInvoiceNumber();

        Invoice::create([
            'order_id' => $order1->id,
            'invoice_no' => $invoiceNo,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Invoice::create([
            'order_id' => $order2->id,
            'invoice_no' => $invoiceNo,
        ]);
    }
}
