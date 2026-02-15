<?php

namespace App\Console\Commands;

use App\Models\RecurringOrder;
use App\Services\OrderService;
use Illuminate\Console\Command;

class ProcessRecurringOrders extends Command
{
    protected $signature = 'orders:process-recurring';
    protected $description = 'Process recurring orders that are due today';

    public function handle(OrderService $orderService): int
    {
        $dueOrders = RecurringOrder::where('is_active', true)
            ->where('next_run_date', '<=', today())
            ->with('customer')
            ->get();

        $processed = 0;
        foreach ($dueOrders as $recurring) {
            try {
                $order = $orderService->createOrder($recurring->customer, [
                    'delivery_address_id' => $recurring->delivery_address_id,
                    'items' => $recurring->items,
                    'notes' => 'Recurring order - ' . ($recurring->notes ?? ''),
                ]);

                // Calculate next run date
                $nextDate = match ($recurring->frequency) {
                    'weekly' => $recurring->next_run_date->addWeek(),
                    'biweekly' => $recurring->next_run_date->addWeeks(2),
                    'monthly' => $recurring->next_run_date->addMonth(),
                };

                $recurring->update([
                    'last_run_date' => today(),
                    'next_run_date' => $nextDate,
                ]);

                $processed++;
            } catch (\Exception $e) {
                $this->error("Failed recurring order #{$recurring->id}: {$e->getMessage()}");
            }
        }

        $this->info("Processed {$processed} recurring orders.");
        return self::SUCCESS;
    }
}
