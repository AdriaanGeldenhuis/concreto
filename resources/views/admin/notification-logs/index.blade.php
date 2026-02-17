@extends('layouts.admin')
@section('title', 'Notification & Email Logs')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Notification & Email Logs</h1>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Notifications</h6>
                    <h4 class="mb-0">{{ number_format($stats['notif_total']) }}</h4>
                    <small class="text-success">{{ $stats['notif_sent'] }} sent</small> &middot;
                    <small class="text-danger">{{ $stats['notif_failed'] }} failed</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Emails</h6>
                    <h4 class="mb-0">{{ number_format($stats['email_total']) }}</h4>
                    <small class="text-success">{{ $stats['email_sent'] }} sent</small> &middot;
                    <small class="text-danger">{{ $stats['email_failed'] }} failed</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Payment Events</h6>
                    <h4 class="mb-0">{{ number_format($stats['events_total']) }}</h4>
                    <small class="text-success">{{ $stats['events_processed'] }} processed</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'notifications' ? 'active' : '' }}" href="?tab=notifications">
                Notifications ({{ number_format($stats['notif_total']) }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'emails' ? 'active' : '' }}" href="?tab=emails">
                Email Logs ({{ number_format($stats['email_total']) }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'events' ? 'active' : '' }}" href="?tab=events">
                Payment Events ({{ number_format($stats['events_total']) }})
            </a>
        </li>
    </ul>

    @if($tab === 'notifications')
    <!-- Notification Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <input type="hidden" name="tab" value="notifications">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="notif_search" class="form-control" placeholder="Recipient, subject..." value="{{ request('notif_search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="notif_status" class="form-select">
                        <option value="">All</option>
                        <option value="sent" {{ request('notif_status') === 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="failed" {{ request('notif_status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From</label>
                    <input type="date" name="notif_from" class="form-control" value="{{ request('notif_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To</label>
                    <input type="date" name="notif_to" class="form-control" value="{{ request('notif_to') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="?tab=notifications" class="btn btn-secondary">Reset</a>
                    <a href="{{ route('admin.notification-logs.export', ['type' => 'notifications']) }}" class="btn btn-outline-primary">Export</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Notification Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Channel</th>
                            <th>Recipient</th>
                            <th>Subject</th>
                            <th>Template</th>
                            <th>Status</th>
                            <th>Attempts</th>
                            <th>Error</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($notifications as $log)
                            <tr class="{{ $log->status === 'failed' ? 'table-danger' : '' }}">
                                <td class="text-nowrap">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                                <td><span class="badge bg-info">{{ $log->channel }}</span></td>
                                <td>{{ $log->recipient }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($log->subject, 40) }}</td>
                                <td><code>{{ $log->template_key }}</code></td>
                                <td>
                                    <span class="badge bg-{{ $log->status === 'sent' ? 'success' : ($log->status === 'failed' ? 'danger' : 'warning') }}">
                                        {{ $log->status }}
                                    </span>
                                </td>
                                <td class="text-center">{{ $log->attempts }}</td>
                                <td class="text-small text-danger" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $log->error_message }}">
                                    {{ $log->error_message ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No notification logs found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @if($notifications->hasPages())
        <div class="mt-3">{!! $notifications->appends(request()->query())->links('pagination::simple-default') !!}</div>
    @endif
    @endif

    @if($tab === 'emails')
    <!-- Email Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <input type="hidden" name="tab" value="emails">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="email_search" class="form-control" placeholder="To email, subject..." value="{{ request('email_search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="email_status" class="form-select">
                        <option value="">All</option>
                        <option value="sent" {{ request('email_status') === 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="failed" {{ request('email_status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From</label>
                    <input type="date" name="email_from" class="form-control" value="{{ request('email_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To</label>
                    <input type="date" name="email_to" class="form-control" value="{{ request('email_to') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="?tab=emails" class="btn btn-secondary">Reset</a>
                    <a href="{{ route('admin.notification-logs.export', ['type' => 'emails']) }}" class="btn btn-outline-primary">Export</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>To</th>
                            <th>Subject</th>
                            <th>Template</th>
                            <th>Related</th>
                            <th>Status</th>
                            <th>Error</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($emails as $log)
                            <tr class="{{ $log->status === 'failed' ? 'table-danger' : '' }}">
                                <td class="text-nowrap">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                                <td>{{ $log->to_email }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($log->subject, 40) }}</td>
                                <td><code>{{ $log->template }}</code></td>
                                <td class="text-muted">{{ $log->related_type }}#{{ $log->related_id }}</td>
                                <td>
                                    <span class="badge bg-{{ $log->status === 'sent' ? 'success' : 'danger' }}">{{ $log->status }}</span>
                                </td>
                                <td class="text-small text-danger" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $log->error_message }}">
                                    {{ $log->error_message ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No email logs found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @if($emails->hasPages())
        <div class="mt-3">{!! $emails->appends(request()->query())->links('pagination::simple-default') !!}</div>
    @endif
    @endif

    @if($tab === 'events')
    <!-- Payment Event Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <input type="hidden" name="tab" value="events">
                <div class="col-md-2">
                    <label class="form-label">Event Type</label>
                    <input type="text" name="event_type" class="form-control" placeholder="e.g. payment.succeeded" value="{{ request('event_type') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="event_status" class="form-select">
                        <option value="">All</option>
                        <option value="processed" {{ request('event_status') === 'processed' ? 'selected' : '' }}>Processed</option>
                        <option value="failed" {{ request('event_status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="ignored" {{ request('event_status') === 'ignored' ? 'selected' : '' }}>Ignored</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From</label>
                    <input type="date" name="event_from" class="form-control" value="{{ request('event_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To</label>
                    <input type="date" name="event_to" class="form-control" value="{{ request('event_to') }}">
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="?tab=events" class="btn btn-secondary">Reset</a>
                    <a href="{{ route('admin.notification-logs.export', ['type' => 'events']) }}" class="btn btn-outline-primary">Export</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Event ID</th>
                            <th>Type</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Payload</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paymentEvents as $event)
                            <tr class="{{ $event->status === 'failed' ? 'table-danger' : '' }}">
                                <td class="text-nowrap">{{ $event->created_at->format('d M Y H:i:s') }}</td>
                                <td><code>{{ \Illuminate\Support\Str::limit($event->event_id, 20) }}</code></td>
                                <td><span class="badge bg-secondary">{{ $event->event_type }}</span></td>
                                <td>
                                    @if($event->payment)
                                        {{ $event->payment->reference ?? '#' . $event->payment_id }}
                                    @else
                                        {{ $event->payment_id ? '#' . $event->payment_id : '-' }}
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $event->status === 'processed' ? 'success' : ($event->status === 'failed' ? 'danger' : 'warning') }}">
                                        {{ $event->status }}
                                    </span>
                                </td>
                                <td>
                                    @if($event->payload)
                                        <button class="btn btn-sm btn-outline-secondary" onclick="alert(JSON.stringify({{ json_encode($event->payload) }}, null, 2))">View</button>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No payment events found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @if($paymentEvents->hasPages())
        <div class="mt-3">{!! $paymentEvents->appends(request()->query())->links('pagination::simple-default') !!}</div>
    @endif
    @endif
</div>
@endsection
