<?php

namespace App\Http\Controllers;

use App\Mail\CitaConfirmada;
use App\Models\Cita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class CitaController extends Controller
{
    /**
     * Citas reservadas por los clientes. Por defecto: de hoy en adelante.
     * Vistas: "proximas" (hoy en adelante), "hoy" y "anteriores".
     */
    public function index(Request $request)
    {
        $vista = in_array($request->input('vista'), ['proximas', 'hoy', 'anteriores']) ? $request->input('vista') : 'proximas';
        $estado = array_key_exists((string) $request->input('estado'), Cita::ESTADOS) ? $request->input('estado') : null;
        $search = $request->input('search');

        $citas = Cita::with('service')
                    ->when($vista === 'hoy', fn ($q) => $q->whereDate('fecha', today()))
                    ->when($vista === 'proximas', fn ($q) => $q->whereDate('fecha', '>=', today()))
                    ->when($vista === 'anteriores', fn ($q) => $q->whereDate('fecha', '<', today()))
                    ->when($estado, fn ($q) => $q->where('estado', $estado))
                    ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                        $q->where('cliente_nombre', 'like', "%{$search}%")
                          ->orWhere('telefono', 'like', "%{$search}%")
                          ->orWhere('servicio', 'like', "%{$search}%");
                    }))
                    ->when($vista === 'anteriores',
                        fn ($q) => $q->orderByDesc('fecha')->orderByDesc('hora'),
                        fn ($q) => $q->orderBy('fecha')->orderBy('hora'))
                    ->paginate(20)
                    ->withQueryString();

        $resumen = [
            'pendientes' => Cita::where('estado', 'pendiente')->whereDate('fecha', '>=', today())->count(),
            'hoy' => Cita::whereDate('fecha', today())->whereIn('estado', ['pendiente', 'confirmada'])->count(),
        ];

        return view('citas.index', compact('citas', 'vista', 'estado', 'search', 'resumen'));
    }

    /**
     * Cambiar el estado de una cita (confirmar, marcar atendida o cancelar).
     */
    public function updateEstado(Request $request, Cita $cita)
    {
        $validated = $request->validate([
            'estado' => ['required', Rule::in(array_keys(Cita::ESTADOS))],
        ]);

        $estadoAnterior = $cita->estado;
        $cita->update(['estado' => $validated['estado']]);

        $mensaje = 'Cita de ' . $cita->cliente_nombre . ' marcada como ' . mb_strtolower($cita->nombreEstado()) . '.';

        // Al confirmar, se le avisa al cliente por correo.
        if ($cita->estado === 'confirmada' && $estadoAnterior !== 'confirmada') {
            $mensaje .= ' ' . $this->avisarConfirmacion($cita);
        }

        return redirect()->back()->with('success', $mensaje);
    }

    /**
     * Envía al cliente el correo "Tu cita está confirmada". Si falla, la
     * cita queda confirmada igual y el error se anota en el registro.
     */
    private function avisarConfirmacion(Cita $cita): string
    {
        if (!$cita->email) {
            return 'El cliente no dejó correo: avísale por teléfono (' . $cita->telefono . ').';
        }

        try {
            Mail::to($cita->email, $cita->cliente_nombre)->send(new CitaConfirmada($cita));
            return 'Se le envió un correo de confirmación a ' . $cita->email . '.';
        } catch (\Throwable $e) {
            Log::warning('No se pudo enviar la confirmación de la cita #' . $cita->id . ' a ' . $cita->email . ': ' . $e->getMessage());
            return 'No se pudo enviar el correo de confirmación; avísale por teléfono (' . $cita->telefono . ').';
        }
    }
}
