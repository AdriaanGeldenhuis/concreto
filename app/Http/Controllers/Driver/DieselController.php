<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\DieselLog;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class DieselController extends Controller
{
    public function index(Request $request)
    {
        $driver = $request->user();

        $logs = DieselLog::where('driver_id', $driver->id)
            ->with('vehicle')
            ->orderBy('fill_date', 'desc')
            ->paginate(20);

        $monthTotal = DieselLog::where('driver_id', $driver->id)
            ->whereMonth('fill_date', now()->month)
            ->whereYear('fill_date', now()->year)
            ->sum('total_cost');

        $monthLitres = DieselLog::where('driver_id', $driver->id)
            ->whereMonth('fill_date', now()->month)
            ->whereYear('fill_date', now()->year)
            ->sum('litres');

        return view('driver.diesel', compact('logs', 'monthTotal', 'monthLitres'));
    }

    public function store(Request $request)
    {
        $driver = $request->user();

        $data = $request->validate([
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'fill_date' => 'required|date|before_or_equal:today',
            'litres' => 'required|numeric|min:0.01',
            'cost_per_litre' => 'required|numeric|min:0.01',
            'odometer' => 'nullable|numeric|min:0',
            'station' => 'nullable|string|max:150',
            'full_tank' => 'boolean',
        ]);

        $data['driver_id'] = $driver->id;
        $data['full_tank'] = $request->boolean('full_tank');
        $data['total_cost'] = round($data['litres'] * $data['cost_per_litre'], 2);
        $data['created_by'] = $driver->id;

        DieselLog::create($data);

        // Update vehicle odometer
        if (!empty($data['odometer']) && !empty($data['vehicle_id'])) {
            Vehicle::where('id', $data['vehicle_id'])
                ->where('odometer', '<', $data['odometer'])
                ->update(['odometer' => $data['odometer']]);
        }

        return back()->with('success', 'Diesel logged - R ' . number_format($data['total_cost'], 2));
    }
}
