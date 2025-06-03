<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InventarioController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Inventario::with(['producto.categoria', 'producto.subcategoria', 'producto.talla'])
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->has('producto_id')) {
            $query->where('producto_id', $request->producto_id);
        }

        if ($request->boolean('disponible', false)) {
            $query->disponible();
        }

        if ($request->boolean('no_vencido', false)) {
            $query->noVencido();
        }

        if ($request->has('precio_min')) {
            $query->where('precio', '>=', $request->precio_min);
        }

        if ($request->has('precio_max')) {
            $query->where('precio', '<=', $request->precio_max);
        }

        $inventario = $query->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $inventario
        ]);
    }

    public function show(Inventario $inventario): JsonResponse
    {
        $inventario->load(['producto.categoria', 'producto.subcategoria', 'producto.talla']);

        return response()->json([
            'success' => true,
            'data' => $inventario
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|numeric|min:0',
            'unidad' => 'required|string|max:50',
            'precio' => 'required|numeric|min:0',
            'fecha_vencimiento' => 'nullable|date|after:today',
        ]);

        $data = $request->only(['producto_id', 'cantidad', 'unidad', 'precio', 'fecha_vencimiento']);

        $inventario = Inventario::create($data);
        $inventario->load(['producto.categoria', 'producto.subcategoria', 'producto.talla']);

        return response()->json([
            'success' => true,
            'message' => 'Inventario creado exitosamente',
            'data' => $inventario
        ], 201);
    }

    public function update(Request $request, Inventario $inventario): JsonResponse
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|numeric|min:0',
            'unidad' => 'required|string|max:50',
            'precio' => 'required|numeric|min:0',
            'fecha_vencimiento' => 'nullable|date|after:today',
        ]);

        $data = $request->only(['producto_id', 'cantidad', 'unidad', 'precio', 'fecha_vencimiento']);

        $inventario->update($data);
        $inventario->load(['producto.categoria', 'producto.subcategoria', 'producto.talla']);

        return response()->json([
            'success' => true,
            'message' => 'Inventario actualizado exitosamente',
            'data' => $inventario
        ]);
    }

    public function destroy(Inventario $inventario): JsonResponse
    {
        $inventario->delete();

        return response()->json([
            'success' => true,
            'message' => 'Inventario eliminado exitosamente'
        ]);
    }

    public function updateStock(Request $request, Inventario $inventario): JsonResponse
    {
        $request->validate([
            'cantidad' => 'required|numeric|min:0',
            'operacion' => 'required|in:sumar,restar,establecer',
        ]);

        $cantidad = $request->cantidad;
        $operacion = $request->operacion;

        switch ($operacion) {
            case 'sumar':
                $inventario->cantidad += $cantidad;
                break;
            case 'restar':
                $inventario->cantidad = max(0, $inventario->cantidad - $cantidad);
                break;
            case 'establecer':
                $inventario->cantidad = $cantidad;
                break;
        }

        $inventario->save();

        return response()->json([
            'success' => true,
            'message' => 'Stock actualizado exitosamente',
            'data' => $inventario
        ]);
    }
}
