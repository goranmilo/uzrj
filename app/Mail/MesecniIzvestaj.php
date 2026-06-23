<?php

namespace App\Mail;

use App\Models\Clan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MesecniIzvestaj extends Mailable
{
    use Queueable, SerializesModels;

    public Clan $clan;
    public array $data;

    /**
     * Create a new message instance.
     */
    public function __construct(Clan $clan, array $data)
    {
        $this->clan = $clan;
        $this->data = $data;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Mesečni izveštaj - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.mesecni-izvestaj',
            with: [
                'clan' => $this->clan,
                'aktuelnosti' => $this->data['aktuelnosti'] ?? [],
                'clanarina' => $this->data['clanarina'] ?? null,
                'edukacije' => $this->data['edukacije'] ?? [],
                'bodovi' => $this->data['bodovi'] ?? [],
                'statusBodova' => $this->data['statusBodova'] ?? null,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
