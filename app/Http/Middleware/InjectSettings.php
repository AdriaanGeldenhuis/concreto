<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class InjectSettings
{
    public function handle(Request $request, Closure $next): Response
    {
        $settings = Setting::getAll();
        View::share('siteSettings', $settings);
        return $next($request);
    }
}
