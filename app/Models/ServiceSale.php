<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceSale extends Model
{
    use HasFactory;

    protected $table = 'service_sales';

    protected $fillable = [
        'user_id',
        'cash_register_id',
        'cliente_nombre',
        'metodo_pago',
        'subtotal',
        'descuento',
        'iva',
        'total',
        'estado',
        'notas',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Caja en la que se registró la venta.
     */
    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function details()
    {
        return $this->hasMany(ServiceSaleDetail::class);
    }

    /**
     * Solo se puede cancelar si está completada y su caja sigue abierta
     * (o si es una venta antigua sin caja asignada).
     */
    public function sePuedeCancelar(): bool
    {
        return $this->estado === 'completada'
            && (!$this->cashRegister || $this->cashRegister->estaAbierta());
    }
}
