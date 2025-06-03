<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Carrito;
use App\Models\Inventario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CarritoController extends Controller
{
    // Listar items del carrito del usuario autenticado
    public function index(Request $request): JsonResponse
    {
        $carrito = Carrito::with([
            'inventario.producto.categoria', 
            'inventario.producto.subcategoria', 
            'inventario.producto.talla'
        ])
        ->where('cliente_id', $request->user()->id)
        ->get();

        $total = $carrito->sum('subtotal');
        $cantidadItems = $carrito->sum('cantidad');

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $carrito,
                'total' => $total,
                'cantidad_items' => $cantidadItems,
            ]
        ]);
    }

    // Agregar producto al carrito
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'inventario_id' => 'required|exists:inventario,id',
            'cantidad' => 'required|integer|min:1',
        ]);

        $inventario = Inventario::findOrFail($request->inventario_id);

        if ($inventario->cantidad < $request->cantidad) {
            return response()->json([
                'success' => false,
                'message' => 'Cantidad insuficiente en inventario',
            ], 400);
        }

        $carritoExistente = Carrito::where('cliente_id', $request->user()->id)
            ->where('inventario_id', $request->inventario_id)
            ->first();

        if ($carritoExistente) {
            $nuevaCantidad = $carritoExistente->cantidad + $request->cantidad;

            if ($inventario->cantidad < $nuevaCantidad) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cantidad insuficiente en inventario',
                ], 400);
            }

            $carritoExistente->update([
                'cantidad' => $nuevaCantidad,
            ]);

            $carrito = $carritoExistente;
        } else {
            $carrito = Carrito::create([
                'cliente_id' => $request->user()->id,
                'inventario_id' => $request->inventario_id,
                'precio' => $inventario->precio,
                'cantidad' => $request->cantidad,
            ]);
        }

        $carrito->load(['inventario.producto.categoria', 'inventario.producto.subcategoria', 'inventario.producto.talla']);

        return response()->json([
            'success' => true,
            'message' => 'Producto agregado al carrito',
            'data' => $carrito,
        ], 201);
    }

    // Actualizar cantidad de producto en carrito
    public function update(Request $request, Carrito $carrito): JsonResponse
    {
        if ($carrito->cliente_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado',
            ], 403);
        }

        $request->validate([
            'cantidad' => 'required|integer|min:1',
        ]);

        if ($carrito->inventario->cantidad < $request->cantidad) {
            return response()->json([
                'success' => false,
                'message' => 'Cantidad insuficiente en inventario',
            ], 400);
        }

        $carrito->update([
            'cantidad' => $request->cantidad,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Carrito actualizado exitosamente',
            'data' => $carrito,
        ]);
    }

    // Eliminar producto del carrito
    public function destroy(Request $request, Carrito $carrito): JsonResponse
    {
        if ($carrito->cliente_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado',
            ], 403);
        }

        $carrito->delete();

        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado del carrito',
        ]);
    }

    // Vaciar todo el carrito
    public function clear(Request $request): JsonResponse
    {
        Carrito::where('cliente_id', $request->user()->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Carrito vaciado exitosamente',
        ]);
    }

    // Obtener total y cantidad de items del carrito
    public function total(Request $request): JsonResponse
    {
        $carrito = Carrito::where('cliente_id', $request->user()->id)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $carrito->sum('subtotal'),
                'cantidad_items' => $carrito->sum('cantidad'),
            ],
        ]);
    }
}
