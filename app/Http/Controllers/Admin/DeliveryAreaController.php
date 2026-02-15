<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DeliveryArea;
use Illuminate\Http\Request;

class DeliveryAreaController extends Controller
{
    public function index()
    {
        $areas = DeliveryArea::orderBy('name')->get();
        return view('admin.delivery-areas.index', compact('areas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'fee' => 'required|numeric|min:0',
            'postal_codes' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $area = DeliveryArea::create($data);

        AuditLog::log('created', 'DeliveryArea', $area->id, $data);

        return back()->with('success', 'Delivery area created.');
    }

    public function update(Request $request, DeliveryArea $deliveryArea)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'fee' => 'required|numeric|min:0',
            'postal_codes' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $old = $deliveryArea->only(['name', 'fee', 'postal_codes', 'is_active']);
        $deliveryArea->update($data);

        AuditLog::log('updated', 'DeliveryArea', $deliveryArea->id, ['old' => $old, 'new' => $data]);

        return back()->with('success', 'Delivery area updated.');
    }

    public function destroy(DeliveryArea $deliveryArea)
    {
        AuditLog::log('deleted', 'DeliveryArea', $deliveryArea->id, $deliveryArea->toArray());
        $deliveryArea->delete();

        return back()->with('success', 'Delivery area deleted.');
    }
}
