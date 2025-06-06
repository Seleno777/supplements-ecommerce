<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;


class CartController extends Controller
{
    public function index()
    {
        $items = CartItem::with('product')
            ->where('user_id', Auth::id())
            ->get();

        return Inertia::render('Cart', [
            'cartItems' => $items
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        $cartItem = CartItem::firstOrNew([
            'user_id' => Auth::id(),
            'product_id' => $request->product_id,
        ]);

        // Nueva cantidad deseada (puede ser igual o distinta)
        $newQty = $request->quantity;

        // No dejar que exceda el stock
        if ($newQty > $product->stock) {
            return response()->json([
                'message' => 'No hay suficiente stock disponible.'
            ], 400);
        }

        $cartItem->quantity = $newQty;
        $cartItem->save();

        return $cartItem->load('product');
    }

    public function destroy($product_id)
    {
        CartItem::where('user_id', Auth::id())
            ->where('product_id', $product_id)
            ->delete();

        return response()->noContent();
    }
}
