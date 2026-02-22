<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\DieselLog;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\Request;

class ProfitabilityCalculatorController extends Controller
{
    public function index()
    {
        // Only admin (super admin) can access
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        // Get latest diesel price from logs
        $latestDiesel = DieselLog::orderBy('fill_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();
        $defaultDieselPrice = $latestDiesel ? (float) $latestDiesel->cost_per_litre : 23.50;

        // Get products with cost prices for reference
        $products = Product::where('is_active', true)
            ->whereNotNull('cost_price')
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'cost_price', 'unit']);

        // Get vendors with full address for origin selection
        $vendors = Vendor::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get all customer addresses for destination selection
        $addresses = Address::with('customer.user')
            ->orderBy('city')
            ->get();

        return view('admin.profitability-calculator', compact('defaultDieselPrice', 'products', 'vendors', 'addresses'));
    }
}
