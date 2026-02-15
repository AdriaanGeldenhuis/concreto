<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'company_name' => 'required|string|max:255',
            'vat_number' => 'nullable|string|max:50',
            'registration_number' => 'nullable|string|max:50',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $request->password,
            'role' => 'customer',
        ]);

        $company = Company::create([
            'name' => $request->company_name,
            'vat_number' => $request->vat_number,
            'registration_number' => $request->registration_number,
            'email' => $request->email,
            'phone' => $request->phone,
            'contact_person' => $request->name,
        ]);

        Customer::create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'type' => 'COD',
        ]);

        Auth::login($user);

        return redirect()->route('customer.dashboard');
    }
}
