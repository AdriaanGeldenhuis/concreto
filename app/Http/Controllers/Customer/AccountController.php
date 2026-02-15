<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $customer = $request->user()->customer;
        $customer->load('company');
        $addresses = $customer->addresses;

        return view('customer.account.index', compact('customer', 'addresses'));
    }

    public function updateCompany(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'trading_as' => 'nullable|string|max:255',
            'registration_number' => 'nullable|string|max:50',
            'vat_number' => 'nullable|string|max:50',
            'company_email' => 'nullable|email|max:255',
            'company_phone' => 'nullable|string|max:20',
            'contact_person' => 'nullable|string|max:255',
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
        ]);

        $customer = $request->user()->customer;

        if ($customer->company) {
            $customer->company->update([
                'name' => $request->company_name,
                'trading_as' => $request->trading_as,
                'registration_number' => $request->registration_number,
                'vat_number' => $request->vat_number,
                'email' => $request->company_email,
                'phone' => $request->company_phone,
                'contact_person' => $request->contact_person,
                'address_line1' => $request->address_line1,
                'address_line2' => $request->address_line2,
                'city' => $request->city,
                'province' => $request->province,
                'postal_code' => $request->postal_code,
            ]);
        } else {
            $company = Company::create([
                'name' => $request->company_name,
                'trading_as' => $request->trading_as,
                'registration_number' => $request->registration_number,
                'vat_number' => $request->vat_number,
                'email' => $request->company_email,
                'phone' => $request->company_phone,
                'contact_person' => $request->contact_person,
                'address_line1' => $request->address_line1,
                'address_line2' => $request->address_line2,
                'city' => $request->city,
                'province' => $request->province,
                'postal_code' => $request->postal_code,
            ]);
            $customer->update(['company_id' => $company->id]);
        }

        return back()->with('success', 'Company details updated.');
    }
}
