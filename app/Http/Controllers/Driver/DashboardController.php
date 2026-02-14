<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $driver = $request->user();

        $activeOrders = Order::where('driver_id', $driver->id)
            ->whereNotIn('status', ['DELIVERED', 'CANCELLED', 'REFUNDED'])
            ->orderBy('scheduled_date')
            ->get();

        $completedToday = Order::where('driver_id', $driver->id)
            ->where('status', 'DELIVERED')
            ->whereDate('updated_at', today())
            ->count();

        return view('driver.dashboard', compact('activeOrders', 'completedToday'));
    }
}
