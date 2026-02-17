@extends('layouts.admin')
@section('title', 'Message Details')
@section('content')
    <div class="page-header">
        <div class="breadcrumb"><a href="{{ route('admin.messages.index') }}">Messages</a> / #{{ $contactMessage->id }}</div>
        <h1>
            @if($contactMessage->type === 'quote_request')
                <span class="badge badge-primary">Quote Request</span>
            @else
                <span class="badge badge-info">Contact Message</span>
            @endif
            from {{ $contactMessage->name }}
        </h1>
    </div>

    <div class="card">
        <div class="card-header">
            <span>Details</span>
            @if($contactMessage->status === 'new')
                <span class="badge badge-warning">New</span>
            @elseif($contactMessage->status === 'read')
                <span class="badge badge-info">Read</span>
            @else
                <span class="badge badge-success">Replied</span>
            @endif
        </div>
        <div class="card-body">
            <div class="form-row mb-2">
                <div>
                    <label class="form-label">Name</label>
                    <p>{{ $contactMessage->name }}</p>
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <p><a href="mailto:{{ $contactMessage->email }}">{{ $contactMessage->email }}</a></p>
                </div>
            </div>
            <div class="form-row mb-2">
                <div>
                    <label class="form-label">Phone</label>
                    <p>{{ $contactMessage->phone ?: 'Not provided' }}</p>
                </div>
                <div>
                    <label class="form-label">Received</label>
                    <p>{{ $contactMessage->created_at->format('d M Y H:i') }}</p>
                </div>
            </div>

            @if($contactMessage->type === 'contact')
                <label class="form-label">Message</label>
                <div class="card" style="background:var(--bg);margin-bottom:0;">
                    <div class="card-body">{{ $contactMessage->message }}</div>
                </div>
            @else
                <label class="form-label">Products Needed</label>
                <div class="card" style="background:var(--bg);">
                    <div class="card-body" style="white-space:pre-line;">{{ $contactMessage->products_needed }}</div>
                </div>
                <label class="form-label">Delivery Address</label>
                <p>{{ $contactMessage->delivery_address }}</p>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header">Reply to {{ $contactMessage->name }}</div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.messages.reply', $contactMessage) }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Your Reply</label>
                    <textarea name="admin_notes" class="form-control" rows="5" placeholder="Type your reply to {{ $contactMessage->name }}...">{{ old('admin_notes', $contactMessage->admin_notes) }}</textarea>
                    @error('admin_notes')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-check" style="margin-bottom: 1rem;">
                    <input type="checkbox" name="send_email" value="1" id="send_email" checked>
                    <label for="send_email">Send reply via email to {{ $contactMessage->email }}</label>
                </div>
                <button type="submit" class="btn btn-primary">Send Reply</button>
                <small class="text-muted" style="margin-left: 0.5rem;">Uncheck the box above to save as internal notes only</small>
            </form>
        </div>
    </div>

    <div class="d-flex gap-1 mt-2">
        <a href="mailto:{{ $contactMessage->email }}?subject=Re: {{ $contactMessage->type === 'quote_request' ? 'Your Quote Request' : 'Your Message' }} - {{ $siteSettings['company_name'] ?? 'Concreto' }}" class="btn btn-secondary">Open in Email Client</a>
        @if($contactMessage->phone)
        <a href="tel:{{ $contactMessage->phone }}" class="btn btn-outline">Call</a>
        @endif
        <form method="POST" action="{{ route('admin.messages.destroy', $contactMessage) }}" onsubmit="return confirm('Delete this message?')" style="margin-left:auto;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
        </form>
    </div>
@endsection
