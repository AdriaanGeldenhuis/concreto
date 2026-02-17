@extends('layouts.admin')
@section('title', 'Reviews')
@section('content')
    <div class="page-header">
        <h1>Product Reviews</h1>
        <span class="badge badge-secondary">{{ $stats['total'] }} total</span>
    </div>

    {{-- Analytics Cards --}}
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap:1rem; margin-bottom:1.5rem;">
        <div class="card"><div class="card-body" style="padding:1rem; text-align:center;"><h6 class="text-muted mb-1" style="font-size:0.75rem;">Average Rating</h6><h4 class="mb-0"><span class="text-warning">&#9733;</span> {{ $stats['avg_rating'] }}/5</h4></div></div>
        <div class="card"><div class="card-body" style="padding:1rem; text-align:center;"><h6 class="text-muted mb-1" style="font-size:0.75rem;">Pending</h6><h4 class="mb-0 {{ $stats['pending'] > 0 ? 'text-warning' : '' }}">{{ $stats['pending'] }}</h4></div></div>
        <div class="card"><div class="card-body" style="padding:1rem; text-align:center;"><h6 class="text-muted mb-1" style="font-size:0.75rem;">Approved</h6><h4 class="mb-0 text-success">{{ $stats['approved'] }}</h4></div></div>
        <div class="card"><div class="card-body" style="padding:1rem; text-align:center;"><h6 class="text-muted mb-1" style="font-size:0.75rem;">Rejected</h6><h4 class="mb-0 text-danger">{{ $stats['rejected'] }}</h4></div></div>
        <div class="card"><div class="card-body" style="padding:1rem; text-align:center;"><h6 class="text-muted mb-1" style="font-size:0.75rem;">Replied</h6><h4 class="mb-0">{{ $stats['with_reply'] }}</h4></div></div>
    </div>

    {{-- Rating Distribution --}}
    @if($stats['total'] > 0)
    <div class="card mb-2">
        <div class="card-body" style="padding:0.75rem 1rem;">
            <div style="display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
                <span class="text-muted" style="font-size:0.8125rem; white-space:nowrap;">Rating Distribution:</span>
                @foreach($ratingDist as $rating => $count)
                    @php $pct = $stats['total'] > 0 ? round(($count / $stats['total']) * 100) : 0; @endphp
                    <div style="display:flex; align-items:center; gap:0.25rem;">
                        <span style="font-size:0.8125rem;">{{ $rating }}&#9733;</span>
                        <div style="width:80px; height:8px; background:rgba(255,255,255,0.1); border-radius:4px; overflow:hidden;">
                            <div style="width:{{ $pct }}%; height:100%; background:var(--warning, #f59e0b); border-radius:4px;"></div>
                        </div>
                        <span style="font-size:0.75rem;" class="text-muted">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Filters --}}
    <div class="card mb-2">
        <div class="card-body">
            <form class="filters" method="GET" style="flex-wrap:wrap;">
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="">All</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Rating</label>
                    <select name="rating" class="form-control">
                        <option value="">All</option>
                        @for($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>{{ $i }} &#9733;</option>
                        @endfor
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Reply</label>
                    <select name="replied" class="form-control">
                        <option value="">All</option>
                        <option value="yes" {{ request('replied') === 'yes' ? 'selected' : '' }}>Has Reply</option>
                        <option value="no" {{ request('replied') === 'no' ? 'selected' : '' }}>No Reply</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Product, customer, comment..." value="{{ request('search') }}">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    @if(request()->hasAny(['status', 'rating', 'replied', 'search']))
                        <a href="{{ route('admin.reviews.index') }}" class="btn btn-ghost btn-sm">Clear</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Bulk Actions --}}
    <div class="card mb-2" id="bulk-actions" style="display:none;">
        <div class="card-body" style="padding:0.75rem;">
            <div style="display:flex; align-items:center; gap:1rem;">
                <span id="selected-count" class="font-semibold">0 selected</span>
                <form method="POST" action="{{ route('admin.reviews.bulk-approve') }}" id="bulk-approve-form">
                    @csrf
                    <div id="bulk-ids"></div>
                    <button type="submit" class="btn btn-sm btn-success">Approve Selected</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Reviews Table --}}
    <div class="card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all"></th>
                        <th>Product</th>
                        <th>Customer</th>
                        <th>Rating</th>
                        <th>Comment</th>
                        <th>Reply</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($reviews as $review)
                    <tr>
                        <td><input type="checkbox" class="review-cb" value="{{ $review->id }}"></td>
                        <td class="font-semibold">{{ $review->product->name ?? '-' }}</td>
                        <td>{{ $review->customer->user->name ?? '-' }}<br><small class="text-muted">{{ $review->customer->user->email ?? '' }}</small></td>
                        <td>
                            <span class="text-warning">@for($i = 1; $i <= 5; $i++)@if($i <= $review->rating)&#9733;@else&#9734;@endif @endfor</span>
                            <small>({{ $review->rating }})</small>
                        </td>
                        <td style="max-width:200px;">{{ Str::limit($review->comment, 80) ?: '-' }}</td>
                        <td>
                            @if($review->admin_reply)
                                <span class="badge badge-info">Replied</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $review->created_at->format('d M Y') }}</td>
                        <td>
                            @if($review->is_approved === null)
                                <span class="badge badge-warning">Pending</span>
                            @elseif($review->is_approved)
                                <span class="badge badge-success">Approved</span>
                            @else
                                <span class="badge badge-danger">Rejected</span>
                            @endif
                        </td>
                        <td class="text-right" style="white-space:nowrap;">
                            @if($review->is_approved !== true)
                            <form action="{{ route('admin.reviews.approve', $review->id) }}" method="POST" class="d-inline">@csrf<button type="submit" class="btn btn-sm btn-success">Approve</button></form>
                            @endif
                            @if($review->is_approved !== false)
                            <form action="{{ route('admin.reviews.reject', $review->id) }}" method="POST" class="d-inline">@csrf<button type="submit" class="btn btn-sm btn-warning">Reject</button></form>
                            @endif
                            <button type="button" class="btn btn-sm btn-outline" onclick="document.getElementById('modal-{{ $review->id }}').style.display='flex'">View</button>
                            <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this review?');">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-danger">Delete</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9"><div class="empty-state"><h3>No reviews found</h3><p>Try adjusting your filters.</p></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($reviews->hasPages())
        <div class="pagination">{!! $reviews->appends(request()->query())->links('pagination::simple-default') !!}</div>
    @endif

    {{-- View/Reply Modals --}}
    @foreach($reviews as $review)
    <div id="modal-{{ $review->id }}" style="display:none; position:fixed; inset:0; z-index:1000; background:rgba(0,0,0,0.7); align-items:center; justify-content:center;" onclick="if(event.target===this)this.style.display='none'">
        <div style="background:var(--card,#1a1d23); border:1px solid var(--border-color); border-radius:var(--radius,8px); padding:1.5rem; max-width:550px; width:95%; max-height:90vh; overflow-y:auto;" onclick="event.stopPropagation()">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <h3 style="margin:0;">Review Details</h3>
                <button onclick="this.closest('[id^=modal]').style.display='none'" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
            </div>
            <div style="margin-bottom:0.75rem;"><strong>Product:</strong> {{ $review->product->name ?? '-' }}</div>
            <div style="margin-bottom:0.75rem;"><strong>Customer:</strong> {{ $review->customer->user->name ?? '-' }} ({{ $review->customer->user->email ?? '' }})</div>
            <div style="margin-bottom:0.75rem;"><strong>Rating:</strong> <span class="text-warning">@for($i = 1; $i <= 5; $i++)@if($i <= $review->rating)&#9733;@else&#9734;@endif @endfor</span> ({{ $review->rating }}/5)</div>
            <div style="margin-bottom:0.75rem;"><strong>Date:</strong> {{ $review->created_at->format('d M Y H:i') }}</div>
            <div style="margin-bottom:0.75rem;"><strong>Comment:</strong><br>{{ $review->comment ?? 'No comment.' }}</div>

            @if($review->admin_reply)
                <div style="margin-bottom:0.75rem; padding:0.75rem; background:rgba(59,130,246,0.1); border-radius:var(--radius,8px);">
                    <strong>Admin Reply:</strong> <small class="text-muted">({{ $review->admin_reply_at?->diffForHumans() }})</small><br>
                    {{ $review->admin_reply }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.reviews.reply', $review->id) }}" style="margin-top:1rem;">
                @csrf
                <label class="form-label">{{ $review->admin_reply ? 'Update Reply' : 'Write Reply' }}</label>
                <textarea name="admin_reply" class="form-control" rows="3" required placeholder="Write a response to this review...">{{ $review->admin_reply }}</textarea>
                <button type="submit" class="btn btn-primary btn-sm mt-1">{{ $review->admin_reply ? 'Update Reply' : 'Save Reply' }}</button>
            </form>
        </div>
    </div>
    @endforeach

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sa = document.getElementById('select-all');
    const cbs = document.querySelectorAll('.review-cb');
    const bar = document.getElementById('bulk-actions');
    const cnt = document.getElementById('selected-count');

    function update() {
        const checked = document.querySelectorAll('.review-cb:checked');
        bar.style.display = checked.length > 0 ? 'block' : 'none';
        cnt.textContent = checked.length + ' selected';
        const c = document.getElementById('bulk-ids');
        c.innerHTML = '';
        checked.forEach(cb => { const i = document.createElement('input'); i.type='hidden'; i.name='review_ids[]'; i.value=cb.value; c.appendChild(i); });
    }
    sa.addEventListener('change', function() { cbs.forEach(cb => cb.checked = this.checked); update(); });
    cbs.forEach(cb => cb.addEventListener('change', update));
});
</script>
@endpush
@endsection
