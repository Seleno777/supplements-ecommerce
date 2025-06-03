<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Talla;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TallaController extends Controller
{
    public function index(): JsonResponse
    {
        $tallas = Talla::orderBy('nombre')->get();

        return response()->json([
            'success' => true,
            'data' => $tallas,
        ]);
    }

    public function show(Talla $talla): JsonResponse
    {
        $talla->load('productos');

        return response()->json([
            'success' => true,
            'data' => $talla,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:tallas,nombre',
            'descripcion' => 'nullable|string',
        ]);

        $data = $request->only(['nombre', 'descripcion']);

        $talla = Talla::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Talla creada exitosamente',
            'data' => $talla,
        ], 201);
    }

    public function update(Request $request, Talla $talla): JsonResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:tallas,nombre,' . $talla->id,
            'descripcion' => 'nullable|string',
        ]);

        $data = $request->only(['nombre', 'descripcion']);

        $talla->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Talla actualizada exitosamente',
            'data' => $talla,
        ]);
    }

    public function destroy(Talla $talla): JsonResponse
    {
        $talla->delete();

        return response()->json([
            'success' => true,
            'message' => 'Talla eliminada exitosamente',
        ]);
    }
}
