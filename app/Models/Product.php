<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'codigo',
        'category_id',
        'producto',
        'marca',
        'presentacion_valor',
        'presentacion_unidad',
        'existencia',
        'stock_minimo',
        'precio_compra',
        'precio_venta',
    ];

    /**
     * Categoría a la que pertenece este producto.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}