<?php
namespace Eva;

use Throwable;
use Eva\Mail\EvaAlertMailable;
use Illuminate\Support\Facades\Mail;

class Eva
{
    protected array $config;
    protected Suggester $suggester;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->suggester = new Suggester();
    }

    public function capture(Throwable $e, array $context = [])
    {
        $module = $this->config['module'] ?? 'Aplicação';
        $title = "[EVA] Alerta de Erro - {$module}";

        $file = $e->getFile();
        $line = $e->getLine();
        $class = get_class($e);
        $message = $e->getMessage();

        $shortMessage = $this->shorten($message, $this->config['max_message_length'] ?? 2000);
        $suggestion = $this->suggester->suggest($e);

        $payload = [
            'title' => $title,
            'module' => $module,
            'file' => $file,
            'line' => $line,
            'class' => $class,
            'message' => $shortMessage,
            'stack' => $this->formatStack($e),
            'suggestion' => $suggestion,
            'context' => $context,
        ];

        $recipients = $this->config['recipients'] ?? [];
        if (empty($recipients)) return false;

        try {
            $mailable = new EvaAlertMailable($payload);
            foreach ($recipients as $to) {
                if ($this->config['sync_send'] ?? true) {
                    Mail::to($to)->send($mailable);
                } else {
                    Mail::to($to)->queue($mailable);
                }
            }
            return true;
        } catch (\Throwable $mailEx) {
            logger()->error('[EVA] falha ao enviar alerta: '.$mailEx->getMessage());
            return false;
        }
    }

    protected function shorten(string $text, int $max): string
    {
        $text = trim($text);
        return strlen($text) <= $max ? $text : mb_substr($text,0,$max) . '...';
    }

    protected function formatStack(Throwable $e): string
    {
        if (($this->config['detail_level'] ?? 'normal') === 'minimal') return '';
        return $e->getTraceAsString();
    }
}
