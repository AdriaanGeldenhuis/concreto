<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $addresses = $request->user()->customer->addresses;
        return view('customer.addresses.index', compact('addresses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'label' => 'nullable|string|max:50',
            'line1' => 'required|string|max:255',
            'line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'gps_lat' => 'nullable|numeric',
            'gps_lng' => 'nullable|numeric',
        ]);

        $customer = $request->user()->customer;
        $data = $request->all();
        if (!empty($data['gps_lat']) && !empty($data['gps_lng'])) {
            $data['gps_pinned'] = true;
        }
        $address = $customer->addresses()->create($data);

        if (!$customer->default_address_id) {
            $customer->update(['default_address_id' => $address->id]);
        }

        return back()->with('success', 'Address added.');
    }

    public function destroy(Request $request, Address $address)
    {
        if ($address->customer_id !== $request->user()->customer->id) {
            abort(403);
        }

        $address->delete();
        return back()->with('success', 'Address removed.');
    }
}
