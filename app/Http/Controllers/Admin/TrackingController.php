<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DriverLocation;
use App\Models\Order;
use App\Models\User;

class TrackingController extends Controller
{
    public function drivers()
    {
        // Get all active drivers
        $drivers = User::where('role', 'driver')
            ->where('is_active', true)
            ->get()
            ->map(function ($driver) {
                $lastLocation = $driver->driverLocations()
                    ->orderBy('recorded_at', 'desc')
                    ->first();

                $activeOrder = $driver->driverOrders()
                    ->whereIn('status', ['ASSIGNED', 'ACCEPTED', 'LOADED', 'IN_TRANSIT', 'ARRIVED'])
                    ->with('customer.user', 'deliveryAddress')
                    ->first();

                $driver->last_location = $lastLocation;
                $driver->active_order = $activeOrder;

                return $driver;
            });

        // Separate drivers with active tracking from idle ones
        $activeDrivers = $drivers->filter(fn($d) => $d->active_order !== null);
        $idleDrivers = $drivers->filter(fn($d) => $d->active_order === null);

        // Build map marker data for drivers with GPS
        $mapDrivers = $drivers->filter(fn($d) => $d->last_location !== null)->map(function ($d) {
            return [
                'id'        => $d->id,
                'name'      => $d->name,
                'phone'     => $d->phone,
                'lat'       => (float) $d->last_location->lat,
                'lng'       => (float) $d->last_location->lng,
                'speed'     => $d->last_location->speed ? round($d->last_location->speed) : 0,
                'updated'   => $d->last_location->recorded_at->diffForHumans(),
                'active'    => $d->active_order !== null,
                'status'    => $d->active_order ? str_replace('_', ' ', $d->active_order->status) : 'Idle',
                'order'     => $d->active_order ? $d->active_order->order_number : null,
                'orderUrl'  => $d->active_order ? route('admin.orders.show', $d->active_order) : null,
                'detailUrl' => route('admin.tracking.driver-detail', $d),
            ];
        })->values();

        return view('admin.tracking.drivers', compact('activeDrivers', 'idleDrivers', 'mapDrivers'));
    }

    public function driverDetail(User $driver)
    {
        abort_if($driver->role !== 'driver', 404);

        $activeOrders = $driver->driverOrders()
            ->whereIn('status', ['ASSIGNED', 'ACCEPTED', 'LOADED', 'IN_TRANSIT', 'ARRIVED'])
            ->with('customer.user', 'deliveryAddress')
            ->get();

        $recentLocations = $driver->driverLocations()
            ->orderBy('recorded_at', 'desc')
            ->limit(50)
            ->get();

        $todayDeliveries = $driver->driverOrders()
            ->where('status', 'DELIVERED')
            ->whereDate('updated_at', today())
            ->count();

        return view('admin.tracking.driver-detail', compact('driver', 'activeOrders', 'recentLocations', 'todayDeliveries'));
    }

    public function orderTracking(Order $order)
    {
        $order->load('customer.user', 'driver', 'deliveryAddress', 'driverLocations');

        $locations = $order->driverLocations()
            ->orderBy('recorded_at', 'desc')
            ->limit(100)
            ->get();

        return view('admin.tracking.order', compact('order', 'locations'));
    }
}
