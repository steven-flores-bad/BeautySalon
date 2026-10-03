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
        'cash_register_id',
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
     * Caja en la que se registró la venta.
     */
    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }

    /**
     * Líneas de detalle: los productos incluidos en esta venta.
     */
    public function details()
    {
        return $this->hasMany(SaleDetail::class);
    }

    /**
     * Solo se puede cancelar si está completada y pertenece a la caja de
     * HOY que sigue abierta. Las ventas de días anteriores no se pueden
     * cancelar, aunque su caja haya quedado abierta.
     */
    public function sePuedeCancelar(): bool
    {
        return $this->estado === 'completada'
            && $this->cashRegister
            && $this->cashRegister->estaAbierta()
            && $this->cashRegister->esDeHoy();
    }
}
