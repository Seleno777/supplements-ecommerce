<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrito extends Model
{
    use HasFactory;

    protected $table = 'carrito';

    protected $fillable = [
        'cliente_id',
        'inventario_id',
        'precio',
        'cantidad'
    ];

    protected $casts = [
        'precio' => 'decimal:2',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function inventario()
    {
        return $this->belongsTo(Inventario::class);
    }

    public function getSubtotalAttribute()
    {
        return $this->precio * $this->cantidad;
    }
}