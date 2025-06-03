<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subcategoria;

class SubcategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $subcategorias = [
            // Proteínas
            ['categoria_id' => 1, 'nombre' => 'Whey Protein', 'descripcion' => 'Proteína de suero de leche', 'activo' => true],
            ['categoria_id' => 1, 'nombre' => 'Caseína', 'descripcion' => 'Proteína de absorción lenta', 'activo' => true],
            ['categoria_id' => 1, 'nombre' => 'Proteína Vegetal', 'descripcion' => 'Proteínas de origen vegetal', 'activo' => true],
            ['categoria_id' => 1, 'nombre' => 'Isolate', 'descripcion' => 'Proteína aislada de alta pureza', 'activo' => true],
            
            // Creatinas
            ['categoria_id' => 2, 'nombre' => 'Creatina Monohidrato', 'descripcion' => 'Creatina básica más estudiada', 'activo' => true],
            ['categoria_id' => 2, 'nombre' => 'Creatina HCl', 'descripcion' => 'Creatina con mejor solubilidad', 'activo' => true],
            ['categoria_id' => 2, 'nombre' => 'Creatina Alcalina', 'descripcion' => 'Creatina con pH balanceado', 'activo' => true],
            
            // Pre-Entreno
            ['categoria_id' => 3, 'nombre' => 'Con Estimulantes', 'descripcion' => 'Pre-entreno con cafeína', 'activo' => true],
            ['categoria_id' => 3, 'nombre' => 'Sin Estimulantes', 'descripcion' => 'Pre-entreno libre de cafeína', 'activo' => true],
            ['categoria_id' => 3, 'nombre' => 'Pump', 'descripcion' => 'Para congestión muscular', 'activo' => true],
            
            // Vitaminas
            ['categoria_id' => 4, 'nombre' => 'Multivitamínicos', 'descripcion' => 'Complejos completos de vitaminas', 'activo' => true],
            ['categoria_id' => 4, 'nombre' => 'Vitamina D', 'descripcion' => 'Suplementos de vitamina D', 'activo' => true],
            ['categoria_id' => 4, 'nombre' => 'Vitamina C', 'descripcion' => 'Suplementos de vitamina C', 'activo' => true],
            ['categoria_id' => 4, 'nombre' => 'Complejo B', 'descripcion' => 'Vitaminas del complejo B', 'activo' => true],
            
            // Quemadores
            ['categoria_id' => 5, 'nombre' => 'Termogénicos', 'descripcion' => 'Quemadores que aumentan la temperatura corporal', 'activo' => true],
            ['categoria_id' => 5, 'nombre' => 'Bloqueadores', 'descripcion' => 'Bloqueadores de carbohidratos y grasas', 'activo' => true],
            ['categoria_id' => 5, 'nombre' => 'L-Carnitina', 'descripcion' => 'Para transporte de grasas', 'activo' => true],
            
            // Post-Entreno
            ['categoria_id' => 6, 'nombre' => 'Recovery', 'descripcion' => 'Fórmulas de recuperación completa', 'activo' => true],
            ['categoria_id' => 6, 'nombre' => 'Glutamina', 'descripcion' => 'Aminoácido para recuperación', 'activo' => true],
            
            // Aminoácidos
            ['categoria_id' => 7, 'nombre' => 'BCAA', 'descripcion' => 'Aminoácidos de cadena ramificada', 'activo' => true],
            ['categoria_id' => 7, 'nombre' => 'EAA', 'descripcion' => 'Aminoácidos esenciales completos', 'activo' => true],
            ['categoria_id' => 7, 'nombre' => 'Arginina', 'descripcion' => 'Para producción de óxido nítrico', 'activo' => true]
        ];

        foreach ($subcategorias as $subcategoria) {
            Subcategoria::create($subcategoria);
        }
    }
}
