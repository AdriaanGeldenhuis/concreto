<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\OrderTemplate;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderTemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = OrderTemplate::with(['customer.user', 'customer.company', 'deliveryAddress']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('customer.user', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $templates = $query->orderBy('updated_at', 'desc')->paginate(30);

        // Resolve product names for display
        $productIds = $templates->flatMap(fn($t) => collect($t->items ?? [])->pluck('product_id'))->unique()->filter();
        $products = Product::whereIn('id', $productIds)->pluck('name', 'id');

        $totalTemplates = OrderTemplate::count();
        $customersWithTemplates = OrderTemplate::distinct('customer_id')->count('customer_id');

        return view('admin.order-templates.index', compact(
            'templates', 'products', 'totalTemplates', 'customersWithTemplates'
        ));
    }

    public function show(OrderTemplate $orderTemplate)
    {
        $orderTemplate->load(['customer.user', 'customer.company', 'deliveryAddress']);

        // Resolve product details
        $productIds = collect($orderTemplate->items ?? [])->pluck('product_id')->filter();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        // Related recurring orders
        $recurringOrders = $orderTemplate->customer->recurringOrders()
            ->where('order_template_id', $orderTemplate->id)
            ->get();

        return view('admin.order-templates.show', compact('orderTemplate', 'products', 'recurringOrders'));
    }

    public function destroy(OrderTemplate $orderTemplate)
    {
        $name = $orderTemplate->name;
        $customerId = $orderTemplate->customer_id;

        // Check for active recurring orders
        $activeRecurring = $orderTemplate->customer->recurringOrders()
            ->where('order_template_id', $orderTemplate->id)
            ->where('is_active', true)
            ->exists();

        if ($activeRecurring) {
            return back()->with('error', 'Cannot delete template with active recurring orders. Pause them first.');
        }

        $orderTemplate->delete();

        AuditLog::log('deleted_order_template', 'OrderTemplate', $orderTemplate->id, [
            'name' => $name,
            'customer_id' => $customerId,
        ]);

        return back()->with('success', "Template \"{$name}\" deleted.");
    }
}
