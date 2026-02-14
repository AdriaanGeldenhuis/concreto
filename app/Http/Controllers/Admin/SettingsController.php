<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->groupBy('group');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
        ]);

        $changed = [];
        foreach ($request->settings as $key => $value) {
            $group = $request->input("groups.{$key}", 'general');
            $old = Setting::get($key);
            Setting::set($key, $value, $group);
            if ($old !== $value) {
                $changed[$key] = ['from' => $old, 'to' => $value];
            }
        }

        if (!empty($changed)) {
            AuditLog::log('updated', 'Setting', null, $changed);
        }

        return back()->with('success', 'Settings saved.');
    }
}
