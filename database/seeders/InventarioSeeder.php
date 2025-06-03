<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inventario;
use Carbon\Carbon;

class InventarioSeeder extends Seeder
{
    public function run(): void
    {
        $inventarios = [
            // Producto 1 - Gold Standard Whey
            [
                'producto_id' => 1, 'cantidad' => 25.00, 'unidad' => 'unidades',
                'precio' => 89.99, 'fecha_vencimiento' => Carbon::now()->addMonths(18)
            ],
            // Producto 2 - Nitro Tech Whey
            [
                'producto_id' => 2, 'cantidad' => 15.00, 'unidad' => 'unidades',
                'precio' => 65.99, 'fecha_vencimiento' => Carbon::now()->addMonths(16)
            ],
            // Producto 3 - ISO 100
            [
                'producto_id' => 3, 'cantidad' => 10.00, 'unidad' => 'unidades',
                'precio' => 45.99, 'fecha_vencimiento' => Carbon::now()->addMonths(20)
            ],
            // Producto 4 - Creatine Monohydrate
            [
                'producto_id' => 4, 'cantidad' => 30.00, 'unidad' => 'unidades',
                'precio' => 25.99, 'fecha_vencimiento' => Carbon::now()->addMonths(24)
            ],
            // Producto 5 - Con-Cret HCl
            [
                'producto_id' => 5, 'cantidad' => 20.00, 'unidad' => 'unidades',
                'precio' => 35.99, 'fecha_vencimiento' => Carbon::now()->addMonths(22)
            ],
            // Producto 6 - C4 Original
            [
                'producto_id' => 6, 'cantidad' => 18.00, 'unidad' => 'unidades',
                'precio' => 32.99, 'fecha_vencimiento' => Carbon::now()->addMonths(15)
            ],
            // Producto 7 - Pump Serum
            [
                'producto_id' => 7, 'cantidad' => 12.00, 'unidad' => 'unidades',
                'precio' => 42.99, 'fecha_vencimiento' => Carbon::now()->addMonths(18)
            ],
            // Producto 8 - Opti-Men
            [
                'producto_id' => 8, 'cantidad' => 22.00, 'unidad' => 'unidades',
                'precio' => 28.99, 'fecha_vencimiento' => Carbon::now()->addMonths(30)
            ],
            // Producto 9 - Vitamina D3
            [
                'producto_id' => 9, 'cantidad' => 35.00, 'unidad' => 'unidades',
                'precio' => 18.99, 'fecha_vencimiento' => Carbon::now()->addMonths(36)
            ],
            // Producto 10 - Hydroxycut
            [
                'producto_id' => 10, 'cantidad' => 8.00, 'unidad' => 'unidades',
                'precio' => 38.99, 'fecha_vencimiento' => Carbon::now()->addMonths(14)
            ],
            // Producto 11 - L-Carnitine
            [
                'producto_id' => 11, 'cantidad' => 16.00, 'unidad' => 'unidades',
                'precio' => 24.99, 'fecha_vencimiento' => Carbon::now()->addMonths(12)
            ],
            // Producto 12 - BCAA
            [
                'producto_id' => 12, 'cantidad' => 28.00, 'unidad' => 'unidades',
                'precio' => 31.99, 'fecha_vencimiento' => Carbon::now()->addMonths(20)
            ],
            // Producto 13 - EAA Max
            [
                'producto_id' => 13, 'cantidad' => 14.00, 'unidad' => 'unidades',
                'precio' => 39.99, 'fecha_vencimiento' => Carbon::now()->addMonths(18)
            ]
        ];

        foreach ($inventarios as $inventario) {
            Inventario::create($inventario);
        }
    }
}