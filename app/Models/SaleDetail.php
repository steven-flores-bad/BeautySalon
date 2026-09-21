<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleDetail extends Model
{
    use HasFactory;

    protected $table = 'sale_details';

    protected $fillable = [
        'sale_id',
        'product_id',
        'cantidad',
        'precio_unitario',
        'descuento',
        'subtotal',
    ];

    /**
     * Venta a la que pertenece esta línea.
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Producto vendido en esta línea.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}