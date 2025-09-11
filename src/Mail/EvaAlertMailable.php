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
        $mail = $this->subject($subject)
                    ->view('eva::emails.alert')
                    ->with($this->payload);

        // Se houver configuração específica no payload ou config, aplicar como from
        if (!empty($this->payload['from'])) {
            $mail->from($this->payload['from'], $this->payload['from_name'] ?? null);
        } else {
            $evaFrom = config('eva.from');
            $evaFromName = config('eva.from_name');
            if (!empty($evaFrom)) {
                $mail->from($evaFrom, $evaFromName ?? null);
            }
        }

        return $mail;
    }
}
