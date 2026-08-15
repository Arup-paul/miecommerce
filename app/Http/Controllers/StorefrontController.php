<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

class StorefrontController extends Controller
{
    public function index()
    {
        $products = Product::where('status', 'active')->paginate(12);

        return view('storefront.index', compact('products'));
    }

    public function category(Category $category)
    {
        if ($category->status !== 'active') {
            abort(404);
        }

        $products = $category->products()->where('status', 'active')->paginate(12);

        return view('storefront.category', compact('category', 'products'));
    }

    public function show(Product $product)
    {
        return view('storefront.show', compact('product'));
    }
}
