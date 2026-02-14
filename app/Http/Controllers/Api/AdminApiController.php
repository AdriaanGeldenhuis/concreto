<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Http\Request;

class AdminApiController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function listOrders(Request $request)
    {
        $query = Order::with(['customer.user', 'driver']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);
        return response()->json($orders);
    }

    public function assignDriver(Request $request, Order $order)
    {
        $request->validate(['driver_id' => 'required|exists:users,id']);

        $driver = User::findOrFail($request->driver_id);
        if ($driver->role !== 'driver') {
            return response()->json(['error' => 'Not a driver'], 422);
        }

        $this->orderService->assignDriver($order, $driver->id);
        return response()->json(['status' => 'assigned']);
    }

    public function getSettings()
    {
        return response()->json(Setting::getAll());
    }

    public function updateSettings(Request $request)
    {
        $request->validate(['settings' => 'required|array']);

        foreach ($request->settings as $key => $value) {
            Setting::set($key, $value);
        }

        return response()->json(['status' => 'ok']);
    }
}
