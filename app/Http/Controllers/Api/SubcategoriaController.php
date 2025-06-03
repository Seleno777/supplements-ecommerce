<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subcategoria;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SubcategoriaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Subcategoria::with('categoria')
            ->activo()
            ->orderBy('nombre');

        if ($request->has('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        $subcategorias = $query->get();

        return response()->json([
            'success' => true,
            'data' => $subcategorias
        ]);
    }

    public function show(Subcategoria $subcategoria): JsonResponse
    {
        $subcategoria->load(['categoria', 'productos']);

        return response()->json([
            'success' => true,
            'data' => $subcategoria
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'categoria_id' => 'required|exists:categorias,id',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean'
        ]);

        $data = $request->only(['categoria_id', 'nombre', 'descripcion']);
        $data['activo'] = $request->has('activo') ? (bool) $request->activo : true;

        $subcategoria = Subcategoria::create($data);
        $subcategoria->load('categoria');

        return response()->json([
            'success' => true,
            'message' => 'Subcategoría creada exitosamente',
            'data' => $subcategoria
        ], 201);
    }

    public function update(Request $request, Subcategoria $subcategoria): JsonResponse
    {
        $request->validate([
            'categoria_id' => 'required|exists:categorias,id',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean'
        ]);

        $data = $request->only(['categoria_id', 'nombre', 'descripcion']);
        if ($request->has('activo')) {
            $data['activo'] = (bool) $request->activo;
        }

        $subcategoria->update($data);
        $subcategoria->load('categoria');

        return response()->json([
            'success' => true,
            'message' => 'Subcategoría actualizada exitosamente',
            'data' => $subcategoria
        ]);
    }

    public function destroy(Subcategoria $subcategoria): JsonResponse
    {
        $subcategoria->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subcategoría eliminada exitosamente'
        ]);
    }
}
