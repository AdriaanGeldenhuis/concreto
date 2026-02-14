<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'settings' => 'nullable|array',
            'file_settings' => 'nullable|array',
            'file_settings.*' => 'nullable|image|max:5120',
            'remove_files' => 'nullable|array',
        ]);

        $changed = [];

        // Handle regular text/color settings
        if ($request->has('settings')) {
            foreach ($request->settings as $key => $value) {
                $group = $request->input("groups.{$key}", 'general');
                $old = Setting::get($key);
                Setting::set($key, $value, $group);
                if ($old !== $value) {
                    $changed[$key] = ['from' => $old, 'to' => $value];
                }
            }
        }

        // Handle file removals
        if ($request->has('remove_files')) {
            foreach ($request->remove_files as $key => $val) {
                $old = Setting::get($key);
                if ($old && Storage::disk('public')->exists($old)) {
                    Storage::disk('public')->delete($old);
                }
                $group = $request->input("groups.{$key}", 'branding');
                Setting::set($key, null, $group);
                $changed[$key] = ['from' => $old, 'to' => null];
            }
        }

        // Handle file uploads (logo, backgrounds)
        if ($request->hasAny(['file_settings'])) {
            foreach ($request->allFiles() as $fieldGroup) {
                if (!is_array($fieldGroup)) continue;
                foreach ($fieldGroup as $key => $file) {
                    $group = $request->input("groups.{$key}", 'branding');
                    $old = Setting::get($key);

                    // Delete old file if exists
                    if ($old && Storage::disk('public')->exists($old)) {
                        Storage::disk('public')->delete($old);
                    }

                    $path = $file->store('settings', 'public');
                    Setting::set($key, $path, $group);
                    $changed[$key] = ['from' => $old, 'to' => $path];
                }
            }
        }

        if (!empty($changed)) {
            AuditLog::log('updated', 'Setting', null, $changed);
        }

        return back()->with('success', 'Settings saved.');
    }
}
