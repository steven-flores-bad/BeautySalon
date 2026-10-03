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

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Gastos pagados en efectivo con esta caja (salieron de la caja).
     */
    public function totalGastosEfectivo(): float
    {
        return (float) $this->expenses()->where('metodo_pago', 'efectivo')->sum('monto');
    }

    /**
     * Comisiones de las empleadas por los servicios vendidos en esta caja
     * (ventas completadas). Se pagan en efectivo desde la caja.
     */
    public function totalComisiones(): float
    {
        return (float) ServiceSaleDetail::join('service_sales', 'service_sales.id', '=', 'service_sale_details.service_sale_id')
                                        ->where('service_sales.cash_register_id', $this->id)
                                        ->where('service_sales.estado', 'completada')
                                        ->sum('service_sale_details.comision_monto');
    }

    /**
     * Efectivo que debería haber en la caja:
     * apertura + ventas en efectivo - gastos en efectivo - comisiones.
     */
    public function efectivoEsperado(): float
    {
        return (float) $this->monto_apertura + $this->totalVentas(['efectivo'])
             - $this->totalGastosEfectivo() - $this->totalComisiones();
    }

    /**
     * Comisiones que realmente se descontaron al cerrar esta caja. Las cajas
     * cerradas antes de que las comisiones salieran de la caja no las
     * descontaron, así que se deduce del esperado guardado al cierre.
     */
    public function comisionesDescontadas(): float
    {
        if ($this->estaAbierta()) {
            return $this->totalComisiones();
        }

        $descontado = (float) $this->monto_apertura + $this->totalVentas(['efectivo'])
                    - $this->totalGastosEfectivo() - (float) $this->monto_cierre_esperado;

        return max(round($descontado, 2), 0);
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
