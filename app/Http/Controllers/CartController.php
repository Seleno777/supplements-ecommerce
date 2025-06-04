<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class CartController extends Controller
{
    public function index()
    {
        return CartItem::with('product')
            ->where('user_id', Auth::id())
            ->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = CartItem::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
            ],
            [
                'quantity' => $request->quantity,
            ]
        );

        return $cartItem;
    }

    public function destroy($product_id)
    {
        CartItem::where('user_id', Auth::id())
            ->where('product_id', $product_id)
            ->delete();

        return response()->noContent();
    }
}
