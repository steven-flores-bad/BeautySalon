<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Rango de fechas de un período (día, semana o mes) a partir de una fecha,
 * con la fecha del período anterior y siguiente para navegar con ‹ ›.
 * Lo usan los reportes y la pantalla de Gastos.
 */
class Periodo
{
    public const VALIDOS = ['dia', 'semana', 'mes'];

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string, 3: string} [inicio, fin, fechaAnterior, fechaSiguiente]
     */
    public static function rango(string $periodo, string $fecha): array
    {
        $fechaAncla = Carbon::parse($fecha);

        switch ($periodo) {
            case 'semana':
                $inicio = $fechaAncla->copy()->startOfWeek(Carbon::MONDAY);
                $fin = $fechaAncla->copy()->endOfWeek(Carbon::SUNDAY);
                $fechaAnterior = $inicio->copy()->subWeek()->toDateString();
                $fechaSiguiente = $inicio->copy()->addWeek()->toDateString();
                break;

            case 'mes':
                $inicio = $fechaAncla->copy()->startOfMonth();
                $fin = $fechaAncla->copy()->endOfMonth();
                $fechaAnterior = $inicio->copy()->subMonth()->toDateString();
                $fechaSiguiente = $inicio->copy()->addMonth()->toDateString();
                break;

            default: // dia
                $inicio = $fechaAncla->copy()->startOfDay();
                $fin = $fechaAncla->copy()->endOfDay();
                $fechaAnterior = $inicio->copy()->subDay()->toDateString();
                $fechaSiguiente = $inicio->copy()->addDay()->toDateString();
                break;
        }

        return [$inicio, $fin, $fechaAnterior, $fechaSiguiente];
    }
}
