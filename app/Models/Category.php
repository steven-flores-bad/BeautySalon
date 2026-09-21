<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // Tabla asociada (opcional si sigue la convención en plural, pero es buena práctica)
    protected $table = 'categories';

    // Campos permitidos para asignación masiva
    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    /**
     * Relación: Una categoría tiene muchos productos.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}