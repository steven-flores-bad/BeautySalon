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
 * Correo al cliente cuando el negocio confirma su cita desde la pantalla
 * de Citas (botón "Confirmar").
 */
class CitaConfirmada extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Cita $cita)
    {
    }

    public function envelope(): Envelope
    {
        $negocio = config('services.negocio.nombre');

        return new Envelope(
            from: new Address(config('mail.from.address'), $negocio),
            replyTo: [new Address(config('services.negocio.email'), $negocio)],
            subject: '¡Tu cita está confirmada! - ' . $negocio,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cita-confirmada',
            with: ['negocio' => config('services.negocio.nombre')],
        );
    }
}
