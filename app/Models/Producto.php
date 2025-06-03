<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'categoria_id',
        'subcategoria_id',
        'talla_id',
        'nombre',
        'descripcion',
        'marca',
        'imagen',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function subcategoria()
    {
        return $this->belongsTo(Subcategoria::class);
    }

    public function talla()
    {
        return $this->belongsTo(Talla::class);
    }

    public function inventario()
    {
        return $this->hasMany(Inventario::class);
    }

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    public function getImagenUrlAttribute()
    {
        return $this->imagen ? asset('storage/productos/' . $this->imagen) : null;
    }
}
