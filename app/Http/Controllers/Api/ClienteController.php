<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class ClienteController extends Controller
{
    // Listado con búsqueda y paginación
    public function index(Request $request): JsonResponse
    {
        $query = Cliente::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('apellido', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $clientes = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $clientes,
        ]);
    }

    // Detalle cliente (carga carrito con inventario y producto)
    public function show(Cliente $cliente): JsonResponse
    {
        $cliente->load('carrito.inventario.producto');

        return response()->json([
            'success' => true,
            'data' => $cliente,
        ]);
    }

    // Perfil usuario autenticado
    public function profile(Request $request): JsonResponse
    {
        $cliente = $request->user();
        $cliente->load('carrito.inventario.producto');

        return response()->json([
            'success' => true,
            'data' => $cliente,
        ]);
    }

    // Actualizar perfil usuario autenticado
    public function updateProfile(Request $request): JsonResponse
    {
        $cliente = $request->user();

        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'email' => 'required|string|email|max:255|unique:clientes,email,' . $cliente->id,
        ]);

        $cliente->update($request->only(['nombre', 'apellido', 'telefono', 'email']));

        return response()->json([
            'success' => true,
            'message' => 'Perfil actualizado exitosamente',
            'data' => $cliente,
        ]);
    }

    // Actualizar contraseña usuario autenticado
    public function updatePassword(Request $request): JsonResponse
    {
        $cliente = $request->user();

        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $cliente->password)) {
            return response()->json([
                'success' => false,
                'message' => 'La contraseña actual es incorrecta',
            ], 400);
        }

        $cliente->update(['password' => Hash::make($request->password)]);

        return response()->json([
            'success' => true,
            'message' => 'Contraseña actualizada exitosamente',
        ]);
    }

    // Actualizar cliente (admin)
    public function update(Request $request, Cliente $cliente): JsonResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'email' => 'required|string|email|max:255|unique:clientes,email,' . $cliente->id,
        ]);

        $cliente->update($request->only(['nombre', 'apellido', 'telefono', 'email']));

        return response()->json([
            'success' => true,
            'message' => 'Cliente actualizado exitosamente',
            'data' => $cliente,
        ]);
    }

    // Eliminar cliente (admin)
    public function destroy(Cliente $cliente): JsonResponse
    {
        $cliente->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cliente eliminado exitosamente',
        ]);
    }
}
