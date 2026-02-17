<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PromoCode;
use App\Models\PromoCodeUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PromoCodeController extends Controller
{
    public function index()
    {
        $promoCodes = PromoCode::withCount('usage')
            ->withSum('usage', 'discount_applied')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Analytics
        $stats = [
            'total_codes' => PromoCode::count(),
            'active_codes' => PromoCode::where('is_active', true)->count(),
            'total_uses' => PromoCodeUsage::count(),
            'total_discount' => PromoCodeUsage::sum('discount_applied'),
            'avg_discount' => round(PromoCodeUsage::avg('discount_applied') ?? 0, 2),
        ];

        // Top 5 most used codes
        $topCodes = PromoCode::withCount('usage')
            ->withSum('usage', 'discount_applied')
            ->having('usage_count', '>', 0)
            ->orderByDesc('usage_count')
            ->take(5)
            ->get();

        // Monthly usage trend (last 6 months)
        $monthlyUsage = PromoCodeUsage::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as uses'),
                DB::raw('SUM(discount_applied) as total_discount')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('admin.promo-codes.index', compact('promoCodes', 'stats', 'topCodes', 'monthlyUsage'));
    }

    public function show(PromoCode $promoCode)
    {
        $promoCode->loadCount('usage');
        $usage = PromoCodeUsage::with(['customer.user', 'order'])
            ->where('promo_code_id', $promoCode->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $usageStats = [
            'total_uses' => $promoCode->usage()->count(),
            'total_discount' => $promoCode->usage()->sum('discount_applied'),
            'unique_customers' => $promoCode->usage()->distinct('customer_id')->count('customer_id'),
            'avg_discount' => round($promoCode->usage()->avg('discount_applied') ?? 0, 2),
        ];

        return view('admin.promo-codes.show', compact('promoCode', 'usage', 'usageStats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:promo_codes,code',
            'description' => 'nullable|string|max:255',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0.01',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'max_uses_per_customer' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'boolean',
        ]);

        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = $request->boolean('is_active', true);

        $promo = PromoCode::create($data);
        AuditLog::log('created', 'PromoCode', $promo->id);

        return back()->with('success', 'Promo code created.');
    }

    public function update(Request $request, PromoCode $promoCode)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:promo_codes,code,' . $promoCode->id,
            'description' => 'nullable|string|max:255',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0.01',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'max_uses_per_customer' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'boolean',
        ]);

        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = $request->boolean('is_active', true);

        $promoCode->update($data);
        AuditLog::log('updated', 'PromoCode', $promoCode->id);

        return back()->with('success', 'Promo code updated.');
    }

    public function destroy(PromoCode $promoCode)
    {
        AuditLog::log('deleted', 'PromoCode', $promoCode->id, ['code' => $promoCode->code]);
        $promoCode->delete();
        return back()->with('success', 'Promo code deleted.');
    }
}
