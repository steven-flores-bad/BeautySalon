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
     * Solo se puede cancelar si está completada y pertenece a la caja de
     * HOY que sigue abierta. Las ventas de días anteriores no se pueden
     * cancelar, aunque su caja haya quedado abierta.
     */
    /**
     * Líneas de las notas que registran ediciones de la venta.
     */
    public function registrosDeEdicion(): array
    {
        return array_values(array_filter(
            preg_split('/\R/', (string) $this->notas),
            fn ($linea) => str_starts_with(trim($linea), '[Editada el')
        ));
    }

    /**
     * Notas escritas por el usuario, sin los registros de edición.
     */
    public function notasEditables(): string
    {
        return trim(implode("\n", array_filter(
            preg_split('/\R/', (string) $this->notas),
            fn ($linea) => !str_starts_with(trim($linea), '[Editada el')
        )));
    }

    public function sePuedeCancelar(): bool
    {
        return $this->estado === 'completada'
            && $this->cashRegister
            && $this->cashRegister->estaAbierta()
            && $this->cashRegister->esDeHoy();
    }
}
