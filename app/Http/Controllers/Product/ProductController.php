<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProductController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $rows = $request->input("rows", 10);
        $page = $request->input("page", 1);
        $search = $request->input("search", "");

        $products = Auth::user()
            ->products()
            ->with([
                'category:id,name',
                'company:id,name'
            ])
            ->where('name', 'like', "%{$search}%")
            ->orderByRaw('LOWER(name) ASC')
            ->paginate($rows, ['*'], 'page', $page);

        return Inertia::render('product/index', [
            'products' => $products->items(),
            'pagination' => [
                'total' => $products->total(),
                'currentPage' => $products->currentPage(),
                'lastPage' => $products->lastPage(),
                'perPage' => $products->perPage(),
            ],
            'search' => $request->input('search'),
            'categories' => Category::where('user_id', Auth::id())
                ->select('id', 'name')
                ->orderByRaw('LOWER(name) ASC')
                ->limit(10)
                ->get(),
            'companies' => Company::where('user_id', Auth::id())
                ->select('id', 'name')
                ->orderByRaw('LOWER(name) ASC')
                ->limit(10)
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:30',
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where('user_id', Auth::id()),
                'integer'
            ],
            'company_id' => [
                'required',
                Rule::exists('companies', 'id')->where('user_id', Auth::id()),
                'integer'
            ],
            'barcode' => [
                'required',
                'numeric',
                Rule::unique('products')->where('user_id', Auth::id()),
            ],
            'purchase_price' => 'required|numeric|min:0|max:999999.99',
            'retail_price' => 'required|numeric|min:0|max:999999.99|gte:purchase_price',
        ]);

        $product = Auth::user()->products()->create($validated);

        return redirect()->back()->with('success', 'Product created successfully.');
    }

    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'name' => 'required|string|max:30',
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where('user_id', Auth::id()),
            ],
            'company_id' => [
                'required',
                'integer',
                Rule::exists('companies', 'id')->where('user_id', Auth::id()),
            ],
            'shelf_number' => 'required|string|max:8',
            'barcode' => [
                'required',
                'string',
                Rule::unique('products')
                    ->where('user_id', Auth::id())
                    ->ignore($product->id),
            ],
            'purchase_price' => 'required|numeric|min:0|max:999999.99',
            'retail_price' => 'required|numeric|min:0|max:999999.99|gte:purchase_price',
        ]);

        $product->update($validated);

        return redirect()->back()->with('success', 'Product updated successfully.');
    }
    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);
        $product->delete();
        return redirect()->back()->with('success', 'Product deleted successfully.');
    }
}
