<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('actor');

        if ($request->filled('entity')) {
            $query->where('entity', $request->entity);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50);

        return view('admin.audit-logs.index', compact('logs'));
    }
}
