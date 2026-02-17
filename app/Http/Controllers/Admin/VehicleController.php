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
            ->orderBy('is_active', 'desc')
            ->orderBy('registration')
            ->get()
            ->map(function ($vehicle) {
                $vehicle->total_diesel_cost = DieselLog::where('vehicle_id', $vehicle->id)->sum('total_cost');
                $vehicle->total_litres = DieselLog::where('vehicle_id', $vehicle->id)->sum('litres');
                $vehicle->month_diesel_cost = DieselLog::where('vehicle_id', $vehicle->id)
                    ->whereMonth('fill_date', now()->month)
                    ->whereYear('fill_date', now()->year)
                    ->sum('total_cost');
                return $vehicle;
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
        AuditLog::log('deleted_vehicle', 'Vehicle', $vehicle->id);
        $vehicle->delete();

        return redirect()->route('admin.vehicles.index')->with('success', 'Vehicle deleted.');
    }
}
