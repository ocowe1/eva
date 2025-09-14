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
            ->with(['eva_payload' => $this->payload]);

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

        // Tentativa de notificar via Teams se estiver configurado (não deve quebrar envio de e-mail)
        try {
            if (function_exists('config') && config('eva.teams.enabled')) {
                \Eva\Notifications\TeamsNotifier::send($this->payload);
            }
        } catch (\Throwable $_) {
            // ignorar falhas no envio para Teams
        }

        // Notificar via Slack (webhook/bot) se habilitado
        try {
            if (function_exists('config') && config('eva.slack.enabled')) {
                if (function_exists('config') && config('eva.sync_send')) {
                    // envio síncrono
                    \Eva\Notifications\SlackNotifier::send($this->payload);
                } else {
                    // enfileirar para evitar blocking
                    if (class_exists('\Eva\Jobs\SendSlackNotification')) {
                        \Eva\Jobs\SendSlackNotification::dispatch($this->payload);
                    } else {
                        \Eva\Notifications\SlackNotifier::send($this->payload);
                    }
                }
            }
        } catch (\Throwable $_) {
            // ignorar falhas na notificação Slack
        }

        return $mail;
    }
}
