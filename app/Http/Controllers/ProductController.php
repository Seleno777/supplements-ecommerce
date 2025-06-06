<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {
        return Product::with('user')->where('stock', '>', 0)->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category' => 'nullable|string',
        ]);

        return Product::create([
            ...$request->all(),
            'user_id' => Auth::id(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        //$this->authorize('update', $product);

        $product->update($request->only(['name', 'description', 'price', 'stock', 'category']));
        return $product;
    }

    public function edit(Product $product)
    {
        if ($product->user_id !== Auth::id()) {
            abort(403);
        }

        return Inertia::render('EditProduct', [
            'product' => $product
        ]);
    }

    public function destroy(Product $product)
    {
        if ($product->user_id !== Auth::id()) {
            abort(403);
        }

        $product->delete();
        return response()->noContent(); // status 204
    }

    public function myProducts()
    {
        $products = Product::where('user_id', Auth::id())->get();

        return Inertia::render('MyProducts', [
            'products' => $products
        ]);
    }
}
