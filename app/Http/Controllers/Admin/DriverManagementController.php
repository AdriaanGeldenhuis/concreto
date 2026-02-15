<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DriverSalaryConfig;
use App\Models\DriverShift;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class DriverManagementController extends Controller
{
    public function index(Request $request)
    {
        $drivers = User::where('role', 'driver')
            ->with('salaryConfig')
            ->get()
            ->map(function ($driver) {
                $driver->total_deliveries = $driver->driverOrders()->where('status', 'DELIVERED')->count();
                $driver->today_deliveries = $driver->driverOrders()->where('status', 'DELIVERED')->whereDate('updated_at', today())->count();

                $currentShift = DriverShift::where('driver_id', $driver->id)
                    ->whereNull('clock_out')
                    ->latest('clock_in')
                    ->first();
                $driver->current_shift = $currentShift;

                $monthHours = DriverShift::where('driver_id', $driver->id)
                    ->whereMonth('clock_in', now()->month)
                    ->whereYear('clock_in', now()->year)
                    ->sum('hours_worked');
                $driver->month_hours = round($monthHours, 1);

                $monthDeliveries = DriverShift::where('driver_id', $driver->id)
                    ->whereMonth('clock_in', now()->month)
                    ->whereYear('clock_in', now()->year)
                    ->sum('deliveries_count');
                $driver->month_deliveries = $monthDeliveries;

                // Calculate monthly pay
                $config = $driver->salaryConfig;
                if ($config) {
                    $pay = match ($config->pay_type) {
                        'hourly' => $monthHours * (float) $config->rate,
                        'per_delivery' => $monthDeliveries * (float) $config->rate,
                        'fixed_monthly' => (float) $config->rate,
                        default => 0,
                    };
                    if ($config->bonus_per_delivery) {
                        $pay += $monthDeliveries * (float) $config->bonus_per_delivery;
                    }
                    $driver->month_pay = round($pay, 2);
                } else {
                    $driver->month_pay = 0;
                }

                return $driver;
            });

        return view('admin.drivers.index', compact('drivers'));
    }

    public function shifts(User $driver)
    {
        abort_if($driver->role !== 'driver', 404);

        $shifts = DriverShift::where('driver_id', $driver->id)
            ->orderBy('clock_in', 'desc')
            ->paginate(30);

        $config = DriverSalaryConfig::where('driver_id', $driver->id)->where('is_active', true)->first();

        return view('admin.drivers.shifts', compact('driver', 'shifts', 'config'));
    }

    public function updateSalary(Request $request, User $driver)
    {
        abort_if($driver->role !== 'driver', 404);

        $data = $request->validate([
            'pay_type' => 'required|in:hourly,per_delivery,fixed_monthly',
            'rate' => 'required|numeric|min:0',
            'overtime_rate' => 'nullable|numeric|min:0',
            'bonus_per_delivery' => 'nullable|numeric|min:0',
        ]);

        // Deactivate old config
        DriverSalaryConfig::where('driver_id', $driver->id)->update(['is_active' => false]);

        $config = DriverSalaryConfig::create([
            'driver_id' => $driver->id,
            'pay_type' => $data['pay_type'],
            'rate' => $data['rate'],
            'overtime_rate' => $data['overtime_rate'],
            'bonus_per_delivery' => $data['bonus_per_delivery'],
            'is_active' => true,
        ]);

        AuditLog::log('updated_salary', 'User', $driver->id, $data);

        return back()->with('success', 'Salary configuration updated.');
    }

    public function editShift(Request $request, DriverShift $shift)
    {
        $data = $request->validate([
            'clock_in' => 'required|date',
            'clock_out' => 'nullable|date|after:clock_in',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($data['clock_out']) {
            $data['hours_worked'] = $shift->calculateHours();
            $shift->clock_out = $data['clock_out'];
            $data['hours_worked'] = round(\Carbon\Carbon::parse($data['clock_in'])->diffInMinutes(\Carbon\Carbon::parse($data['clock_out'])) / 60, 2);
        }

        $shift->update($data);
        AuditLog::log('edited_shift', 'DriverShift', $shift->id);

        return back()->with('success', 'Shift updated.');
    }
}
