@extends('layouts.admin')
@section('title', 'Profitability Calculator')

@section('content')
<div class="page-header">
    <h1>Profitability Calculator</h1>
    <span class="badge" style="background:var(--danger, #e74c3c); color:#fff; padding:0.25rem 0.75rem; border-radius:4px; font-size:0.75rem;">Admin Only</span>
</div>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; align-items: start;">
    {{-- Input Card --}}
    <div class="card">
        <div class="card-body">
            <h3 style="margin-bottom:1rem; font-size:1rem;">Route</h3>

            <div class="form-group">
                <label class="form-label">Vendor (Origin)</label>
                <select id="calc-vendor" class="form-control" onchange="onRouteChange()">
                    <option value="" data-lat="" data-lng="" data-address="">-- Select vendor --</option>
                    @foreach($vendors as $v)
                        <option value="{{ $v->id }}" data-lat="{{ $v->gps_lat }}" data-lng="{{ $v->gps_lng }}" data-address="{{ $v->full_address }}">{{ $v->name }}@if($v->city) — {{ $v->city }}@endif</option>
                    @endforeach
                </select>
                @if($vendors->where('gps_lat', '!=', null)->count() === 0)
                <small style="color:var(--warning, #f39c12);">No vendors have GPS coordinates set. <a href="{{ route('admin.vendors.index') }}">Edit vendors</a> to add coordinates.</small>
                @endif
                <div id="vendor-address-display" style="display:none; margin-top:0.5rem; padding:0.5rem 0.75rem; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); border-radius:var(--radius-sm, 4px); font-size:0.8rem; color:var(--text-muted);">
                    <span style="color:#ef4444; font-weight:600;">Origin:</span> <span id="vendor-address-text"></span>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Delivery Address (Destination)</label>
                <select id="calc-address" class="form-control" onchange="onRouteChange()">
                    <option value="" data-lat="" data-lng="" data-address="">-- Select delivery address --</option>
                    @foreach($addresses as $a)
                        <option value="{{ $a->id }}" data-lat="{{ $a->gps_lat }}" data-lng="{{ $a->gps_lng }}" data-address="{{ $a->full_address }}">{{ $a->customer->user->name ?? 'Customer #'.$a->customer_id }} — {{ $a->label ? $a->label.': ' : '' }}{{ $a->line1 }}, {{ $a->city }}</option>
                    @endforeach
                </select>
                <div id="address-display" style="display:none; margin-top:0.5rem; padding:0.5rem 0.75rem; background:rgba(74,222,128,0.08); border:1px solid rgba(74,222,128,0.2); border-radius:var(--radius-sm, 4px); font-size:0.8rem; color:var(--text-muted);">
                    <span style="color:#4ade80; font-weight:600;">Destination:</span> <span id="address-text"></span>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Distance to Site (km, one-way)</label>
                <input type="number" id="calc-distance" class="form-control" step="0.1" min="0" placeholder="e.g. 45" oninput="calculate()">
                <small class="text-muted" id="calc-distance-hint">Auto-calculated from GPS when vendor & address are selected, or enter manually</small>
            </div>

            <hr style="border-color:rgba(255,255,255,0.08); margin:1rem 0;">
            <h3 style="margin-bottom:1rem; font-size:1rem;">Job Details</h3>

            <div class="form-group">
                <label class="form-label">Load Size (tons)</label>
                <input type="number" id="calc-load" class="form-control" step="0.1" min="0" placeholder="e.g. 8" oninput="calculate()">
            </div>

            <div class="form-group">
                <label class="form-label">Selling Price per Ton (excl. VAT)</label>
                <div style="position:relative;">
                    <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-muted,#999);">R</span>
                    <input type="number" id="calc-price-per-ton" class="form-control" style="padding-left:28px;" step="0.01" min="0" placeholder="e.g. 1450.00" oninput="calculate()">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Product Cost per Ton</label>
                <div style="position:relative;">
                    <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-muted,#999);">R</span>
                    <input type="number" id="calc-cost-per-ton" class="form-control" style="padding-left:28px;" step="0.01" min="0" placeholder="e.g. 800.00" oninput="calculate()">
                </div>
                @if($products->count())
                <small class="text-muted">
                    Quick fill:
                    @foreach($products as $p)
                        <a href="#" onclick="event.preventDefault(); document.getElementById('calc-cost-per-ton').value={{ (float) $p->cost_price }}; document.getElementById('calc-price-per-ton').value={{ (float) $p->price }}; calculate();" style="margin-right:0.5rem;">{{ $p->name }} (R{{ number_format($p->cost_price, 0) }})</a>
                    @endforeach
                </small>
                @endif
            </div>

            <hr style="border-color:rgba(255,255,255,0.08); margin:1rem 0;">
            <h3 style="margin-bottom:1rem; font-size:1rem;">Transport Costs</h3>

            <div class="form-group">
                <label class="form-label">Diesel Price per Litre</label>
                <div style="position:relative;">
                    <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-muted,#999);">R</span>
                    <input type="number" id="calc-diesel-price" class="form-control" style="padding-left:28px;" step="0.01" min="0" value="{{ number_format($defaultDieselPrice, 2, '.', '') }}" oninput="calculate()">
                </div>
                <small class="text-muted">Auto-filled from latest diesel log</small>
            </div>

            <div class="form-group">
                <label class="form-label">Truck Fuel Consumption (km/l)</label>
                <input type="number" id="calc-fuel-consumption" class="form-control" step="0.1" min="0.1" value="2" oninput="calculate()">
                <small class="text-muted">Kilometres per litre (default: 2)</small>
            </div>

            <div class="form-group">
                <label class="form-label">Delivery Fee Charged to Customer</label>
                <div style="position:relative;">
                    <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-muted,#999);">R</span>
                    <input type="number" id="calc-delivery-fee" class="form-control" style="padding-left:28px;" step="0.01" min="0" value="0" placeholder="0.00" oninput="calculate()">
                </div>
                <small class="text-muted">What you charge the customer for delivery (excl. VAT)</small>
            </div>

            <hr style="border-color:rgba(255,255,255,0.08); margin:1rem 0;">
            <h3 style="margin-bottom:1rem; font-size:1rem;">Additional Costs (Optional)</h3>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:0.75rem;">
                <div class="form-group">
                    <label class="form-label">Driver Cost for Trip</label>
                    <div style="position:relative;">
                        <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-muted,#999);">R</span>
                        <input type="number" id="calc-driver-cost" class="form-control" style="padding-left:28px;" step="0.01" min="0" value="0" oninput="calculate()">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Wear & Tear / Other</label>
                    <div style="position:relative;">
                        <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-muted,#999);">R</span>
                        <input type="number" id="calc-other-cost" class="form-control" style="padding-left:28px;" step="0.01" min="0" value="0" oninput="calculate()">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Results Card --}}
    <div>
        {{-- Summary --}}
        <div class="card" style="margin-bottom:1rem;">
            <div class="card-body" style="text-align:center; padding:1.5rem;">
                <h6 class="text-muted" style="font-size:0.7rem; text-transform:uppercase; margin-bottom:0.25rem;">Net Profit per Trip</h6>
                <h2 id="result-profit" style="font-size:2.25rem; margin:0; color:var(--success, #2ecc71);">R 0.00</h2>
                <div id="result-margin" style="font-size:0.9rem; margin-top:0.25rem;" class="text-muted">0% margin</div>
            </div>
        </div>

        {{-- Breakdown --}}
        <div class="card">
            <div class="card-body">
                <h3 style="margin-bottom:1rem; font-size:1rem;">Revenue Breakdown</h3>
                <table style="width:100%; font-size:0.875rem;">
                    <tbody>
                        <tr>
                            <td class="text-muted">Load Revenue</td>
                            <td style="text-align:right; font-weight:600;" id="result-load-revenue">R 0.00</td>
                        </tr>
                        <tr>
                            <td class="text-muted">+ Delivery Fee</td>
                            <td style="text-align:right; font-weight:600;" id="result-delivery-revenue">R 0.00</td>
                        </tr>
                        <tr style="border-top:1px solid rgba(255,255,255,0.1);">
                            <td><strong>Total Revenue (excl. VAT)</strong></td>
                            <td style="text-align:right; font-weight:700;" id="result-total-revenue">R 0.00</td>
                        </tr>
                    </tbody>
                </table>

                <h3 style="margin:1.25rem 0 1rem; font-size:1rem;">Cost Breakdown</h3>
                <table style="width:100%; font-size:0.875rem;">
                    <tbody>
                        <tr>
                            <td class="text-muted">Product Cost</td>
                            <td style="text-align:right;" id="result-product-cost">R 0.00</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Fuel Cost</td>
                            <td style="text-align:right;" id="result-fuel-cost">R 0.00</td>
                        </tr>
                        <tr>
                            <td class="text-muted" style="padding-left:1rem; font-size:0.8rem;" id="result-fuel-detail">0 L @ R0.00/L (0 km round trip)</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Driver Cost</td>
                            <td style="text-align:right;" id="result-driver-cost">R 0.00</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Other Costs</td>
                            <td style="text-align:right;" id="result-other-cost">R 0.00</td>
                        </tr>
                        <tr style="border-top:1px solid rgba(255,255,255,0.1);">
                            <td><strong>Total Costs</strong></td>
                            <td style="text-align:right; font-weight:700; color:var(--danger, #e74c3c);" id="result-total-cost">R 0.00</td>
                        </tr>
                    </tbody>
                </table>

                <div style="margin-top:1.25rem; padding:0.75rem; border-radius:var(--radius, 8px); background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.08);">
                    <table style="width:100%; font-size:0.875rem;">
                        <tbody>
                            <tr>
                                <td class="text-muted">Profit per Ton</td>
                                <td style="text-align:right; font-weight:600;" id="result-profit-per-ton">R 0.00</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Cost per km (round trip)</td>
                                <td style="text-align:right; font-weight:600;" id="result-cost-per-km">R 0.00</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Revenue per km</td>
                                <td style="text-align:right; font-weight:600;" id="result-revenue-per-km">R 0.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- VAT Card --}}
        <div class="card" style="margin-top:1rem;">
            <div class="card-body">
                <h3 style="margin-bottom:1rem; font-size:1rem;">Customer Invoice (incl. 15% VAT)</h3>
                <table style="width:100%; font-size:0.875rem;">
                    <tbody>
                        <tr>
                            <td class="text-muted">Subtotal</td>
                            <td style="text-align:right;" id="result-vat-subtotal">R 0.00</td>
                        </tr>
                        <tr>
                            <td class="text-muted">VAT (15%)</td>
                            <td style="text-align:right;" id="result-vat-amount">R 0.00</td>
                        </tr>
                        <tr style="border-top:1px solid rgba(255,255,255,0.1);">
                            <td><strong>Total incl. VAT</strong></td>
                            <td style="text-align:right; font-weight:700;" id="result-vat-total">R 0.00</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Haversine formula: straight-line distance between two GPS points in km
function haversineKm(lat1, lng1, lat2, lng2) {
    var R = 6371; // Earth radius in km
    var dLat = (lat2 - lat1) * Math.PI / 180;
    var dLng = (lng2 - lng1) * Math.PI / 180;
    var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLng / 2) * Math.sin(dLng / 2);
    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
}

function onRouteChange() {
    var vendorSel = document.getElementById('calc-vendor');
    var addressSel = document.getElementById('calc-address');
    var hint = document.getElementById('calc-distance-hint');
    var distInput = document.getElementById('calc-distance');

    var vOpt = vendorSel.options[vendorSel.selectedIndex];
    var aOpt = addressSel.options[addressSel.selectedIndex];

    // Show vendor address
    var vendorBox = document.getElementById('vendor-address-display');
    var vendorText = document.getElementById('vendor-address-text');
    var vAddr = vOpt ? vOpt.getAttribute('data-address') : '';
    if (vAddr) {
        vendorText.textContent = vAddr;
        vendorBox.style.display = 'block';
    } else {
        vendorBox.style.display = 'none';
    }

    // Show delivery address
    var addrBox = document.getElementById('address-display');
    var addrText = document.getElementById('address-text');
    var aAddr = aOpt ? aOpt.getAttribute('data-address') : '';
    if (aAddr) {
        addrText.textContent = aAddr;
        addrBox.style.display = 'block';
    } else {
        addrBox.style.display = 'none';
    }

    if (!vOpt || !aOpt) { calculate(); return; }

    var vLat = parseFloat(vOpt.getAttribute('data-lat'));
    var vLng = parseFloat(vOpt.getAttribute('data-lng'));
    var aLat = parseFloat(aOpt.getAttribute('data-lat'));
    var aLng = parseFloat(aOpt.getAttribute('data-lng'));

    if (vLat && vLng && aLat && aLng) {
        var straight = haversineKm(vLat, vLng, aLat, aLng);
        // Multiply by 1.3 to approximate road distance
        var road = Math.round(straight * 1.3 * 10) / 10;
        distInput.value = road;
        hint.textContent = 'Estimated ' + road + ' km (straight line ' + straight.toFixed(1) + ' km × 1.3 road factor). You can adjust.';
    } else {
        hint.textContent = 'GPS coordinates missing for selection. Enter distance manually.';
    }

    calculate();
}

function calculate() {
    var distance = parseFloat(document.getElementById('calc-distance').value) || 0;
    var load = parseFloat(document.getElementById('calc-load').value) || 0;
    var pricePerTon = parseFloat(document.getElementById('calc-price-per-ton').value) || 0;
    var costPerTon = parseFloat(document.getElementById('calc-cost-per-ton').value) || 0;
    var dieselPrice = parseFloat(document.getElementById('calc-diesel-price').value) || 0;
    var deliveryFee = parseFloat(document.getElementById('calc-delivery-fee').value) || 0;
    var fuelConsumption = parseFloat(document.getElementById('calc-fuel-consumption').value) || 2;
    var driverCost = parseFloat(document.getElementById('calc-driver-cost').value) || 0;
    var otherCost = parseFloat(document.getElementById('calc-other-cost').value) || 0;

    // Fuel: variable km/l, round trip
    var roundTrip = distance * 2;
    var litresNeeded = fuelConsumption > 0 ? roundTrip / fuelConsumption : 0;
    var fuelCost = litresNeeded * dieselPrice;

    // Revenue
    var loadRevenue = load * pricePerTon;
    var totalRevenue = loadRevenue + deliveryFee;

    // Costs
    var productCost = load * costPerTon;
    var totalCost = productCost + fuelCost + driverCost + otherCost;

    // Profit
    var profit = totalRevenue - totalCost;
    var margin = totalRevenue > 0 ? (profit / totalRevenue * 100) : 0;
    var profitPerTon = load > 0 ? profit / load : 0;
    var costPerKm = roundTrip > 0 ? totalCost / roundTrip : 0;
    var revenuePerKm = roundTrip > 0 ? totalRevenue / roundTrip : 0;

    // VAT
    var vatSubtotal = totalRevenue;
    var vatAmount = totalRevenue * 0.15;
    var vatTotal = totalRevenue + vatAmount;

    // Format helper
    function R(v) { return 'R ' + v.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','); }

    // Update profit display
    var profitEl = document.getElementById('result-profit');
    profitEl.textContent = R(profit);
    profitEl.style.color = profit >= 0 ? 'var(--success, #2ecc71)' : 'var(--danger, #e74c3c)';
    document.getElementById('result-margin').textContent = margin.toFixed(1) + '% margin';

    // Revenue
    document.getElementById('result-load-revenue').textContent = R(loadRevenue);
    document.getElementById('result-delivery-revenue').textContent = R(deliveryFee);
    document.getElementById('result-total-revenue').textContent = R(totalRevenue);

    // Costs
    document.getElementById('result-product-cost').textContent = R(productCost);
    document.getElementById('result-fuel-cost').textContent = R(fuelCost);
    document.getElementById('result-fuel-detail').textContent = litresNeeded.toFixed(1) + ' L @ R' + dieselPrice.toFixed(2) + '/L (' + roundTrip.toFixed(0) + ' km round trip)';
    document.getElementById('result-driver-cost').textContent = R(driverCost);
    document.getElementById('result-other-cost').textContent = R(otherCost);
    document.getElementById('result-total-cost').textContent = R(totalCost);

    // Per-unit stats
    document.getElementById('result-profit-per-ton').textContent = R(profitPerTon);
    document.getElementById('result-cost-per-km').textContent = R(costPerKm);
    document.getElementById('result-revenue-per-km').textContent = R(revenuePerKm);

    // VAT
    document.getElementById('result-vat-subtotal').textContent = R(vatSubtotal);
    document.getElementById('result-vat-amount').textContent = R(vatAmount);
    document.getElementById('result-vat-total').textContent = R(vatTotal);
}

// Run on load to initialize
calculate();
</script>
@endpush
@endsection
