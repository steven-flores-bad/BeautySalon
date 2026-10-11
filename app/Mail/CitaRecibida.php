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
 * Correo al cliente cuando su cita (reservada en la página web) quedó
 * registrada en el sistema.
 */
class CitaRecibida extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Cita $cita)
    {
    }

    public function envelope(): Envelope
    {
        $negocio = config('services.negocio.nombre');

        return new Envelope(
            // Remitente con el nombre del negocio (no depende de APP_NAME).
            from: new Address(config('mail.from.address'), $negocio),
            // Si el cliente responde, le llega al correo del negocio.
            replyTo: [new Address(config('services.negocio.email'), $negocio)],
            subject: 'Recibimos tu reserva en ' . $negocio,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cita-recibida',
            with: ['negocio' => config('services.negocio.nombre')],
        );
    }
}
