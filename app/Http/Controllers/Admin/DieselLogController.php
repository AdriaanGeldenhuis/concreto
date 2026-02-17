<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DieselLog;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class DieselLogController extends Controller
{
    public function index(Request $request)
    {
        $query = DieselLog::with(['driver', 'vehicle'])
            ->orderBy('fill_date', 'desc')
            ->orderBy('created_at', 'desc');

        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        if ($request->filled('from')) {
            $query->where('fill_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('fill_date', '<=', $request->to);
        }

        $logs = $query->paginate(30)->withQueryString();

        $drivers = User::where('role', 'driver')->orderBy('name')->get();
        $vehicles = Vehicle::where('is_active', true)->orderBy('registration')->get();

        // Summary stats
        $summaryQuery = DieselLog::query();
        if ($request->filled('from')) $summaryQuery->where('fill_date', '>=', $request->from);
        if ($request->filled('to')) $summaryQuery->where('fill_date', '<=', $request->to);
        if ($request->filled('driver_id')) $summaryQuery->where('driver_id', $request->driver_id);
        if ($request->filled('vehicle_id')) $summaryQuery->where('vehicle_id', $request->vehicle_id);

        $summary = (object) [
            'total_cost' => $summaryQuery->sum('total_cost'),
            'total_litres' => $summaryQuery->sum('litres'),
            'log_count' => $summaryQuery->count(),
            'avg_cost_per_litre' => $summaryQuery->avg('cost_per_litre'),
        ];

        // Month totals for current month
        $monthTotal = DieselLog::whereMonth('fill_date', now()->month)
            ->whereYear('fill_date', now()->year)
            ->sum('total_cost');

        return view('admin.diesel.index', compact('logs', 'drivers', 'vehicles', 'summary', 'monthTotal'));
    }

    public function create()
    {
        $drivers = User::where('role', 'driver')->where('is_active', true)->orderBy('name')->get();
        $vehicles = Vehicle::where('is_active', true)->orderBy('registration')->get();

        return view('admin.diesel.form', compact('drivers', 'vehicles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'driver_id' => 'required|exists:users,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'fill_date' => 'required|date|before_or_equal:today',
            'litres' => 'required|numeric|min:0.01',
            'cost_per_litre' => 'required|numeric|min:0.01',
            'odometer' => 'nullable|numeric|min:0',
            'station' => 'nullable|string|max:150',
            'receipt_reference' => 'nullable|string|max:100',
            'full_tank' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $data['full_tank'] = $request->boolean('full_tank');
        $data['total_cost'] = round($data['litres'] * $data['cost_per_litre'], 2);
        $data['created_by'] = auth()->id();

        $log = DieselLog::create($data);

        // Update vehicle odometer if provided
        if ($data['odometer'] && $data['vehicle_id']) {
            Vehicle::where('id', $data['vehicle_id'])
                ->where('odometer', '<', $data['odometer'])
                ->update(['odometer' => $data['odometer']]);
        }

        AuditLog::log('created_diesel_log', 'DieselLog', $log->id);

        return redirect()->route('admin.diesel.index')->with('success', 'Diesel log recorded - R ' . number_format($data['total_cost'], 2));
    }

    public function edit(DieselLog $diesel)
    {
        $drivers = User::where('role', 'driver')->orderBy('name')->get();
        $vehicles = Vehicle::where('is_active', true)->orderBy('registration')->get();

        return view('admin.diesel.form', compact('diesel', 'drivers', 'vehicles'));
    }

    public function update(Request $request, DieselLog $diesel)
    {
        $data = $request->validate([
            'driver_id' => 'required|exists:users,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'fill_date' => 'required|date|before_or_equal:today',
            'litres' => 'required|numeric|min:0.01',
            'cost_per_litre' => 'required|numeric|min:0.01',
            'odometer' => 'nullable|numeric|min:0',
            'station' => 'nullable|string|max:150',
            'receipt_reference' => 'nullable|string|max:100',
            'full_tank' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $data['full_tank'] = $request->boolean('full_tank');
        $data['total_cost'] = round($data['litres'] * $data['cost_per_litre'], 2);

        $diesel->update($data);

        AuditLog::log('updated_diesel_log', 'DieselLog', $diesel->id);

        return redirect()->route('admin.diesel.index')->with('success', 'Diesel log updated.');
    }

    public function destroy(DieselLog $diesel)
    {
        AuditLog::log('deleted_diesel_log', 'DieselLog', $diesel->id);
        $diesel->delete();

        return redirect()->route('admin.diesel.index')->with('success', 'Diesel log deleted.');
    }

    public function export(Request $request)
    {
        $query = DieselLog::with(['driver', 'vehicle'])
            ->orderBy('fill_date', 'desc');

        if ($request->filled('driver_id')) $query->where('driver_id', $request->driver_id);
        if ($request->filled('vehicle_id')) $query->where('vehicle_id', $request->vehicle_id);
        if ($request->filled('from')) $query->where('fill_date', '>=', $request->from);
        if ($request->filled('to')) $query->where('fill_date', '<=', $request->to);

        $logs = $query->get();
        $filename = 'diesel-logs-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($logs) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Driver', 'Vehicle', 'Litres', 'Cost/L', 'Total', 'Odometer', 'Station', 'Reference', 'Full Tank', 'Notes']);

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->fill_date->format('Y-m-d'),
                    $log->driver?->name ?? '-',
                    $log->vehicle?->registration ?? '-',
                    $log->litres,
                    $log->cost_per_litre,
                    $log->total_cost,
                    $log->odometer ?? '-',
                    $log->station ?? '-',
                    $log->receipt_reference ?? '-',
                    $log->full_tank ? 'Yes' : 'No',
                    $log->notes ?? '',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
