<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Cita reservada por un cliente (por ahora, desde la página de WordPress).
 */
class Cita extends Model
{
    protected $table = 'citas';

    public const ESTADOS = [
        'pendiente' => 'Pendiente',
        'confirmada' => 'Confirmada',
        'atendida' => 'Atendida',
        'cancelada' => 'Cancelada',
    ];

    protected $fillable = [
        'cliente_nombre',
        'telefono',
        'email',
        'servicio',
        'service_id',
        'fecha',
        'hora',
        'notas',
        'estado',
        'origen',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function nombreEstado(): string
    {
        return self::ESTADOS[$this->estado] ?? ucfirst($this->estado);
    }

    /**
     * Hora en formato de 12 horas (ej. "02:30 p. m.").
     */
    public function horaTexto(): string
    {
        return \Carbon\Carbon::parse($this->hora)->format('h:i A');
    }
}
