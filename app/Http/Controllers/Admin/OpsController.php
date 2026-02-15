<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;

class OpsController extends Controller
{
    public function index()
    {
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

        return view('admin.ops.index', compact(
            'unassigned',
            'assignedNotLoaded',
            'enRouteNoTracking',
            'deliveredNotInvoiced',
            'stalePending',
        ));
    }
}
