<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use Illuminate\Http\Request;
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

        $cita->update(['estado' => $validated['estado']]);

        return redirect()->back()->with('success', 'Cita de ' . $cita->cliente_nombre . ' marcada como ' . mb_strtolower($cita->nombreEstado()) . '.');
    }
}
