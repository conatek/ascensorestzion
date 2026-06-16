<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array{nombre:string,email:string,telefono:?string,asunto:string,asunto_label:string,mensaje:string}  $data
     */
    public function __construct(public array $data) {}

    public function envelope(): Envelope
    {
        $asunto = $this->data['asunto_label'] ?? 'Contacto';

        return new Envelope(
            subject: "Nuevo mensaje de contacto — {$asunto}",
            // Reply-To = remitente (correo validado) para responder directamente.
            replyTo: [new Address($this->data['email'], $this->data['nombre'])],
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.contact');
    }
}
