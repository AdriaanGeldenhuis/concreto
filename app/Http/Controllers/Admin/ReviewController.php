<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ProductReview;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductReview::with(['product', 'customer.user']);

        if ($request->filled('status')) {
            $query->where('is_approved', $request->status === 'approved');
        }

        $reviews = $query->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.reviews.index', compact('reviews'));
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

    public function destroy(ProductReview $review)
    {
        AuditLog::log('deleted_review', 'ProductReview', $review->id);
        $review->delete();
        return back()->with('success', 'Review deleted.');
    }
}
