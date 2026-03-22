<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\NotificationLog;
use App\Models\PaymentEvent;
use App\Helpers\CsvHelper;
use Illuminate\Http\Request;

class NotificationLogController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'notifications');

        // Notification logs
        $notifQuery = NotificationLog::query()->orderBy('created_at', 'desc');
        if ($request->filled('notif_status')) {
            $notifQuery->where('status', $request->notif_status);
        }
        if ($request->filled('notif_search')) {
            $search = $request->notif_search;
            $notifQuery->where(function ($q) use ($search) {
                $q->where('recipient', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('template_key', 'like', "%{$search}%");
            });
        }
        if ($request->filled('notif_from')) {
            $notifQuery->whereDate('created_at', '>=', $request->notif_from);
        }
        if ($request->filled('notif_to')) {
            $notifQuery->whereDate('created_at', '<=', $request->notif_to);
        }
        $notifications = $notifQuery->paginate(30, ['*'], 'notif_page');

        // Email logs
        $emailQuery = EmailLog::query()->orderBy('created_at', 'desc');
        if ($request->filled('email_status')) {
            $emailQuery->where('status', $request->email_status);
        }
        if ($request->filled('email_search')) {
            $search = $request->email_search;
            $emailQuery->where(function ($q) use ($search) {
                $q->where('to_email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }
        if ($request->filled('email_from')) {
            $emailQuery->whereDate('created_at', '>=', $request->email_from);
        }
        if ($request->filled('email_to')) {
            $emailQuery->whereDate('created_at', '<=', $request->email_to);
        }
        $emails = $emailQuery->paginate(30, ['*'], 'email_page');

        // Payment events
        $eventQuery = PaymentEvent::with('payment')->orderBy('created_at', 'desc');
        if ($request->filled('event_type')) {
            $eventQuery->where('event_type', $request->event_type);
        }
        if ($request->filled('event_status')) {
            $eventQuery->where('status', $request->event_status);
        }
        if ($request->filled('event_from')) {
            $eventQuery->whereDate('created_at', '>=', $request->event_from);
        }
        if ($request->filled('event_to')) {
            $eventQuery->whereDate('created_at', '<=', $request->event_to);
        }
        $paymentEvents = $eventQuery->paginate(30, ['*'], 'event_page');

        // Stats
        $stats = [
            'notif_total' => NotificationLog::count(),
            'notif_sent' => NotificationLog::where('status', 'sent')->count(),
            'notif_failed' => NotificationLog::where('status', 'failed')->count(),
            'email_total' => EmailLog::count(),
            'email_sent' => EmailLog::where('status', 'sent')->count(),
            'email_failed' => EmailLog::where('status', 'failed')->count(),
            'events_total' => PaymentEvent::count(),
            'events_processed' => PaymentEvent::where('status', 'processed')->count(),
        ];

        return view('admin.notification-logs.index', compact(
            'tab', 'notifications', 'emails', 'paymentEvents', 'stats'
        ));
    }

    public function export(Request $request)
    {
        $type = $request->input('type', 'notifications');
        $filename = "{$type}-log-" . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($type, $request) {
            $handle = fopen('php://output', 'w');
            CsvHelper::writeBom($handle);

            if ($type === 'notifications') {
                fputcsv($handle, ['Date', 'Channel', 'Recipient', 'Subject', 'Template', 'Related', 'Status', 'Error', 'Attempts']);
                NotificationLog::orderBy('created_at', 'desc')->chunk(200, function ($logs) use ($handle) {
                    foreach ($logs as $log) {
                        CsvHelper::safePutCsv($handle, [
                            $log->created_at->format('Y-m-d H:i:s'),
                            $log->channel,
                            $log->recipient,
                            $log->subject,
                            $log->template_key,
                            $log->related_type . '#' . $log->related_id,
                            $log->status,
                            $log->error_message ?? '-',
                            $log->attempts,
                        ]);
                    }
                });
            } elseif ($type === 'emails') {
                fputcsv($handle, ['Date', 'To', 'Subject', 'Template', 'Related', 'Status', 'Error']);
                EmailLog::orderBy('created_at', 'desc')->chunk(200, function ($logs) use ($handle) {
                    foreach ($logs as $log) {
                        CsvHelper::safePutCsv($handle, [
                            $log->created_at->format('Y-m-d H:i:s'),
                            $log->to_email,
                            $log->subject,
                            $log->template,
                            $log->related_type . '#' . $log->related_id,
                            $log->status,
                            $log->error_message ?? '-',
                        ]);
                    }
                });
            } else {
                fputcsv($handle, ['Date', 'Event ID', 'Type', 'Payment ID', 'Status']);
                PaymentEvent::orderBy('created_at', 'desc')->chunk(200, function ($events) use ($handle) {
                    foreach ($events as $event) {
                        CsvHelper::safePutCsv($handle, [
                            $event->created_at->format('Y-m-d H:i:s'),
                            $event->event_id,
                            $event->event_type,
                            $event->payment_id ?? '-',
                            $event->status,
                        ]);
                    }
                });
            }

            fclose($handle);
        }, 200, $headers);
    }
}
