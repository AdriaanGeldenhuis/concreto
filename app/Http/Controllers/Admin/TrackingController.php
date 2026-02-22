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

        // Separate drivers with active tracking from idle ones
        $activeDrivers = $drivers->filter(fn($d) => $d->active_order !== null);
        $idleDrivers = $drivers->filter(fn($d) => $d->active_order === null);

        // Build map marker data for drivers with GPS
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

        // Customer addresses with GPS for map markers (green)
        $mapCustomers = Address::with('customer.user')
            ->whereNotNull('gps_lat')
            ->whereNotNull('gps_lng')
            ->where('gps_lat', '!=', 0)
            ->where('gps_lng', '!=', 0)
            ->get()
            ->filter(fn($a) => $a->gps_lat && $a->gps_lng)
            ->map(function ($a) {
                $name = $a->customer->user->name ?? 'Customer #' . $a->customer_id;
                return [
                    'name'    => $name,
                    'label'   => $a->label,
                    'address' => $a->full_address,
                    'lat'     => (float) $a->gps_lat,
                    'lng'     => (float) $a->gps_lng,
                ];
            })->values();

        // Vendor locations with GPS for map markers (red)
        $mapVendors = Vendor::where('is_active', true)
            ->whereNotNull('gps_lat')
            ->whereNotNull('gps_lng')
            ->where('gps_lat', '!=', 0)
            ->where('gps_lng', '!=', 0)
            ->get()
            ->filter(fn($v) => $v->gps_lat && $v->gps_lng)
            ->map(function ($v) {
                return [
                    'name'    => $v->name,
                    'address' => $v->full_address,
                    'lat'     => (float) $v->gps_lat,
                    'lng'     => (float) $v->gps_lng,
                ];
            })->values();

        return view('admin.tracking.drivers', compact('activeDrivers', 'idleDrivers', 'mapDrivers', 'mapCustomers', 'mapVendors'));
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

        // Geocode customer delivery addresses missing GPS
        $addresses = Address::where(function ($q) {
            $q->whereNull('gps_lat')->orWhere('gps_lat', 0);
        })->get();

        $log[] = "Addresses missing GPS: {$addresses->count()}";

        foreach ($addresses as $address) {
            $query = collect([$address->line1, $address->city, $address->province, $address->postal_code])
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
                $log[] = "OK Addr#{$address->id}: {$result['lat']},{$result['lng']}";
            } else {
                $failed++;
                $log[] = "FAIL Addr#{$address->id}: '{$query}'";
            }

            usleep(100000);
        }

        // Geocode company addresses missing GPS
        $companies = Company::where(function ($q) {
                $q->whereNull('gps_lat')->orWhere('gps_lat', 0);
            })
            ->whereNotNull('address_line1')
            ->where('address_line1', '!=', '')
            ->get();

        $log[] = "Companies missing GPS: {$companies->count()}";

        foreach ($companies as $company) {
            $query = collect([$company->address_line1, $company->city, $company->province, $company->postal_code])
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

        // Geocode vendor addresses missing GPS
        $vendors = Vendor::where(function ($q) {
                $q->whereNull('gps_lat')->orWhere('gps_lat', 0);
            })
            ->whereNotNull('address_line1')
            ->where('address_line1', '!=', '')
            ->get();

        $log[] = "Vendors missing GPS: {$vendors->count()}";

        foreach ($vendors as $vendor) {
            $query = collect([$vendor->address_line1, $vendor->city, $vendor->province, $vendor->postal_code])
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
