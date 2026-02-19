<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortField = $request->input('sort', 'name');
        $sortDir = $request->input('dir', 'asc');
        $allowedSorts = ['name', 'email', 'role', 'created_at', 'is_active'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('name');
        }

        $query->with('customer');
        $users = $query->paginate(25);

        // Summary stats
        $stats = [
            'total' => User::count(),
            'admins' => User::where('role', 'admin')->count(),
            'staff' => User::where('role', 'staff')->count(),
            'drivers' => User::where('role', 'driver')->count(),
            'customers' => User::where('role', 'customer')->count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'two_factor' => User::where('two_factor_enabled', true)->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    public function create()
    {
        return view('admin.users.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,staff,driver,customer',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['two_factor_enabled'] = $request->boolean('two_factor_enabled');
        $user = User::create($data);

        if ($user->role === 'customer') {
            Customer::create([
                'user_id' => $user->id,
                'type' => $request->input('customer_type', 'COD'),
            ]);
        }

        AuditLog::log('created', 'User', $user->id, ['role' => $user->role]);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        return view('admin.users.form', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:admin,staff,driver,customer',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['two_factor_enabled'] = $request->boolean('two_factor_enabled');
        if (empty($data['password'])) {
            unset($data['password']);
        }

        // If changing role to customer and no customer record exists, create one
        if ($data['role'] === 'customer' && !$user->customer) {
            Customer::create([
                'user_id' => $user->id,
                'type' => 'COD',
            ]);
        }

        $user->update($data);
        AuditLog::log('updated', 'User', $user->id);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function toggleActive(User $user)
    {
        // Prevent deactivating yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'activated' : 'deactivated';
        AuditLog::log($status, 'User', $user->id);

        return back()->with('success', "User {$status} successfully.");
    }
}
