<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            [
                'nombre' => 'Proteínas',
                'descripcion' => 'Suplementos proteicos para el desarrollo muscular',
                'activo' => true
            ],
            [
                'nombre' => 'Creatinas', 
                'descripcion' => 'Suplementos de creatina para aumentar la fuerza y potencia',
                'activo' => true
            ],
            [
                'nombre' => 'Pre-Entreno',
                'descripcion' => 'Suplementos para energía antes del entrenamiento',
                'activo' => true
            ],
            [
                'nombre' => 'Vitaminas',
                'descripcion' => 'Complejos vitamínicos y minerales',
                'activo' => true
            ],
            [
                'nombre' => 'Quemadores',
                'descripcion' => 'Suplementos para quemar grasa y acelerar metabolismo',
                'activo' => true
            ],
            [
                'nombre' => 'Post-Entreno',
                'descripcion' => 'Suplementos para recuperación después del entrenamiento',
                'activo' => true
            ],
            [
                'nombre' => 'Aminoácidos',
                'descripcion' => 'Suplementos de aminoácidos esenciales',
                'activo' => true
            ]
        ];

        foreach ($categorias as $categoria) {
            Categoria::create($categoria);
        }
    }
}