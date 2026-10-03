<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // Con la caja abierta los totales se calculan en vivo. Con la caja
        // cerrada se usan los valores guardados al cerrar (ver la vista).
        $ventasEfectivoHoy = $caja ? $caja->totalVentas(['efectivo']) : 0;
        $ventasTarjetaTransferenciaHoy = $caja ? $caja->totalVentas(['tarjeta', 'transferencia']) : 0;
        $gastosEfectivoHoy = $caja ? $caja->totalGastosEfectivo() : 0;
        $comisionesHoy = $caja ? $caja->totalComisiones() : 0;

        $efectivoEsperado = $caja ? $caja->efectivoEsperado() : null;

        // Caja de un día anterior que quedó abierta: hay que cerrarla
        // (con su arqueo) antes de poder abrir la de hoy.
        $pendiente = CashRegister::pendienteAnterior();
        $cajasPendientes = CashRegister::where('estado', 'abierta')->whereDate('fecha', '<', $hoy)->count();

        return view('cash_register.index', compact(
            'caja',
            'ventasEfectivoHoy',
            'ventasTarjetaTransferenciaHoy',
            'gastosEfectivoHoy',
            'comisionesHoy',
            'efectivoEsperado',
            'pendiente',
            'cajasPendientes'
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

        if ($pendiente = CashRegister::pendienteAnterior()) {
            return redirect()->route('cash-register.index')
                             ->with('error', 'Primero debes cerrar la caja del ' . $pendiente->fecha->format('d/m/Y') . ', que quedó abierta.');
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
     * Corregir el monto de apertura (o las notas) si se escribió mal.
     * Solo mientras la caja siga abierta: una caja cerrada ya tiene su
     * arqueo calculado. El cambio queda anotado en las notas de la caja.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'monto_apertura' => 'required|numeric|min:0',
            'notas' => 'nullable|string',
        ]);

        $caja = DB::transaction(function () use ($validated) {
            $caja = CashRegister::abiertaHoy(bloquear: true);

            if (!$caja) {
                return null;
            }

            $anterior = (float) $caja->monto_apertura;
            $nuevo = (float) $validated['monto_apertura'];

            // Los registros de correcciones anteriores se conservan siempre;
            // el usuario solo edita sus propias notas.
            $registros = $caja->registrosDeCorreccion();

            if (abs($anterior - $nuevo) >= 0.01) {
                $registros[] = '[Apertura corregida de $' . number_format($anterior, 2) . ' a $' . number_format($nuevo, 2)
                             . ' por ' . (auth()->user()->name ?? 'usuario') . ', ' . now()->format('d/m/Y H:i') . ']';
            }

            $notas = trim(implode("\n", array_filter([trim($validated['notas'] ?? ''), ...$registros])));

            $caja->update(['monto_apertura' => $nuevo, 'notas' => $notas !== '' ? $notas : null]);

            return $caja;
        });

        if (!$caja) {
            return redirect()->route('cash-register.index')
                             ->with('error', 'Solo se puede editar la apertura mientras la caja está abierta.');
        }

        return redirect()->route('cash-register.index')
                         ->with('success', 'Apertura actualizada a $' . number_format($caja->monto_apertura, 2) . '.');
    }

    /**
     * Cerrar una caja (la de hoy o una de un día anterior que quedó
     * abierta): compara el efectivo contado contra el esperado
     * (apertura + ventas en efectivo de esa caja) y guarda la diferencia.
     * Después del cierre ya no se puede vender ni cancelar ventas de esa caja.
     */
    public function close(Request $request)
    {
        $validated = $request->validate([
            'caja_id' => 'required|integer',
            'monto_cierre_real' => 'required|numeric|min:0',
            'notas_cierre' => 'nullable|string',
        ]);

        // Se bloquea la fila de la caja para que ninguna venta se registre
        // o cancele mientras se calcula el cierre.
        $caja = DB::transaction(function () use ($validated) {
            $caja = CashRegister::where('id', $validated['caja_id'])
                                ->where('estado', 'abierta')
                                ->lockForUpdate()
                                ->first();

            if (!$caja) {
                return null;
            }

            $efectivoEsperado = $caja->efectivoEsperado();

            $lineas = array_filter([$caja->notas]);
            if (!$caja->esDeHoy()) {
                $lineas[] = '[Cierre tardío el ' . now()->format('d/m/Y H:i') . ' por ' . (auth()->user()->name ?? 'usuario') . ']';
            }
            if (!empty($validated['notas_cierre'])) {
                $lineas[] = '[Cierre] ' . $validated['notas_cierre'];
            }
            $notas = $lineas ? trim(implode("\n", $lineas)) : null;

            $caja->update([
                'monto_cierre_esperado' => $efectivoEsperado,
                'monto_cierre_real' => $validated['monto_cierre_real'],
                'diferencia' => $validated['monto_cierre_real'] - $efectivoEsperado,
                'estado' => 'cerrada',
                'notas' => $notas,
            ]);

            return $caja;
        });

        if (!$caja) {
            return redirect()->route('cash-register.index')
                             ->with('error', 'Esa caja no existe o ya estaba cerrada.');
        }

        $diferencia = $caja->diferencia;

        $mensaje = 'Caja del ' . $caja->fecha->format('d/m/Y') . ' cerrada. ';
        if (abs($diferencia) < 0.01) {
            $mensaje .= 'El efectivo cuadra exactamente.';
        } elseif ($diferencia > 0) {
            $mensaje .= 'Sobrante de $' . number_format($diferencia, 2) . '.';
        } else {
            $mensaje .= 'Faltante de $' . number_format(abs($diferencia), 2) . '.';
        }

        return redirect()->route('cash-register.index')->with('success', $mensaje);
    }
}
