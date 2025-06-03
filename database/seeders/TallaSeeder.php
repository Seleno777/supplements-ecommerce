<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Talla;

class TallaSeeder extends Seeder
{
    public function run(): void
    {
        $tallas = [
            ['nombre' => '1 lb', 'descripcion' => '454 gramos'],
            ['nombre' => '2 lbs', 'descripcion' => '908 gramos'],
            ['nombre' => '5 lbs', 'descripcion' => '2.27 kilogramos'],
            ['nombre' => '10 lbs', 'descripcion' => '4.54 kilogramos'],
            ['nombre' => '300g', 'descripcion' => '300 gramos'],
            ['nombre' => '500g', 'descripcion' => '500 gramos'],
            ['nombre' => '1kg', 'descripcion' => '1000 gramos'],
            ['nombre' => '60 caps', 'descripcion' => '60 cápsulas'],
            ['nombre' => '90 caps', 'descripcion' => '90 cápsulas'],
            ['nombre' => '120 caps', 'descripcion' => '120 cápsulas'],
            ['nombre' => '180 caps', 'descripcion' => '180 cápsulas'],
            ['nombre' => '30 serv', 'descripcion' => '30 servicios'],
            ['nombre' => '60 serv', 'descripcion' => '60 servicios'],
            ['nombre' => '100 tabs', 'descripcion' => '100 tabletas'],
            ['nombre' => '200ml', 'descripcion' => '200 mililitros'],
            ['nombre' => '500ml', 'descripcion' => '500 mililitros'],
            ['nombre' => '1L', 'descripcion' => '1 litro']
        ];

        foreach ($tallas as $talla) {
            Talla::create($talla);
        }
    }
}