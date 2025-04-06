<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Category;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::where('user_id', Auth::id())->get();
        return Inertia::render('categories/index', [
            'categories' => $categories
        ]);
    }

    public function store(Request $request){
        $request->validate([
            'name'=> 'required|string|max:255',
        ]);

        Category::create([
            'name'=> $request->name,
            'user_id'=>auth()->id(),
        ]);

        return redirect()->back()->with('success','Category created successfully.');
    }
}
