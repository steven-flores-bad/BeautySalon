<?php

namespace App\Mail;

use App\Models\Cita;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
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
        return new Envelope(
            subject: 'Recibimos tu reserva en ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cita-recibida',
        );
    }
}
