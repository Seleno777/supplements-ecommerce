<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoriaController extends Controller
{
    public function index(): JsonResponse
    {
        $categorias = Categoria::with(['subcategorias' => function ($query) {
            $query->activo();
        }])
        ->activo()
        ->orderBy('nombre')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $categorias
        ]);
    }

    public function show(Categoria $categoria): JsonResponse
    {
        $categoria->load([
            'subcategorias' => function ($query) {
                $query->activo();
            },
            'productos' => function ($query) {
                $query->activo()->with(['subcategoria', 'talla', 'inventario']);
            }
        ]);

        return response()->json([
            'success' => true,
            'data' => $categoria
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:categorias',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean'
        ]);

        $data = $request->only(['nombre', 'descripcion']);
        $data['activo'] = $request->has('activo') ? (bool)$request->activo : true;

        $categoria = Categoria::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Categoría creada exitosamente',
            'data' => $categoria
        ], 201);
    }

    public function update(Request $request, Categoria $categoria): JsonResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:categorias,nombre,' . $categoria->id,
            'descripcion' => 'nullable|string',
            'activo' => 'boolean'
        ]);

        $data = $request->only(['nombre', 'descripcion']);
        if ($request->has('activo')) {
            $data['activo'] = (bool)$request->activo;
        }

        $categoria->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Categoría actualizada exitosamente',
            'data' => $categoria
        ]);
    }

    public function destroy(Categoria $categoria): JsonResponse
    {
        if ($categoria->productos()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar la categoría porque tiene productos asociados'
            ], 400);
        }

        $categoria->delete();

        return response()->json([
            'success' => true,
            'message' => 'Categoría eliminada exitosamente'
        ]);
    }
}
