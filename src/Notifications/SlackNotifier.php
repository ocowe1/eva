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
        // Helper to safely get values
        $module = $p['module'] ?? 'App';
        $titleText = $p['title'] ?? 'Erro detectado';
        $exceptionClass = $p['class'] ?? '';
        $message = $p['message'] ?? '';
        $file = $p['file'] ?? null;
        $line = $p['line'] ?? null;
        $suggestion = $p['suggestion'] ?? null;
        $stack = $p['stack'] ?? null;
        $code = $p['code_snippet'] ?? ($p['code'] ?? null);
        $env = function_exists('config') ? (config('app.env') ?? '') : '';
        $timestamp = date('Y-m-d H:i:s');

        $short = trim($exceptionClass . ': ' . $message);

        // Truncate helpers
        $truncate = function ($text, $max = 700) {
            if ($text === null) return null;
            $text = trim((string)$text);
            if (mb_strlen($text) <= $max) return $text;
            return mb_substr($text, 0, $max) . "...";
        };

        $blocks = [];

        // Header with emoji and title
        $blocks[] = [
            'type' => 'section',
            'text' => [
                'type' => 'mrkdwn',
                'text' => ":warning: *{$module}* — *{$titleText}*\n*{$short}*",
            ],
        ];

        // Fields: class, file, line, environment, time
        $fields = [];
        if ($exceptionClass) $fields[] = ['type' => 'mrkdwn', 'text' => "*Classe:* `{$exceptionClass}`"];
        if ($file) $fields[] = ['type' => 'mrkdwn', 'text' => "*Arquivo:* `{$file}`"];
        if ($line) $fields[] = ['type' => 'mrkdwn', 'text' => "*Linha:* {$line}"];
        if ($env) $fields[] = ['type' => 'mrkdwn', 'text' => "*Env:* {$env}"];
        $fields[] = ['type' => 'mrkdwn', 'text' => "*Hora:* {$timestamp}"];

        if (!empty($fields)) {
            $blocks[] = ['type' => 'section', 'fields' => $fields];
        }

        // Divider
        $blocks[] = ['type' => 'divider'];

        // Suggestion block (if any)
        if ($suggestion) {
            $blocks[] = ['type' => 'section', 'text' => ['type' => 'mrkdwn', 'text' => "*Sugestão Automática:*\n{$truncate($suggestion, 900)}"]];
        }

        // Code snippet (truncate to reasonable length)
        if ($code) {
            $snippet = $truncate($code, 800);
            // ensure triple backticks inside code are escaped
            $snippet = str_replace('```', "`\`\`\`", $snippet);
            $blocks[] = ['type' => 'section', 'text' => ['type' => 'mrkdwn', 'text' => "```php\n{$snippet}\n```"]];
        }

        // Stack trace preview (first N lines)
        if ($stack) {
            $lines = preg_split('/\r\n|\n|\r/', (string)$stack);
            $preview = array_slice($lines, 0, 12);
            $previewText = implode("\n", $preview);
            $previewText = $truncate($previewText, 1200);
            $blocks[] = ['type' => 'section', 'text' => ['type' => 'mrkdwn', 'text' => "*Stack (preview):*\n```\n{$previewText}\n```"]];
        }

        // Actions: optional buttons if URLs provided
        $actions = [];
        if (!empty($p['view_url'])) {
            $actions[] = [
                'type' => 'button',
                'text' => ['type' => 'plain_text', 'text' => 'Ver (View)'],
                'url' => $p['view_url'],
            ];
        }
        if (!empty($p['issue_url'])) {
            $actions[] = [
                'type' => 'button',
                'text' => ['type' => 'plain_text', 'text' => 'Criar Issue'],
                'url' => $p['issue_url'],
            ];
        }
        if (!empty($actions)) {
            $blocks[] = ['type' => 'actions', 'elements' => $actions];
        }

        // Context footer with small info
        $contextItems = [];
        $contextItems[] = ['type' => 'mrkdwn', 'text' => "*EVA* • {$timestamp}"];
        if (!empty($p['host'])) {
            $contextItems[] = ['type' => 'mrkdwn', 'text' => "Host: {$p['host']}"];
        }
        $blocks[] = ['type' => 'context', 'elements' => $contextItems];

        // Fallback text containing all info (plain)
        $fallbackParts = [
            "Title: {$titleText}",
            "Module: {$module}",
            "Class: {$exceptionClass}",
            "Message: {$message}",
        ];
        if ($file) $fallbackParts[] = "File: {$file}:{$line}";
        if ($suggestion) $fallbackParts[] = "Suggestion: {$suggestion}";
        if ($stack) $fallbackParts[] = "Stack: " . (is_string($stack) ? substr($stack, 0, 2000) : '');

        $fallback = implode("\n", $fallbackParts);

        return ['text' => $fallback, 'blocks' => $blocks, 'username' => $username, 'icon_emoji' => $icon];
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
