@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card bg-dark text-light mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Create New Promo Code</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.promo-codes.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="code" class="form-label">Code</label>
                                <input type="text" class="form-control bg-dark text-light border-secondary" id="code" name="code" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="type" class="form-label">Type</label>
                                <select class="form-control bg-dark text-light border-secondary" id="type" name="type" required>
                                    <option value="percentage">Percentage</option>
                                    <option value="fixed">Fixed</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="value" class="form-label">Value</label>
                                <input type="number" step="0.01" class="form-control bg-dark text-light border-secondary" id="value" name="value" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="min_order_amount" class="form-label">Min Order Amount</label>
                                <input type="number" step="0.01" class="form-control bg-dark text-light border-secondary" id="min_order_amount" name="min_order_amount">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="description" class="form-label">Description</label>
                                <input type="text" class="form-control bg-dark text-light border-secondary" id="description" name="description">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="max_discount" class="form-label">Max Discount</label>
                                <input type="number" step="0.01" class="form-control bg-dark text-light border-secondary" id="max_discount" name="max_discount">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="max_uses" class="form-label">Max Uses</label>
                                <input type="number" class="form-control bg-dark text-light border-secondary" id="max_uses" name="max_uses">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="max_uses_per_customer" class="form-label">Max Uses Per Customer</label>
                                <input type="number" class="form-control bg-dark text-light border-secondary" id="max_uses_per_customer" name="max_uses_per_customer">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="starts_at" class="form-label">Starts At</label>
                                <input type="datetime-local" class="form-control bg-dark text-light border-secondary" id="starts_at" name="starts_at">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="expires_at" class="form-label">Expires At</label>
                                <input type="datetime-local" class="form-control bg-dark text-light border-secondary" id="expires_at" name="expires_at">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="is_active" class="form-label d-block">Status</label>
                                <div class="form-check form-check-inline mt-2">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Create Promo Code</button>
                    </form>
                </div>
            </div>

            <div class="card bg-dark text-light">
                <div class="card-header">
                    <h5 class="mb-0">Promo Codes</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-dark table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Description</th>
                                    <th>Type</th>
                                    <th>Value</th>
                                    <th>Min Order</th>
                                    <th>Max Discount</th>
                                    <th>Max Uses</th>
                                    <th>Uses Per Customer</th>
                                    <th>Starts At</th>
                                    <th>Expires At</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($promoCodes as $promoCode)
                                <tr>
                                    <td><strong>{{ $promoCode->code }}</strong></td>
                                    <td>{{ $promoCode->description }}</td>
                                    <td><span class="badge bg-info">{{ ucfirst($promoCode->type) }}</span></td>
                                    <td>
                                        @if($promoCode->type === 'percentage')
                                            {{ $promoCode->value }}%
                                        @else
                                            R {{ number_format($promoCode->value, 2) }}
                                        @endif
                                    </td>
                                    <td>{{ $promoCode->min_order_amount ? 'R ' . number_format($promoCode->min_order_amount, 2) : '-' }}</td>
                                    <td>{{ $promoCode->max_discount ? 'R ' . number_format($promoCode->max_discount, 2) : '-' }}</td>
                                    <td>{{ $promoCode->max_uses ?? 'Unlimited' }}</td>
                                    <td>{{ $promoCode->max_uses_per_customer ?? 'Unlimited' }}</td>
                                    <td>{{ $promoCode->starts_at ? $promoCode->starts_at->format('Y-m-d H:i') : '-' }}</td>
                                    <td>{{ $promoCode->expires_at ? $promoCode->expires_at->format('Y-m-d H:i') : '-' }}</td>
                                    <td>
                                        @if($promoCode->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $promoCode->id }}">Edit</button>
                                        <form action="{{ route('admin.promo-codes.destroy', $promoCode->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this promo code?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Edit Modal -->
                                <div class="modal fade" id="editModal{{ $promoCode->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content bg-dark text-light">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Promo Code: {{ $promoCode->code }}</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('admin.promo-codes.update', $promoCode->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label">Code</label>
                                                            <input type="text" class="form-control bg-dark text-light border-secondary" name="code" value="{{ $promoCode->code }}" required>
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label">Type</label>
                                                            <select class="form-control bg-dark text-light border-secondary" name="type" required>
                                                                <option value="percentage" {{ $promoCode->type === 'percentage' ? 'selected' : '' }}>Percentage</option>
                                                                <option value="fixed" {{ $promoCode->type === 'fixed' ? 'selected' : '' }}>Fixed</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label">Value</label>
                                                            <input type="number" step="0.01" class="form-control bg-dark text-light border-secondary" name="value" value="{{ $promoCode->value }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-12 mb-3">
                                                            <label class="form-label">Description</label>
                                                            <input type="text" class="form-control bg-dark text-light border-secondary" name="description" value="{{ $promoCode->description }}">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label">Min Order Amount</label>
                                                            <input type="number" step="0.01" class="form-control bg-dark text-light border-secondary" name="min_order_amount" value="{{ $promoCode->min_order_amount }}">
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label">Max Discount</label>
                                                            <input type="number" step="0.01" class="form-control bg-dark text-light border-secondary" name="max_discount" value="{{ $promoCode->max_discount }}">
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label">Max Uses</label>
                                                            <input type="number" class="form-control bg-dark text-light border-secondary" name="max_uses" value="{{ $promoCode->max_uses }}">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label">Max Uses Per Customer</label>
                                                            <input type="number" class="form-control bg-dark text-light border-secondary" name="max_uses_per_customer" value="{{ $promoCode->max_uses_per_customer }}">
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label">Starts At</label>
                                                            <input type="datetime-local" class="form-control bg-dark text-light border-secondary" name="starts_at" value="{{ $promoCode->starts_at ? $promoCode->starts_at->format('Y-m-d\TH:i') : '' }}">
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label">Expires At</label>
                                                            <input type="datetime-local" class="form-control bg-dark text-light border-secondary" name="expires_at" value="{{ $promoCode->expires_at ? $promoCode->expires_at->format('Y-m-d\TH:i') : '' }}">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-12 mb-3">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $promoCode->is_active ? 'checked' : '' }}>
                                                                <label class="form-check-label">Active</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Update Promo Code</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <tr>
                                    <td colspan="12" class="text-center">No promo codes found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $promoCodes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
