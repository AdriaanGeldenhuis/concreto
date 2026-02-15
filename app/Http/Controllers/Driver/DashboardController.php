<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\DriverShift;
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

        // Current shift
        $currentShift = DriverShift::where('driver_id', $driver->id)
            ->whereNull('clock_out')
            ->latest('clock_in')
            ->first();

        // Last offload time
        $lastDelivery = Order::where('driver_id', $driver->id)
            ->where('status', 'DELIVERED')
            ->with('proofOfDelivery')
            ->orderBy('updated_at', 'desc')
            ->first();

        $lastOffloadTime = $lastDelivery?->proofOfDelivery?->signed_at ?? $lastDelivery?->updated_at;

        // This month stats
        $monthDeliveries = Order::where('driver_id', $driver->id)
            ->where('status', 'DELIVERED')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();

        $monthHours = DriverShift::where('driver_id', $driver->id)
            ->whereMonth('clock_in', now()->month)
            ->whereYear('clock_in', now()->year)
            ->sum('hours_worked');

        return view('driver.dashboard', compact(
            'activeOrders', 'completedToday', 'currentShift', 'lastOffloadTime',
            'monthDeliveries', 'monthHours'
        ));
    }

    public function clockIn(Request $request)
    {
        $driver = $request->user();

        // Check if already clocked in
        $existing = DriverShift::where('driver_id', $driver->id)
            ->whereNull('clock_out')
            ->first();

        if ($existing) {
            return back()->with('error', 'You are already clocked in.');
        }

        DriverShift::create([
            'driver_id' => $driver->id,
            'clock_in' => now(),
        ]);

        return back()->with('success', 'Clocked in at ' . now()->format('H:i'));
    }

    public function clockOut(Request $request)
    {
        $driver = $request->user();

        $shift = DriverShift::where('driver_id', $driver->id)
            ->whereNull('clock_out')
            ->latest('clock_in')
            ->first();

        if (!$shift) {
            return back()->with('error', 'You are not clocked in.');
        }

        $shift->update([
            'clock_out' => now(),
            'hours_worked' => round($shift->clock_in->diffInMinutes(now()) / 60, 2),
            'deliveries_count' => Order::where('driver_id', $driver->id)
                ->where('status', 'DELIVERED')
                ->whereBetween('updated_at', [$shift->clock_in, now()])
                ->count(),
        ]);

        return back()->with('success', 'Clocked out. Worked ' . $shift->hours_worked . ' hours.');
    }
}
