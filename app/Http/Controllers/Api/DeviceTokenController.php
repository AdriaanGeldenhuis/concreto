<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required|string|max:500',
            'platform' => 'required|in:android,ios,web',
        ]);

        $deviceToken = DeviceToken::updateOrCreate(
            ['token' => $request->token],
            [
                'user_id' => $request->user()->id,
                'platform' => $request->platform,
                'is_active' => true,
                'last_used_at' => now(),
            ]
        );

        return response()->json([
            'status' => 'registered',
            'id' => $deviceToken->id,
        ]);
    }

    public function destroy(Request $request, string $token)
    {
        DeviceToken::where('token', $token)
            ->where('user_id', $request->user()->id)
            ->update(['is_active' => false]);

        return response()->json(['status' => 'deregistered']);
    }
}
