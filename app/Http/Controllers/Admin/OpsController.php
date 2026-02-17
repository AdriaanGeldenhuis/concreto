<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;

class OpsController extends Controller
{
    public function index()
    {
        // Available drivers for quick-assign
        $availableDrivers = User::where('role', 'driver')->where('is_active', true)->orderBy('name')->get();

        // Unassigned orders (PLACED but no driver)
        $unassigned = Order::where('status', 'PLACED')
            ->whereNull('driver_id')
            ->with('customer.user')
            ->orderBy('created_at')
            ->get();

        // Assigned but not loaded in 60 minutes
        $assignedNotLoaded = Order::whereIn('status', ['ASSIGNED', 'ACCEPTED'])
            ->where('updated_at', '<', now()->subMinutes(60))
            ->with(['customer.user', 'driver'])
            ->orderBy('updated_at')
            ->get();

        // En route but no tracking update in 10 minutes
        $enRouteNoTracking = Order::where('status', 'IN_TRANSIT')
            ->with(['customer.user', 'driver'])
            ->get()
            ->filter(function ($order) {
                $lastLocation = $order->driverLocations()
                    ->orderBy('recorded_at', 'desc')
                    ->first();
                return !$lastLocation || $lastLocation->recorded_at->lt(now()->subMinutes(10));
            });

        // Delivered but not invoiced
        $deliveredNotInvoiced = Order::where('status', 'DELIVERED')
            ->doesntHave('invoice')
            ->with('customer.user')
            ->orderBy('updated_at')
            ->get();

        // Pending payment for over 24 hours
        $stalePending = Order::where('status', 'PENDING_PAYMENT')
            ->where('created_at', '<', now()->subHours(24))
            ->with('customer.user')
            ->orderBy('created_at')
            ->get();

        // Summary counts
        $alertCounts = [
            'unassigned' => $unassigned->count(),
            'stuck' => $assignedNotLoaded->count(),
            'no_tracking' => $enRouteNoTracking->count(),
            'no_invoice' => $deliveredNotInvoiced->count(),
            'stale_pending' => $stalePending->count(),
        ];
        $totalAlerts = array_sum($alertCounts);

        // Today's metrics
        $todayMetrics = [
            'orders_placed' => Order::whereDate('created_at', today())->count(),
            'orders_delivered' => Order::where('status', 'DELIVERED')->whereDate('updated_at', today())->count(),
            'orders_in_transit' => Order::where('status', 'IN_TRANSIT')->count(),
            'active_drivers' => User::where('role', 'driver')->where('is_active', true)
                ->whereHas('driverShifts', fn($q) => $q->whereDate('clock_in', today())->whereNotNull('clock_in')->whereNull('clock_out'))
                ->count(),
            'revenue_today' => (float) Order::where('status', 'DELIVERED')->whereDate('updated_at', today())->sum('total'),
        ];

        return view('admin.ops.index', compact(
            'unassigned', 'assignedNotLoaded', 'enRouteNoTracking',
            'deliveredNotInvoiced', 'stalePending', 'availableDrivers',
            'alertCounts', 'totalAlerts', 'todayMetrics'
        ));
    }
}
