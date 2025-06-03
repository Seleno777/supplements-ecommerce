<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;
use Illuminate\Support\Facades\Hash;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = [
            [
                'nombre' => 'Juan Carlos',
                'apellido' => 'Pérez López',
                'telefono' => '+593987654321',
                'email' => 'juan.perez@email.com',
                'password' => Hash::make('password123')
            ],
            [
                'nombre' => 'María Elena',
                'apellido' => 'García Rodríguez',
                'telefono' => '+593987654322',
                'email' => 'maria.garcia@email.com',
                'password' => Hash::make('password123')
            ],
            [
                'nombre' => 'Luis Fernando',
                'apellido' => 'Martínez Silva',
                'telefono' => '+593987654323',
                'email' => 'luis.martinez@email.com',
                'password' => Hash::make('password123')
            ],
            [
                'nombre' => 'Ana Cristina',
                'apellido' => 'Hernández Vega',
                'telefono' => '+593987654324',
                'email' => 'ana.hernandez@email.com',
                'password' => Hash::make('password123')
            ],
            [
                'nombre' => 'Carlos Eduardo',
                'apellido' => 'Jiménez Torres',
                'telefono' => '+593987654325',
                'email' => 'carlos.jimenez@email.com',
                'password' => Hash::make('password123')
            ]
        ];

        foreach ($clientes as $cliente) {
            Cliente::create($cliente);
        }
    }
}