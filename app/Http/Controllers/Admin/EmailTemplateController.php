<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class EmailTemplateController extends Controller
{
    private array $templates = [
        'emails.invoice' => 'Invoice Email',
        'emails.order-placed' => 'Order Placed Confirmation',
        'emails.payment-received' => 'Payment Received',
        'emails.order-loaded' => 'Order Loaded',
        'emails.order-en-route' => 'Order En Route',
        'emails.order-delivered' => 'Order Delivered',
        'emails.invoice-reminder' => 'Invoice Reminder',
    ];

    public function index()
    {
        $templates = [];
        foreach ($this->templates as $key => $label) {
            $path = resource_path('views/' . str_replace('.', '/', $key) . '.blade.php');
            $templates[] = [
                'key' => $key,
                'label' => $label,
                'exists' => File::exists($path),
                'path' => $path,
            ];
        }
        return view('admin.email-templates.index', compact('templates'));
    }

    public function edit(Request $request)
    {
        $key = $request->input('template');
        if (!isset($this->templates[$key])) abort(404);

        $path = resource_path('views/' . str_replace('.', '/', $key) . '.blade.php');
        $content = File::exists($path) ? File::get($path) : '';
        $label = $this->templates[$key];

        return view('admin.email-templates.edit', compact('key', 'label', 'content'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'template' => 'required|string',
            'content' => 'required|string',
        ]);

        $key = $request->input('template');
        if (!isset($this->templates[$key])) abort(404);

        $path = resource_path('views/' . str_replace('.', '/', $key) . '.blade.php');

        // Backup old version
        if (File::exists($path)) {
            $backupPath = $path . '.bak.' . now()->format('YmdHis');
            File::copy($path, $backupPath);
        }

        File::put($path, $request->input('content'));
        AuditLog::log('updated', 'EmailTemplate', null, ['template' => $key]);

        return redirect()->route('admin.email-templates.index')
            ->with('success', "Template '{$this->templates[$key]}' updated.");
    }
}
