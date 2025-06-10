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

        $existingItem = CartItem::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->first();

        // Caso: añadir desde /products → solo permitir si NO existe aún
        if (!$request->expectsJson() && $existingItem) {
            return response()->json([
                'message' => 'Este producto ya está en el carrito.'
            ], 409);
        }

        // Si existe, se actualiza la cantidad
        if ($existingItem) {
            $newQty = $request->quantity;

            if ($newQty > $product->stock) {
                return response()->json([
                    'message' => 'No hay suficiente stock disponible.'
                ], 400);
            }

            $existingItem->quantity = $newQty;
            $existingItem->save();

            return $existingItem->load('product');
        }

        // Crear nuevo si no existe
        if ($request->quantity > $product->stock) {
            return response()->json([
                'message' => 'No hay suficiente stock disponible.'
            ], 400);
        }

        $cartItem = CartItem::create([
            'user_id' => Auth::id(),
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
        ]);

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
