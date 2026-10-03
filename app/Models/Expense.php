<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $table = 'expenses';

    /**
     * Categorías de gasto (clave guardada en la base => nombre visible).
     */
    public const CATEGORIAS = [
        'insumos' => 'Insumos y productos',
        'alquiler' => 'Alquiler',
        'servicios_basicos' => 'Servicios básicos (luz, agua, internet)',
        'sueldos' => 'Sueldos', // las comisiones se calculan solas en cada venta de servicio
        'mantenimiento' => 'Mantenimiento',
        'publicidad' => 'Publicidad',
        'otros' => 'Otros',
    ];

    protected $fillable = [
        'user_id',
        'cash_register_id',
        'fecha',
        'categoria',
        'descripcion',
        'monto',
        'metodo_pago',
        'notas',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function nombreCategoria(): string
    {
        return self::CATEGORIAS[$this->categoria] ?? ucfirst($this->categoria);
    }

    /**
     * Un gasto que ya forma parte del arqueo de una caja cerrada no se
     * puede editar ni eliminar (cambiaría un cierre ya hecho).
     */
    public function sePuedeModificar(): bool
    {
        return !$this->cashRegister || $this->cashRegister->estaAbierta();
    }
}
