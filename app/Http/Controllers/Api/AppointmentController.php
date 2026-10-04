<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Recibe las citas que los clientes reservan en la página de WordPress.
 * La protege el middleware "wordpress" (clave secreta en X-Api-Key).
 */
class AppointmentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'   => 'required|string|max:150',
            'telefono' => 'required|string|max:30',
            'email'    => 'nullable|email|max:150',
            'servicio' => 'required|string|max:150',
            'fecha'    => 'required|string',
            'hora'     => 'required|string|max:20',
            'notas'    => 'nullable|string|max:1000',
        ]);

        // WordPress puede enviar la fecha como 2026-10-25 o 25/10/2026,
        // y la hora como 14:30 o 2:30 PM.
        $fecha = $this->leerFecha($validated['fecha']);
        $hora = $this->leerHora($validated['hora']);

        if (!$fecha || !$hora) {
            return response()->json([
                'success' => false,
                'message' => 'La fecha o la hora no tienen un formato válido.',
            ], 422);
        }

        if ($fecha->lt(today())) {
            return response()->json([
                'success' => false,
                'message' => 'La fecha de la cita no puede ser anterior a hoy.',
            ], 422);
        }

        // Si el servicio coincide por nombre con uno del sistema, se liga.
        $servicio = Service::whereRaw('LOWER(nombre) = ?', [mb_strtolower(trim($validated['servicio']))])->first();

        $cita = Cita::create([
            'cliente_nombre' => trim($validated['nombre']),
            'telefono'       => trim($validated['telefono']),
            'email'          => $validated['email'] ?? null,
            'servicio'       => trim($validated['servicio']),
            'service_id'     => $servicio?->id,
            'fecha'          => $fecha->toDateString(),
            'hora'           => $hora,
            'notas'          => $validated['notas'] ?? null,
            'estado'         => 'pendiente',
            'origen'         => 'wordpress',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cita registrada correctamente.',
            'data'    => [
                'id' => $cita->id,
                'fecha' => $cita->fecha->format('d/m/Y'),
                'hora' => $cita->horaTexto(),
                'estado' => $cita->estado,
            ],
        ], 201);
    }

    private function leerFecha(string $valor): ?Carbon
    {
        foreach (['Y-m-d', 'd/m/Y', 'j/n/Y', 'd-m-Y'] as $formato) {
            try {
                $fecha = Carbon::createFromFormat('!' . $formato, trim($valor));
                if ($fecha && $fecha->format($formato) === trim($valor)) {
                    return $fecha;
                }
            } catch (\Throwable $e) {
                // se prueba el siguiente formato
            }
        }

        return null;
    }

    private function leerHora(string $valor): ?string
    {
        try {
            return Carbon::parse(trim($valor))->format('H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
