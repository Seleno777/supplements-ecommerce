<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    use HasFactory;

    protected $table = 'inventario';

    protected $fillable = [
        'producto_id',
        'cantidad',
        'unidad',
        'precio',
        'fecha_vencimiento'
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
        'precio' => 'decimal:2',
        'fecha_vencimiento' => 'date',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function carrito()
    {
        return $this->hasMany(Carrito::class);
    }

    public function scopeDisponible($query)
    {
        return $query->where('cantidad', '>', 0);
    }

    public function scopeNoVencido($query)
    {
        return $query->where('fecha_vencimiento', '>', now());
    }
}