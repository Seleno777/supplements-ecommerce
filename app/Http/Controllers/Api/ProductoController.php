<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::all(); // Obtengo todos los productos de la base
        return response()->json($productos); // Los devuelvo en formato JSON
    }
}
