@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="text-light">Quote #{{ $quote->id }}</h3>
                <div class="d-flex gap-2">
                    <form action="{{ route('admin.quotes.send', $quote->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            Send to Customer
                        </button>
                    </form>
                    <a href="{{ route('admin.quotes.pdf', $quote->id) }}" class="btn btn-secondary" target="_blank">
                        Download PDF
                    </a>
                    <form action="{{ route('admin.quotes.convert', $quote->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to convert this quote to an order?');">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            Convert to Order
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-dark text-light h-100">
                <div class="card-header">
                    <h5 class="mb-0">Quote Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-muted">Quote ID</h6>
                        <p class="mb-0"><strong>#{{ $quote->id }}</strong></p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-muted">Status</h6>
                        <p class="mb-0">
                            <span class="badge bg-info">{{ ucfirst($quote->status ?? 'pending') }}</span>
                        </p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-muted">Created At</h6>
                        <p class="mb-0">{{ $quote->created_at->format('F j, Y \a\t g:i A') }}</p>
                    </div>
                    @if($quote->valid_until)
                    <div class="mb-3">
                        <h6 class="text-muted">Valid Until</h6>
                        <p class="mb-0">{{ $quote->valid_until->format('F j, Y') }}</p>
                    </div>
                    @endif
                    @if($quote->notes)
                    <div class="mb-3">
                        <h6 class="text-muted">Notes</h6>
                        <p class="mb-0">{{ $quote->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card bg-dark text-light h-100">
                <div class="card-header">
                    <h5 class="mb-0">Customer Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-muted">Name</h6>
                        <p class="mb-0"><strong>{{ $quote->customer->user->name }}</strong></p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-muted">Email</h6>
                        <p class="mb-0">{{ $quote->customer->user->email }}</p>
                    </div>
                    @if($quote->customer->user->phone)
                    <div class="mb-3">
                        <h6 class="text-muted">Phone</h6>
                        <p class="mb-0">{{ $quote->customer->user->phone }}</p>
                    </div>
                    @endif
                    @if($quote->customer->company_name)
                    <div class="mb-3">
                        <h6 class="text-muted">Company</h6>
                        <p class="mb-0">{{ $quote->customer->company_name }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card bg-dark text-light">
                <div class="card-header">
                    <h5 class="mb-0">Quote Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-dark table-striped">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $subtotal = 0;
                                @endphp
                                @foreach($quote->items as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->product->name }}</strong>
                                        @if($item->notes)
                                        <br><small class="text-muted">{{ $item->notes }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">R {{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end">
                                        @php
                                            $itemTotal = $item->quantity * $item->unit_price;
                                            $subtotal += $itemTotal;
                                        @endphp
                                        R {{ number_format($itemTotal, 2) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                    <td class="text-end"><strong>R {{ number_format($subtotal, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>VAT (15%):</strong></td>
                                    <td class="text-end"><strong>R {{ number_format($subtotal * 0.15, 2) }}</strong></td>
                                </tr>
                                <tr class="table-active">
                                    <td colspan="3" class="text-end"><h5 class="mb-0">Total (incl. VAT):</h5></td>
                                    <td class="text-end">
                                        <h5 class="mb-0 text-success">R {{ number_format($subtotal * 1.15, 2) }}</h5>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($quote->terms_and_conditions)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card bg-dark text-light">
                <div class="card-header">
                    <h5 class="mb-0">Terms & Conditions</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0 small">{{ $quote->terms_and_conditions }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
