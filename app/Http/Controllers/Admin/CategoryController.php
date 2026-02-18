<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('sort_order')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
            'image' => 'nullable|image|max:5120|mimes:jpg,jpeg,png,webp',
        ]);

        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('categories', 'public');
        }
        unset($data['image']);

        $category = Category::create($data);
        AuditLog::log('created', 'Category', $category->id);

        return back()->with('success', 'Category created.');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
            'image' => 'nullable|image|max:5120|mimes:jpg,jpeg,png,webp',
        ]);

        $data['slug'] = $this->uniqueSlug($data['name'], $category->id);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('categories', 'public');
        }
        unset($data['image']);

        $category->update($data);
        AuditLog::log('updated', 'Category', $category->id);

        return back()->with('success', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        AuditLog::log('deleted', 'Category', $category->id, ['name' => $category->name]);
        $category->delete();
        return back()->with('success', 'Category deleted.');
    }

    private function uniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $i = 1;

        while (Category::where('slug', $slug)->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))->exists()) {
            $slug = $original . '-' . $i++;
        }

        return $slug;
    }
}
