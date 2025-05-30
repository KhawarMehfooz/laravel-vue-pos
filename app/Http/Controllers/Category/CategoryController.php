<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Category;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CategoryController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $rows = $request->input("rows", 10);
        $page = $request->input("page", 1);
        $search = $request->input("search", "");

        $categories = Category::where('user_id', Auth::id())
            ->where('name', 'like', "%{$search}%")
            ->orderBy('name', 'asc')
            ->paginate($rows, ['*'], 'page', $page);

        return Inertia::render('categories/index', [
            'categories' => $categories->items(),
            'pagination' => [
                'total' => $categories->total(),
                'currentPage' => $categories->currentPage(),
                'lastPage' => $categories->lastPage(),
                'perPage' => $categories->perPage(),
            ],
            'search' => $request->input('search')
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Category::create([
            'name' => $request->name,
            'user_id' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Category created successfully.');
    }

    public function search(Request $request)
    {
        $search = $request->input('search', '');

        $categories = Auth::user()
            ->categories()
            ->where('name', 'like', "%{$search}%")
            ->select('id', 'name')
            ->orderByRaw('LOWER(name) ASC')
            ->limit(10)
            ->get();

        return response()->json($categories);
    }


    public function update(Request $request, Category $category)
    {
        $this->authorize('update', $category);
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $category->update(['name' => $request->name]);
        return redirect()->back()->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $this->authorize('delete', $category);
        $category->delete();
        return redirect()->back()->with('success', 'Category deleted successfully.');
    }
}
