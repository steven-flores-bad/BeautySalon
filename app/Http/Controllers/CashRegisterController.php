<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\Sale;
use App\Models\ServiceSale;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CashRegisterController extends Controller
{
    /**
     * Muestra el estado de la caja de hoy: si no existe, el formulario de
     * apertura; si está abierta, los totales en vivo y el formulario de
     * cierre; si ya está cerrada, el resumen final con la diferencia.
     */
    public function index()
    {
        $hoy = Carbon::today();

        $caja = CashRegister::whereDate('fecha', $hoy)->first();

        $ventasEfectivoHoy = $this->ventasEfectivoDelDia($hoy);
        $ventasTarjetaTransferenciaHoy = $this->ventasNoEfectivoDelDia($hoy);

        $efectivoEsperado = $caja ? $caja->monto_apertura + $ventasEfectivoHoy : null;

        return view('cash_register.index', compact(
            'caja',
            'ventasEfectivoHoy',
            'ventasTarjetaTransferenciaHoy',
            'efectivoEsperado'
        ));
    }

    /**
     * Abrir la caja del día con un monto inicial (cambio/fondo).
     */
    public function open(Request $request)
    {
        $hoy = Carbon::today();

        if (CashRegister::whereDate('fecha', $hoy)->exists()) {
            return redirect()->route('cash-register.index')
                             ->with('error', 'Ya existe una caja registrada para el día de hoy.');
        }

        $validated = $request->validate([
            'monto_apertura' => 'required|numeric|min:0',
            'notas' => 'nullable|string',
        ]);

        CashRegister::create([
            'user_id' => auth()->id(),
            'fecha' => $hoy,
            'monto_apertura' => $validated['monto_apertura'],
            'estado' => 'abierta',
            'notas' => $validated['notas'] ?? null,
        ]);

        return redirect()->route('cash-register.index')
                         ->with('success', 'Caja abierta con $' . number_format($validated['monto_apertura'], 2) . '.');
    }

    /**
     * Cerrar la caja del día: compara el efectivo contado contra el esperado
     * (apertura + ventas en efectivo del día) y guarda la diferencia.
     */
    public function close(Request $request)
    {
        $hoy = Carbon::today();

        $caja = CashRegister::whereDate('fecha', $hoy)->where('estado', 'abierta')->first();

        if (!$caja) {
            return redirect()->route('cash-register.index')
                             ->with('error', 'No hay una caja abierta para cerrar hoy.');
        }

        $validated = $request->validate([
            'monto_cierre_real' => 'required|numeric|min:0',
            'notas_cierre' => 'nullable|string',
        ]);

        $efectivoEsperado = $caja->monto_apertura + $this->ventasEfectivoDelDia($hoy);
        $diferencia = $validated['monto_cierre_real'] - $efectivoEsperado;

        $notas = $caja->notas;
        if (!empty($validated['notas_cierre'])) {
            $notas = trim(($notas ? $notas . "\n" : '') . '[Cierre] ' . $validated['notas_cierre']);
        }

        $caja->update([
            'monto_cierre_esperado' => $efectivoEsperado,
            'monto_cierre_real' => $validated['monto_cierre_real'],
            'diferencia' => $diferencia,
            'estado' => 'cerrada',
            'notas' => $notas,
        ]);

        $mensaje = 'Caja cerrada. ';
        if (abs($diferencia) < 0.01) {
            $mensaje .= 'El efectivo cuadra exactamente.';
        } elseif ($diferencia > 0) {
            $mensaje .= 'Sobrante de $' . number_format($diferencia, 2) . '.';
        } else {
            $mensaje .= 'Faltante de $' . number_format(abs($diferencia), 2) . '.';
        }

        return redirect()->route('cash-register.index')->with('success', $mensaje);
    }

    /**
     * Suma de ventas (productos + servicios) pagadas en EFECTIVO en el día dado.
     * Es lo único que afecta el efectivo físico dentro de la caja.
     */
    private function ventasEfectivoDelDia(Carbon $fecha): float
    {
        $productos = Sale::whereDate('created_at', $fecha)
                        ->where('estado', 'completada')
                        ->where('metodo_pago', 'efectivo')
                        ->sum('total');

        $servicios = ServiceSale::whereDate('created_at', $fecha)
                        ->where('estado', 'completada')
                        ->where('metodo_pago', 'efectivo')
                        ->sum('total');

        return $productos + $servicios;
    }

    /**
     * Suma de ventas pagadas con tarjeta o transferencia (informativo: ese
     * dinero no entra físicamente a la caja, pero sí forma parte del total
     * del día para efectos de reportes).
     */
    private function ventasNoEfectivoDelDia(Carbon $fecha): float
    {
        $productos = Sale::whereDate('created_at', $fecha)
                        ->where('estado', 'completada')
                        ->whereIn('metodo_pago', ['tarjeta', 'transferencia'])
                        ->sum('total');

        $servicios = ServiceSale::whereDate('created_at', $fecha)
                        ->where('estado', 'completada')
                        ->whereIn('metodo_pago', ['tarjeta', 'transferencia'])
                        ->sum('total');

        return $productos + $servicios;
    }
}
