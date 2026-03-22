<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DieselLog;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::withCount('dieselLogs')
            ->withSum('dieselLogs as total_diesel_cost', 'total_cost')
            ->withSum('dieselLogs as total_litres', 'litres')
            ->orderBy('is_active', 'desc')
            ->orderBy('registration')
            ->get();

        // Calculate month diesel cost with a single query for all vehicles
        $monthCosts = DieselLog::selectRaw('vehicle_id, SUM(total_cost) as cost')
            ->whereMonth('fill_date', now()->month)
            ->whereYear('fill_date', now()->year)
            ->groupBy('vehicle_id')
            ->pluck('cost', 'vehicle_id');

        $vehicles->each(function ($vehicle) use ($monthCosts) {
            $vehicle->month_diesel_cost = $monthCosts[$vehicle->id] ?? 0;
        });

        return view('admin.vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        return view('admin.vehicles.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'registration' => 'required|string|max:20|unique:vehicles',
            'make' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'year' => 'nullable|integer|min:1990|max:' . (date('Y') + 1),
            'color' => 'nullable|string|max:50',
            'vin' => 'nullable|string|max:50',
            'odometer' => 'nullable|numeric|min:0',
            'tank_capacity' => 'nullable|numeric|min:0',
            'fuel_type' => 'required|in:diesel,petrol',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $vehicle = Vehicle::create($data);

        AuditLog::log('created_vehicle', 'Vehicle', $vehicle->id);

        return redirect()->route('admin.vehicles.index')->with('success', 'Vehicle added.');
    }

    public function edit(Vehicle $vehicle)
    {
        return view('admin.vehicles.form', compact('vehicle'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate([
            'registration' => 'required|string|max:20|unique:vehicles,registration,' . $vehicle->id,
            'make' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'year' => 'nullable|integer|min:1990|max:' . (date('Y') + 1),
            'color' => 'nullable|string|max:50',
            'vin' => 'nullable|string|max:50',
            'odometer' => 'nullable|numeric|min:0',
            'tank_capacity' => 'nullable|numeric|min:0',
            'fuel_type' => 'required|in:diesel,petrol',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $vehicle->update($data);

        AuditLog::log('updated_vehicle', 'Vehicle', $vehicle->id);

        return redirect()->route('admin.vehicles.index')->with('success', 'Vehicle updated.');
    }

    public function destroy(Vehicle $vehicle)
    {
        if ($vehicle->dieselLogs()->exists()) {
            return redirect()->route('admin.vehicles.index')
                ->with('error', 'Cannot delete vehicle with diesel log records. Deactivate it instead.');
        }

        AuditLog::log('deleted_vehicle', 'Vehicle', $vehicle->id);
        $vehicle->delete();

        return redirect()->route('admin.vehicles.index')->with('success', 'Vehicle deleted.');
    }
}
