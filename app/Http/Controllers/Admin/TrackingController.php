<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Company;
use App\Models\DriverLocation;
use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\Http;

class TrackingController extends Controller
{
    public function drivers()
    {
        // Get all active drivers
        $drivers = User::where('role', 'driver')
            ->where('is_active', true)
            ->get()
            ->map(function ($driver) {
                $lastLocation = $driver->driverLocations()
                    ->orderBy('recorded_at', 'desc')
                    ->first();

                $activeOrder = $driver->driverOrders()
                    ->whereIn('status', ['ASSIGNED', 'ACCEPTED', 'LOADED', 'IN_TRANSIT', 'ARRIVED'])
                    ->with('customer.user', 'deliveryAddress')
                    ->first();

                $driver->last_location = $lastLocation;
                $driver->active_order = $activeOrder;

                return $driver;
            });

        $activeDrivers = $drivers->filter(fn($d) => $d->active_order !== null);
        $idleDrivers = $drivers->filter(fn($d) => $d->active_order === null);

        $mapDrivers = $drivers->filter(fn($d) => $d->last_location !== null)->map(function ($d) {
            return [
                'id'        => $d->id,
                'name'      => $d->name,
                'phone'     => $d->phone,
                'lat'       => (float) $d->last_location->lat,
                'lng'       => (float) $d->last_location->lng,
                'speed'     => $d->last_location->speed ? round($d->last_location->speed) : 0,
                'updated'   => $d->last_location->recorded_at->diffForHumans(),
                'active'    => $d->active_order !== null,
                'status'    => $d->active_order ? str_replace('_', ' ', $d->active_order->status) : 'Idle',
                'order'     => $d->active_order ? $d->active_order->order_number : null,
                'orderUrl'  => $d->active_order ? route('admin.orders.show', $d->active_order) : null,
                'detailUrl' => route('admin.tracking.driver-detail', $d),
            ];
        })->values();

        // Vendor locations with GPS
        $vendors = Vendor::where('is_active', true)
            ->whereNotNull('gps_lat')->whereNotNull('gps_lng')
            ->where('gps_lat', '!=', 0)->where('gps_lng', '!=', 0)
            ->get();

        $mapVendors = $vendors->map(fn($v) => [
            'name'    => $v->name,
            'address' => $v->full_address,
            'lat'     => (float) $v->gps_lat,
            'lng'     => (float) $v->gps_lng,
        ])->values();

        // Customer delivery addresses with GPS + distance from nearest vendor
        $addresses = Address::with('customer.user')
            ->whereNotNull('gps_lat')->whereNotNull('gps_lng')
            ->where('gps_lat', '!=', 0)->where('gps_lng', '!=', 0)
            ->get();

        $mapCustomers = $addresses->map(function ($a) use ($vendors) {
            $name = $a->customer?->user?->name ?? 'Customer #' . $a->customer_id;
            $minDistance = null;
            foreach ($vendors as $v) {
                $dist = $this->haversineDistance(
                    (float) $v->gps_lat, (float) $v->gps_lng,
                    (float) $a->gps_lat, (float) $a->gps_lng
                );
                if ($minDistance === null || $dist < $minDistance) {
                    $minDistance = $dist;
                }
            }
            return [
                'name'        => $name,
                'label'       => $a->label,
                'address'     => $a->full_address,
                'lat'         => (float) $a->gps_lat,
                'lng'         => (float) $a->gps_lng,
                'distance'    => $minDistance !== null ? round($minDistance, 1) : null,
                'customerUrl' => route('admin.customers.show', $a->customer_id),
            ];
        });

        // Also include company addresses with GPS (from Company Details form)
        $seenCustomerIds = $addresses->pluck('customer_id')->unique()->toArray();
        $companyCustomers = \App\Models\Customer::with('user', 'company')
            ->whereHas('company', function ($q) {
                $q->whereNotNull('gps_lat')->whereNotNull('gps_lng')
                  ->where('gps_lat', '!=', 0)->where('gps_lng', '!=', 0);
            })->get();

        $companyMarkers = $companyCustomers->map(function ($cust) use ($vendors) {
            $co = $cust->company;
            $name = $cust->user?->name ?? 'Customer #' . $cust->id;
            $minDistance = null;
            foreach ($vendors as $v) {
                $dist = $this->haversineDistance(
                    (float) $v->gps_lat, (float) $v->gps_lng,
                    (float) $co->gps_lat, (float) $co->gps_lng
                );
                if ($minDistance === null || $dist < $minDistance) {
                    $minDistance = $dist;
                }
            }
            return [
                'name'        => $name,
                'label'       => $co->display_name ?: 'Company',
                'address'     => $co->full_address,
                'lat'         => (float) $co->gps_lat,
                'lng'         => (float) $co->gps_lng,
                'distance'    => $minDistance !== null ? round($minDistance, 1) : null,
                'customerUrl' => route('admin.customers.show', $cust->id),
            ];
        });

        $mapCustomers = $mapCustomers->concat($companyMarkers)->values();

        // Distance band stats
        $distanceBands = [
            ['label' => '0 – 10 km', 'min' => 0, 'max' => 10, 'count' => 0],
            ['label' => '10 – 20 km', 'min' => 10, 'max' => 20, 'count' => 0],
            ['label' => '20 – 30 km', 'min' => 20, 'max' => 30, 'count' => 0],
            ['label' => '30 – 40 km', 'min' => 30, 'max' => 40, 'count' => 0],
            ['label' => '40 – 50 km', 'min' => 40, 'max' => 50, 'count' => 0],
            ['label' => '50+ km', 'min' => 50, 'max' => PHP_INT_MAX, 'count' => 0],
        ];
        foreach ($mapCustomers as $c) {
            if ($c['distance'] === null) continue;
            foreach ($distanceBands as &$band) {
                if ($c['distance'] >= $band['min'] && $c['distance'] < $band['max']) {
                    $band['count']++;
                    break;
                }
            }
        }

        return view('admin.tracking.drivers', compact(
            'activeDrivers', 'idleDrivers', 'mapDrivers', 'mapCustomers', 'mapVendors', 'distanceBands'
        ));
    }

    public function driversJson()
    {
        $drivers = User::where('role', 'driver')
            ->where('is_active', true)
            ->get()
            ->map(function ($driver) {
                $lastLocation = $driver->driverLocations()
                    ->orderBy('recorded_at', 'desc')
                    ->first();

                $activeOrder = $driver->driverOrders()
                    ->whereIn('status', ['ASSIGNED', 'ACCEPTED', 'LOADED', 'IN_TRANSIT', 'ARRIVED'])
                    ->with('customer.user', 'deliveryAddress')
                    ->first();

                if (!$lastLocation) return null;

                return [
                    'id'        => $driver->id,
                    'name'      => $driver->name,
                    'phone'     => $driver->phone,
                    'lat'       => (float) $lastLocation->lat,
                    'lng'       => (float) $lastLocation->lng,
                    'speed'     => $lastLocation->speed ? round($lastLocation->speed) : 0,
                    'heading'   => $lastLocation->heading ? round($lastLocation->heading) : 0,
                    'accuracy'  => $lastLocation->accuracy ? round($lastLocation->accuracy) : null,
                    'updated'   => $lastLocation->recorded_at->diffForHumans(),
                    'updated_at' => $lastLocation->recorded_at->toIso8601String(),
                    'active'    => $activeOrder !== null,
                    'status'    => $activeOrder ? str_replace('_', ' ', $activeOrder->status) : 'Idle',
                    'order'     => $activeOrder ? $activeOrder->order_number : null,
                    'orderUrl'  => $activeOrder ? route('admin.orders.show', $activeOrder) : null,
                    'detailUrl' => route('admin.tracking.driver-detail', $driver),
                ];
            })
            ->filter()
            ->values();

        return response()->json($drivers);
    }

    public function driverDetail(User $driver)
    {
        abort_if($driver->role !== 'driver', 404);

        $activeOrders = $driver->driverOrders()
            ->whereIn('status', ['ASSIGNED', 'ACCEPTED', 'LOADED', 'IN_TRANSIT', 'ARRIVED'])
            ->with('customer.user', 'deliveryAddress')
            ->get();

        $recentLocations = $driver->driverLocations()
            ->with('order')
            ->orderBy('recorded_at', 'desc')
            ->limit(50)
            ->get();

        $todayDeliveries = $driver->driverOrders()
            ->where('status', 'DELIVERED')
            ->whereDate('updated_at', today())
            ->count();

        return view('admin.tracking.driver-detail', compact('driver', 'activeOrders', 'recentLocations', 'todayDeliveries'));
    }

    /**
     * Calculate distance between two GPS points using the Haversine formula.
     * Returns distance in kilometers.
     */
    private function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function orderTracking(Order $order)
    {
        $order->load('customer.user', 'driver', 'deliveryAddress', 'driverLocations');

        $locations = $order->driverLocations()
            ->orderBy('recorded_at', 'desc')
            ->limit(100)
            ->get();

        return view('admin.tracking.order', compact('order', 'locations'));
    }

    public function geocodeAddresses()
    {
        $token = config('services.mapbox.token');
        if (!$token) {
            return back()->with('success', 'ERROR: Mapbox token not configured.');
        }

        $updated = 0;
        $failed = 0;
        $log = [];

        // Diagnostics first
        $totalAddresses = Address::count();
        $log[] = "Total delivery addresses in DB: {$totalAddresses}";

        // Only geocode delivery addresses MISSING GPS (don't overwrite manual pins)
        $addresses = Address::where(function ($q) {
            $q->whereNull('gps_lat')->orWhere('gps_lat', 0);
        })->get();

        $log[] = "Addresses missing GPS: {$addresses->count()} (skipping " . (Address::count() - $addresses->count()) . " with manual pins)";

        foreach ($addresses as $address) {
            $query = collect([$address->line1, $address->city, $address->province, $address->postal_code, 'South Africa'])
                ->filter()
                ->implode(', ');

            if (empty(trim($query))) {
                $failed++;
                $log[] = "SKIP Addr#{$address->id}: empty";
                continue;
            }

            $result = $this->geocodeQuery($query, $token);
            if ($result) {
                $address->update(['gps_lat' => $result['lat'], 'gps_lng' => $result['lng']]);
                $updated++;
                $log[] = "OK Addr#{$address->id} '{$query}' => {$result['lat']},{$result['lng']}";
            } else {
                $failed++;
                $log[] = "FAIL Addr#{$address->id}: '{$query}'";
            }

            usleep(100000);
        }

        // Geocode ALL company addresses (re-geocode for accuracy)
        $companies = Company::where(function ($q) {
                $q->whereNull('gps_lat')->orWhere('gps_lat', 0);
            })
            ->whereNotNull('address_line1')
            ->where('address_line1', '!=', '')
            ->get();

        $log[] = "Companies missing GPS: {$companies->count()}";

        foreach ($companies as $company) {
            $query = collect([$company->address_line1, $company->city, $company->province, $company->postal_code, 'South Africa'])
                ->filter()
                ->implode(', ');

            if (empty(trim($query))) {
                $failed++;
                continue;
            }

            $result = $this->geocodeQuery($query, $token);
            if ($result) {
                $company->update(['gps_lat' => $result['lat'], 'gps_lng' => $result['lng']]);
                $updated++;
                $log[] = "OK Co#{$company->id}: {$result['lat']},{$result['lng']}";
            } else {
                $failed++;
                $log[] = "FAIL Co#{$company->id}: '{$query}'";
            }

            usleep(100000);
        }

        // Only geocode vendor addresses MISSING GPS
        $vendors = Vendor::where(function ($q) {
                $q->whereNull('gps_lat')->orWhere('gps_lat', 0);
            })
            ->whereNotNull('address_line1')
            ->where('address_line1', '!=', '')
            ->get();

        $log[] = "Vendors missing GPS: {$vendors->count()}";

        foreach ($vendors as $vendor) {
            $query = collect([$vendor->address_line1, $vendor->city, $vendor->province, $vendor->postal_code, 'South Africa'])
                ->filter()
                ->implode(', ');

            if (empty(trim($query))) {
                $failed++;
                continue;
            }

            $result = $this->geocodeQuery($query, $token);
            if ($result) {
                $vendor->update(['gps_lat' => $result['lat'], 'gps_lng' => $result['lng']]);
                $updated++;
                $log[] = "OK Vendor#{$vendor->id}: {$result['lat']},{$result['lng']}";
            } else {
                $failed++;
                $log[] = "FAIL Vendor#{$vendor->id}: '{$query}'";
            }

            usleep(100000);
        }

        $addressesWithGps = Address::whereNotNull('gps_lat')->where('gps_lat', '!=', 0)->count();
        $log[] = "After: {$addressesWithGps}/{$totalAddresses} addresses have GPS";

        $msg = "Geocoding: {$updated} updated, {$failed} failed. " . implode(' | ', $log);

        return back()->with('success', $msg);
    }

    private function geocodeQuery(string $query, string $token): ?array
    {
        try {
            $response = Http::get('https://api.mapbox.com/geocoding/v5/mapbox.places/' . urlencode($query) . '.json', [
                'access_token' => $token,
                'country' => 'za',
                'limit' => 1,
                'language' => 'en',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['features'])) {
                    $center = $data['features'][0]['center'];
                    return ['lng' => $center[0], 'lat' => $center[1]];
                }
            }
        } catch (\Throwable $e) {
            // fail silently per address
        }

        return null;
    }
}
