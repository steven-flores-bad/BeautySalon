<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $table = 'sales';

    protected $fillable = [
        'user_id',
        'cliente_nombre',
        'metodo_pago',
        'subtotal',
        'descuento',
        'total',
        'estado',
        'notas',
    ];

    /**
     * Usuario (cajero) que registró la venta.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Líneas de detalle: los productos incluidos en esta venta.
     */
    public function details()
    {
        return $this->hasMany(SaleDetail::class);
    }
}
