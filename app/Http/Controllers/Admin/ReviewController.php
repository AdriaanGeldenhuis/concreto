<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductReview::with(['product', 'customer.user']);

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $query->whereNull('is_approved');
            } else {
                $query->where('is_approved', $request->status === 'approved');
            }
        }

        // Search
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('comment', 'like', "%{$s}%")
                  ->orWhereHas('product', fn($q2) => $q2->where('name', 'like', "%{$s}%"))
                  ->orWhereHas('customer.user', fn($q2) => $q2->where('name', 'like', "%{$s}%"));
            });
        }

        // Rating filter
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        // Has reply filter
        if ($request->filled('replied')) {
            if ($request->replied === 'yes') {
                $query->whereNotNull('admin_reply');
            } else {
                $query->whereNull('admin_reply');
            }
        }

        $reviews = $query->orderBy('created_at', 'desc')->paginate(20)->appends($request->query());

        // Analytics
        $stats = [
            'total' => ProductReview::count(),
            'pending' => ProductReview::whereNull('is_approved')->count(),
            'approved' => ProductReview::where('is_approved', true)->count(),
            'rejected' => ProductReview::where('is_approved', false)->count(),
            'avg_rating' => round(ProductReview::avg('rating') ?? 0, 1),
            'with_reply' => ProductReview::whereNotNull('admin_reply')->count(),
        ];

        // Rating distribution
        $ratingDist = ProductReview::select('rating', DB::raw('COUNT(*) as count'))
            ->groupBy('rating')
            ->orderBy('rating', 'desc')
            ->pluck('count', 'rating')
            ->toArray();

        // Fill missing ratings
        for ($i = 5; $i >= 1; $i--) {
            if (!isset($ratingDist[$i])) $ratingDist[$i] = 0;
        }
        krsort($ratingDist);

        return view('admin.reviews.index', compact('reviews', 'stats', 'ratingDist'));
    }

    public function approve(ProductReview $review)
    {
        $review->update(['is_approved' => true]);
        AuditLog::log('approved_review', 'ProductReview', $review->id);
        return back()->with('success', 'Review approved.');
    }

    public function reject(ProductReview $review)
    {
        $review->update(['is_approved' => false]);
        AuditLog::log('rejected_review', 'ProductReview', $review->id);
        return back()->with('success', 'Review rejected.');
    }

    public function reply(Request $request, ProductReview $review)
    {
        $request->validate(['admin_reply' => 'required|string|max:1000']);
        $review->update([
            'admin_reply' => $request->admin_reply,
            'admin_reply_at' => now(),
        ]);
        AuditLog::log('replied_review', 'ProductReview', $review->id);
        return back()->with('success', 'Reply saved.');
    }

    public function bulkApprove(Request $request)
    {
        $request->validate(['review_ids' => 'required|array', 'review_ids.*' => 'exists:product_reviews,id']);
        ProductReview::whereIn('id', $request->review_ids)->update(['is_approved' => true]);
        AuditLog::log('bulk_approved_reviews', 'ProductReview', null, ['count' => count($request->review_ids)]);
        return back()->with('success', count($request->review_ids) . ' reviews approved.');
    }

    public function destroy(ProductReview $review)
    {
        AuditLog::log('deleted_review', 'ProductReview', $review->id);
        $review->delete();
        return back()->with('success', 'Review deleted.');
    }
}
