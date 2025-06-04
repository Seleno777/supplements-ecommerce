<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        return Order::with('items.product')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();
    }

    public function checkout()
    {
        $user = Auth::user();

        $cartItems = CartItem::with('product')
            ->where('user_id', $user->id)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Tu carrito está vacío.'], 400);
        }

        DB::beginTransaction();

        try {
            $total = 0;

            foreach ($cartItems as $item) {
                if ($item->quantity > $item->product->stock) {
                    throw new \Exception("No hay suficiente stock para '{$item->product->name}'");
                }
                $total += $item->quantity * $item->product->price;
            }

            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $total,
                'status' => 'completed',
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);

                // Descontar stock
                $item->product->decrement('stock', $item->quantity);

                // Notificar si el producto se agotó
                if ($item->product->stock <= 0) {
                    $item->product->user->notifications()->create([
                        'message' => "Tu producto '{$item->product->name}' está agotado.",
                        'type' => 'warning',
                    ]);
                }
            }

            // Vaciar carrito
            CartItem::where('user_id', $user->id)->delete();

            DB::commit();
            return $order->load('items.product');
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
