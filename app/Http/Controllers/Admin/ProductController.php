<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BulkPricingTier;
use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'vendor']);

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        // Stock filter
        if ($stock = $request->input('stock')) {
            match ($stock) {
                'in_stock' => $query->where('in_stock', true)->where(fn($q) => $q->whereNull('stock_qty')->orWhere('stock_qty', '>', 0)),
                'low_stock' => $query->whereNotNull('stock_qty')->whereNotNull('low_stock_threshold')->whereColumn('stock_qty', '<=', 'low_stock_threshold')->where('stock_qty', '>', 0),
                'out_of_stock' => $query->where(fn($q) => $q->where('in_stock', false)->orWhere(fn($q2) => $q2->whereNotNull('stock_qty')->where('stock_qty', '<=', 0))),
                default => null,
            };
        }

        // Active filter
        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Sorting
        $sort = $request->input('sort', 'name');
        $dir = $request->input('dir', 'asc');
        $allowedSorts = ['name', 'price', 'stock_qty', 'created_at', 'sku'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $dir === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('name');
        }

        $products = $query->paginate(20)->withQueryString();
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function show(Product $product)
    {
        $product->load(['category', 'vendor', 'bulkPricingTiers', 'reviews.customer.user', 'reviews.order']);

        // Sales stats
        $salesStats = \App\Models\OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.product_id', $product->id)
            ->where('orders.status', 'DELIVERED')
            ->selectRaw('COUNT(DISTINCT orders.id) as order_count, SUM(order_items.qty) as total_qty, SUM(order_items.line_total) as total_revenue')
            ->first();

        return view('admin.products.show', compact('product', 'salesStats'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        return view('admin.products.form', compact('categories', 'vendors'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'category_id' => 'nullable|exists:categories,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'unit' => 'required|string|max:20',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'in_stock' => 'boolean',
            'stock_qty' => 'nullable|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:5120|mimes:jpg,jpeg,png,webp',
        ]);

        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['is_active'] = $request->boolean('is_active');
        $data['in_stock'] = $request->boolean('in_stock');

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        unset($data['image']);
        $product = Product::create($data);

        $this->saveBulkTiers($product, $request->input('tiers', []));

        AuditLog::log('created', 'Product', $product->id);

        return redirect()->route('admin.products.index')->with('success', 'Product created.');
    }

    public function edit(Product $product)
    {
        $product->load('bulkPricingTiers');
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        return view('admin.products.form', compact('product', 'categories', 'vendors'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100|unique:products,sku,' . $product->id,
            'category_id' => 'nullable|exists:categories,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'unit' => 'required|string|max:20',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'in_stock' => 'boolean',
            'stock_qty' => 'nullable|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:5120|mimes:jpg,jpeg,png,webp',
        ]);

        $data['slug'] = $this->uniqueSlug($data['name'], $product->id);
        $data['is_active'] = $request->boolean('is_active');
        $data['in_stock'] = $request->boolean('in_stock');

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        unset($data['image']);
        $product->update($data);

        $this->saveBulkTiers($product, $request->input('tiers', []));

        AuditLog::log('updated', 'Product', $product->id);

        return redirect()->route('admin.products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        AuditLog::log('deleted', 'Product', $product->id, ['name' => $product->name]);
        $product->delete();
        return back()->with('success', 'Product deleted.');
    }

    public function export(Request $request): StreamedResponse
    {
        $query = Product::with('category');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->orderBy('name')->get();

        return response()->streamDownload(function () use ($products) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['SKU', 'Name', 'Category', 'Price', 'Cost Price', 'Unit', 'Stock Qty', 'In Stock', 'Active']);
            foreach ($products as $p) {
                fputcsv($out, [
                    $p->sku ?? '',
                    $p->name,
                    $p->category?->name ?? '',
                    $p->price,
                    $p->cost_price ?? '',
                    $p->unit,
                    $p->stock_qty ?? '',
                    $p->in_stock ? 'Yes' : 'No',
                    $p->is_active ? 'Yes' : 'No',
                ]);
            }
            fclose($out);
        }, 'products-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function saveBulkTiers(Product $product, array $tiers): void
    {
        $product->bulkPricingTiers()->delete();

        foreach ($tiers as $tier) {
            if (empty($tier['min_qty']) || empty($tier['price'])) continue;

            BulkPricingTier::create([
                'product_id' => $product->id,
                'min_qty' => $tier['min_qty'],
                'max_qty' => !empty($tier['max_qty']) ? $tier['max_qty'] : null,
                'price' => $tier['price'],
            ]);
        }
    }

    private function uniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $i = 1;

        while (Product::where('slug', $slug)->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))->exists()) {
            $slug = $original . '-' . $i++;
        }

        return $slug;
    }
}
