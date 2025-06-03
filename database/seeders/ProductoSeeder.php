<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $productos = [
            // Proteínas Whey
            [
                'categoria_id' => 1, 'subcategoria_id' => 1, 'talla_id' => 3,
                'nombre' => 'Gold Standard Whey', 'descripcion' => 'Proteína premium con 24g por servicio',
                'marca' => 'Optimum Nutrition', 'activo' => true
            ],
            [
                'categoria_id' => 1, 'subcategoria_id' => 1, 'talla_id' => 2,
                'nombre' => 'Nitro Tech Whey', 'descripcion' => 'Whey protein con creatina y aminoácidos',
                'marca' => 'MuscleTech', 'activo' => true
            ],
            [
                'categoria_id' => 1, 'subcategoria_id' => 4, 'talla_id' => 1,
                'nombre' => 'ISO 100 Hydrolyzed', 'descripcion' => 'Proteína hidrolizada de rápida absorción',
                'marca' => 'Dymatize', 'activo' => true
            ],
            
            // Creatinas
            [
                'categoria_id' => 2, 'subcategoria_id' => 5, 'talla_id' => 5,
                'nombre' => 'Creatine Monohydrate', 'descripcion' => 'Creatina pura micronizada',
                'marca' => 'Optimum Nutrition', 'activo' => true
            ],
            [
                'categoria_id' => 2, 'subcategoria_id' => 6, 'talla_id' => 6,
                'nombre' => 'Con-Cret HCl', 'descripcion' => 'Creatina HCl concentrada',
                'marca' => 'ProMera Sports', 'activo' => true
            ],
            
            // Pre-Entrenos
            [
                'categoria_id' => 3, 'subcategoria_id' => 8, 'talla_id' => 12,
                'nombre' => 'C4 Original', 'descripcion' => 'Pre-entreno explosivo con beta-alanina',
                'marca' => 'Cellucor', 'activo' => true
            ],
            [
                'categoria_id' => 3, 'subcategoria_id' => 9, 'talla_id' => 13,
                'nombre' => 'Pump Serum', 'descripcion' => 'Pre-entreno sin estimulantes para pump',
                'marca' => 'Huge Supplements', 'activo' => true
            ],
            
            // Vitaminas
            [
                'categoria_id' => 4, 'subcategoria_id' => 11, 'talla_id' => 10,
                'nombre' => 'Opti-Men', 'descripcion' => 'Multivitamínico para hombres',
                'marca' => 'Optimum Nutrition', 'activo' => true
            ],
            [
                'categoria_id' => 4, 'subcategoria_id' => 12, 'talla_id' => 8,
                'nombre' => 'Vitamina D3 5000 IU', 'descripcion' => 'Alta potencia de vitamina D',
                'marca' => 'Now Foods', 'activo' => true
            ],
            
            // Quemadores
            [
                'categoria_id' => 5, 'subcategoria_id' => 15, 'talla_id' => 9,
                'nombre' => 'Hydroxycut Hardcore', 'descripcion' => 'Quemador termogénico avanzado',
                'marca' => 'MuscleTech', 'activo' => true
            ],
            [
                'categoria_id' => 5, 'subcategoria_id' => 17, 'talla_id' => 11,
                'nombre' => 'L-Carnitine 3000', 'descripcion' => 'L-Carnitina líquida de alta concentración',
                'marca' => 'MuscleTech', 'activo' => true
            ],
            
            // Aminoácidos
            [
                'categoria_id' => 7, 'subcategoria_id' => 20, 'talla_id' => 7,
                'nombre' => 'BCAA 2:1:1', 'descripcion' => 'Aminoácidos ramificados instantáneos',
                'marca' => 'Scivation', 'activo' => true
            ],
            [
                'categoria_id' => 7, 'subcategoria_id' => 21, 'talla_id' => 12,
                'nombre' => 'EAA Max', 'descripcion' => 'Los 9 aminoácidos esenciales',
                'marca' => 'PEScience', 'activo' => true
            ]
        ];

        foreach ($productos as $producto) {
            Producto::create($producto);
        }
    }
}