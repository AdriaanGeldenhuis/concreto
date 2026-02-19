<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::withCount('products')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.vendors.index', compact('vendors'));
    }

    public function create()
    {
        return view('admin.vendors.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'create_user_account' => 'nullable|boolean',
            'user_password' => 'nullable|required_if:create_user_account,1|string|min:8',
        ]);

        $userId = null;

        // Optionally create a user account for the vendor (for push notifications / login)
        if ($request->boolean('create_user_account')) {
            $user = User::create([
                'name' => $request->contact_person ?? $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => bcrypt($request->user_password),
                'role' => 'admin', // Vendor users get admin role for now
                'is_active' => true,
            ]);
            $userId = $user->id;
        }

        $vendor = Vendor::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'contact_person' => $request->contact_person,
            'user_id' => $userId,
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLog::log('created', 'Vendor', $vendor->id, [
            'name' => $vendor->name,
        ]);

        return redirect()->route('admin.vendors.index')
            ->with('success', "Vendor '{$vendor->name}' created successfully.");
    }

    public function edit(Vendor $vendor)
    {
        return view('admin.vendors.form', compact('vendor'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $vendor->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'contact_person' => $request->contact_person,
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLog::log('updated', 'Vendor', $vendor->id, [
            'name' => $vendor->name,
        ]);

        return redirect()->route('admin.vendors.index')
            ->with('success', "Vendor '{$vendor->name}' updated.");
    }

    public function destroy(Vendor $vendor)
    {
        $name = $vendor->name;
        $vendor->delete();

        AuditLog::log('deleted', 'Vendor', $vendor->id, [
            'name' => $name,
        ]);

        return redirect()->route('admin.vendors.index')
            ->with('success', "Vendor '{$name}' deleted.");
    }
}
