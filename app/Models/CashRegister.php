<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashRegister extends Model
{
    use HasFactory;

    protected $table = 'cash_registers';

    protected $fillable = [
        'user_id',
        'fecha',
        'monto_apertura',
        'monto_cierre_esperado',
        'monto_cierre_real',
        'diferencia',
        'estado',
        'notas',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function serviceSales()
    {
        return $this->hasMany(ServiceSale::class);
    }

    /**
     * Caja abierta del día de hoy (null si no se ha abierto o ya se cerró).
     */
    public static function abiertaHoy(bool $bloquear = false): ?self
    {
        return static::whereDate('fecha', today())
                     ->where('estado', 'abierta')
                     ->when($bloquear, fn ($q) => $q->lockForUpdate())
                     ->first();
    }

    /**
     * Líneas de las notas que registran correcciones de la apertura.
     */
    public function registrosDeCorreccion(): array
    {
        return array_values(array_filter(
            preg_split('/\R/', (string) $this->notas),
            fn ($linea) => str_starts_with(trim($linea), '[Apertura corregida')
        ));
    }

    /**
     * Notas escritas por el usuario, sin los registros de corrección.
     */
    public function notasEditables(): string
    {
        return trim(implode("\n", array_filter(
            preg_split('/\R/', (string) $this->notas),
            fn ($linea) => !str_starts_with(trim($linea), '[Apertura corregida')
        )));
    }

    /**
     * Caja de un día anterior que quedó abierta (la más antigua primero).
     * Debe cerrarse antes de poder abrir la caja de hoy.
     */
    public static function pendienteAnterior(): ?self
    {
        return static::where('estado', 'abierta')
                     ->whereDate('fecha', '<', today())
                     ->orderBy('fecha')
                     ->first();
    }

    public function esDeHoy(): bool
    {
        return $this->fecha->isToday();
    }

    /**
     * Efectivo que debería haber en la caja: apertura + ventas en efectivo.
     */
    public function efectivoEsperado(): float
    {
        return (float) $this->monto_apertura + $this->totalVentas(['efectivo']);
    }

    public function estaAbierta(): bool
    {
        return $this->estado === 'abierta';
    }

    /**
     * Suma de ventas completadas (productos + servicios) de esta caja,
     * filtradas por métodos de pago.
     */
    public function totalVentas(array $metodosPago): float
    {
        $productos = $this->sales()
                          ->where('estado', 'completada')
                          ->whereIn('metodo_pago', $metodosPago)
                          ->sum('total');

        $servicios = $this->serviceSales()
                          ->where('estado', 'completada')
                          ->whereIn('metodo_pago', $metodosPago)
                          ->sum('total');

        return $productos + $servicios;
    }
}
