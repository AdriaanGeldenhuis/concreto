<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class HealthController extends Controller
{
    public function __invoke()
    {
        $checks = [];

        // Database check
        try {
            DB::select('SELECT 1');
            $checks['database'] = 'ok';
        } catch (\Exception $e) {
            $checks['database'] = 'error: ' . $e->getMessage();
        }

        // Queue check (verify jobs table is accessible)
        try {
            DB::table('jobs')->count();
            $checks['queue'] = 'ok';
        } catch (\Exception $e) {
            $checks['queue'] = 'error: ' . $e->getMessage();
        }

        // Cache check
        try {
            Cache::put('health_check', true, 10);
            Cache::get('health_check');
            $checks['cache'] = 'ok';
        } catch (\Exception $e) {
            $checks['cache'] = 'error: ' . $e->getMessage();
        }

        // Storage check
        try {
            $testFile = 'health_check_' . time() . '.tmp';
            \Illuminate\Support\Facades\Storage::disk('local')->put($testFile, 'ok');
            \Illuminate\Support\Facades\Storage::disk('local')->delete($testFile);
            $checks['storage'] = 'ok';
        } catch (\Exception $e) {
            $checks['storage'] = 'error: ' . $e->getMessage();
        }

        $allOk = collect($checks)->every(fn($v) => $v === 'ok');

        return response()->json([
            'status' => $allOk ? 'healthy' : 'degraded',
            'checks' => $checks,
            'timestamp' => now()->toIso8601String(),
        ], $allOk ? 200 : 503);
    }
}
