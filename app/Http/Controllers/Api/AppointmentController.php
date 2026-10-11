<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\CitaRecibida;
use App\Mail\NuevaReserva;
use App\Models\Cita;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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

        // Verificar si el horario ya está ocupado (las citas canceladas no
        // ocupan el horario: ese espacio vuelve a quedar libre).
        $citaExistente = Cita::whereDate('fecha', $fecha->toDateString())
                             ->where('hora', $hora)
                             ->where('estado', '!=', 'cancelada')
                             ->exists();

        if ($citaExistente) {
            return response()->json([
                'success' => false,
                'message' => 'Lo sentimos, este horario ya está reservado. Por favor, selecciona otra hora o fecha.',
            ], 422); // Código 422 para indicar conflicto de datos
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

        $correoEnviado = $this->enviarConfirmacion($cita);
        $avisoNegocio = $this->avisarAlNegocio($cita);

        return response()->json([
            'success' => true,
            'message' => 'Cita registrada correctamente.',
            'data'    => [
                'id' => $cita->id,
                'fecha' => $cita->fecha->format('d/m/Y'),
                'hora' => $cita->horaTexto(),
                'estado' => $cita->estado,
                'correo_enviado' => $correoEnviado,
                'aviso_negocio' => $avisoNegocio,
            ],
        ], 201);
    }

    /**
     * Envía al correo del negocio (NEGOCIO_EMAIL) un aviso con los datos de
     * quien reservó. Si falla, la cita queda registrada igual.
     */
    private function avisarAlNegocio(Cita $cita): bool
    {
        $correoNegocio = config('services.negocio.email');

        if (!$correoNegocio) {
            return false;
        }

        try {
            Mail::to($correoNegocio)->send(new NuevaReserva($cita->load('service')));
            return true;
        } catch (\Throwable $e) {
            Log::warning('No se pudo enviar el aviso de la cita #' . $cita->id . ' al negocio (' . $correoNegocio . '): ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envía al cliente el correo "Recibimos tu reserva" (si dejó su correo).
     * Si el envío falla (por ejemplo, datos de Gmail incorrectos en el .env),
     * la cita queda registrada igual y el error se anota en el registro.
     */
    private function enviarConfirmacion(Cita $cita): bool
    {
        if (!$cita->email) {
            return false;
        }

        try {
            Mail::to($cita->email, $cita->cliente_nombre)->send(new CitaRecibida($cita));
            return true;
        } catch (\Throwable $e) {
            Log::warning('No se pudo enviar el correo de la cita #' . $cita->id . ' a ' . $cita->email . ': ' . $e->getMessage());
            return false;
        }
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
