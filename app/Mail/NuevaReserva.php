<?php

namespace App\Mail;

use App\Models\Cita;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Aviso al correo del negocio cada vez que un cliente reserva una cita
 * desde la página web, con todos sus datos.
 */
class NuevaReserva extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Cita $cita)
    {
    }

    public function envelope(): Envelope
    {
        $negocio = config('services.negocio.nombre');

        return new Envelope(
            from: new Address(config('mail.from.address'), $negocio . ' - Reservas'),
            // "Responder" contesta directamente al cliente (si dejó correo).
            replyTo: $this->cita->email ? [new Address($this->cita->email, $this->cita->cliente_nombre)] : [],
            subject: 'Nueva reserva: ' . $this->cita->cliente_nombre . ' - '
                   . $this->cita->fecha->format('d/m/Y') . ' ' . $this->cita->horaTexto(),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.nueva-reserva',
            with: ['negocio' => config('services.negocio.nombre')],
        );
    }
}
