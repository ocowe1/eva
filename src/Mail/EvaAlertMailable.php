<?php
namespace Eva\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EvaAlertMailable extends Mailable
{
    use Queueable, SerializesModels;

    public array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function build()
    {
        $subject = $this->payload['title'] ?? '[EVA] Alerta de Erro';
        return $this->subject($subject)
                    ->view('eva::emails.alert')
                    ->with($this->payload);
    }
}
