<?php
namespace Eva\Notifications;

class SlackNotifier
{
    /**
     * Envia mensagem ao Slack (webhook ou bot token). Retorna true se enviada com sucesso.
     * @param array $payload
     * @return bool
     */
    public static function send(array $payload): bool
    {
        try {
            $cfg = function_exists('config') ? config('eva.slack', []) : [];
            if (empty($cfg) || empty($cfg['enabled'])) {
                return false;
            }

            $webhook = $cfg['webhook_url'] ?? null;
            $username = $cfg['username'] ?? 'EVA';
            $icon = $cfg['icon_emoji'] ?? ':robot_face:';
            $dedupeTtl = isset($cfg['dedupe_ttl']) ? (int)$cfg['dedupe_ttl'] : 300;

            // fingerprint para dedupe
            $finger = sha1(($payload['class'] ?? '') . '|' . ($payload['file'] ?? '') . '|' . ($payload['message'] ?? ''));
            if ($dedupeTtl > 0 && function_exists('cache') && cache()->has("eva:slack:fp:$finger")) {
                return true; // já enviado recentemente
            }

            $message = self::buildMessage($payload, $username, $icon);

            $sent = false;
            if (!empty($webhook)) {
                $sent = self::sendWithWebhook($webhook, $message);
            }

            if ($sent && $dedupeTtl > 0 && function_exists('cache')) {
                cache()->put("eva:slack:fp:$finger", true, $dedupeTtl);
            }

            return (bool) $sent;
        } catch (\Throwable $ex) {
            if (function_exists('logger')) {
                logger()->error('[EVA] slack send error: ' . $ex->getMessage());
            }
            return false;
        }
    }

    protected static function buildMessage(array $p, string $username, string $icon): array
    {
        $title = ($p['module'] ?? 'App') . ' - ' . ($p['title'] ?? 'Erro');
        $short = ($p['class'] ?? '') . ': ' . ($p['message'] ?? '');
        $fields = [];
        if (!empty($p['file'])) {
            $fields[] = ['type' => 'mrkdwn', 'text' => "*Arquivo:* `{$p['file']}`:{$p['line']}"];
        }
        if (!empty($p['suggestion'])) {
            $fields[] = ['type' => 'mrkdwn', 'text' => "*Sugestão:* {$p['suggestion']}"];
        }

        // Blocks format (rich)
        $blocks = [];
        $blocks[] = ['type' => 'section', 'text' => ['type' => 'mrkdwn', 'text' => "*{$title}*\n{$short}"]];
        if (!empty($fields)) {
            $blocks[] = ['type' => 'section', 'fields' => $fields];
        }
        // optional code snippet
        if (!empty($p['code_snippet'])) {
            $blocks[] = ['type' => 'section', 'text' => ['type' => 'mrkdwn', 'text' => "```php\n{$p['code_snippet']}\n```"]];
        }
        // footer with environment/time
        $env = function_exists('config') ? (config('app.env') ?? '') : '';
        $blocks[] = ['type' => 'context', 'elements' => [['type' => 'mrkdwn', 'text' => "{$env} • " . date('Y-m-d H:i:s')]]];

        return ['text' => "{$title} - {$short}", 'blocks' => $blocks, 'username' => $username, 'icon_emoji' => $icon];
    }

    protected static function sendWithWebhook(string $url, array $message): bool
    {
        $body = json_encode(['text' => $message['text'], 'blocks' => $message['blocks']]);
        if (class_exists('\GuzzleHttp\Client')) {
            try {
                $client = new \GuzzleHttp\Client(['timeout' => 5]);
                $res = $client->post($url, ['body' => $body, 'headers' => ['Content-Type' => 'application/json']]);
                return $res->getStatusCode() >= 200 && $res->getStatusCode() < 300;
            } catch (\Throwable $_) {
                return false;
            }
        }

        $opts = ['http' => ['method' => 'POST', 'header' => "Content-Type: application/json\r\n", 'content' => $body, 'timeout' => 5]];
        $ctx = stream_context_create($opts);
        $res = @file_get_contents($url, false, $ctx);
        return $res !== false;
    }

    // bot token support removed: webhook-only notifier
}
