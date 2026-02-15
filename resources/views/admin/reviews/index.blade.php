@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card bg-dark text-light">
                <div class="card-header">
                    <h5 class="mb-0">Product Reviews</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-dark table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Customer</th>
                                    <th>Rating</th>
                                    <th>Comment</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reviews as $review)
                                <tr>
                                    <td>
                                        <strong>{{ $review->product->name }}</strong>
                                    </td>
                                    <td>
                                        {{ $review->customer->user->name }}<br>
                                        <small class="text-muted">{{ $review->customer->user->email }}</small>
                                    </td>
                                    <td>
                                        <div class="text-warning">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= $review->rating)
                                                    &#9733;
                                                @else
                                                    &#9734;
                                                @endif
                                            @endfor
                                        </div>
                                        <small>({{ $review->rating }}/5)</small>
                                    </td>
                                    <td>
                                        @if($review->comment)
                                            {{ Str::limit($review->comment, 100) }}
                                        @else
                                            <em class="text-muted">No comment</em>
                                        @endif
                                    </td>
                                    <td>{{ $review->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        @if($review->is_approved === null)
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($review->is_approved)
                                            <span class="badge bg-success">Approved</span>
                                        @else
                                            <span class="badge bg-danger">Rejected</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($review->is_approved !== true)
                                        <form action="{{ route('admin.reviews.approve', $review->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                                Approve
                                            </button>
                                        </form>
                                        @endif

                                        @if($review->is_approved !== false)
                                        <form action="{{ route('admin.reviews.reject', $review->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-warning" title="Reject">
                                                Reject
                                            </button>
                                        </form>
                                        @endif

                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewModal{{ $review->id }}" title="View Full">
                                            View
                                        </button>

                                        <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this review?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- View Modal -->
                                <div class="modal fade" id="viewModal{{ $review->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content bg-dark text-light">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Review Details</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <h6 class="text-muted">Product</h6>
                                                    <p>{{ $review->product->name }}</p>
                                                </div>
                                                <div class="mb-3">
                                                    <h6 class="text-muted">Customer</h6>
                                                    <p>{{ $review->customer->user->name }}<br>
                                                    {{ $review->customer->user->email }}</p>
                                                </div>
                                                <div class="mb-3">
                                                    <h6 class="text-muted">Rating</h6>
                                                    <div class="text-warning" style="font-size: 1.5rem;">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            @if($i <= $review->rating)
                                                                &#9733;
                                                            @else
                                                                &#9734;
                                                            @endif
                                                        @endfor
                                                    </div>
                                                    <small>({{ $review->rating }}/5)</small>
                                                </div>
                                                <div class="mb-3">
                                                    <h6 class="text-muted">Comment</h6>
                                                    <p>{{ $review->comment ?? 'No comment provided.' }}</p>
                                                </div>
                                                <div class="mb-3">
                                                    <h6 class="text-muted">Date</h6>
                                                    <p>{{ $review->created_at->format('F j, Y \a\t g:i A') }}</p>
                                                </div>
                                                <div class="mb-3">
                                                    <h6 class="text-muted">Status</h6>
                                                    <p>
                                                        @if($review->is_approved === null)
                                                            <span class="badge bg-warning">Pending</span>
                                                        @elseif($review->is_approved)
                                                            <span class="badge bg-success">Approved</span>
                                                        @else
                                                            <span class="badge bg-danger">Rejected</span>
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">No reviews found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $reviews->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
