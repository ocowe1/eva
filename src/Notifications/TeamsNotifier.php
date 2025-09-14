<?php
namespace Eva\Notifications;

class TeamsNotifier
{
    /**
     * Envia um alerta para o Microsoft Teams via Incoming Webhook
     * @param array $payload - array com title, class, message, file, line, suggestion
     * @return bool
     */
    public static function send(array $payload): bool
    {
        $config = function_exists('config') ? config('eva.teams', []) : [];
        if (empty($config) || empty($config['enabled']) || empty($config['webhook_url'])) {
            return false;
        }

        $webhook = $config['webhook_url'];
        $title = $config['title'] ?? ($payload['title'] ?? 'EVA Alert');

        // Monta o card simples em formato MessageCard (compatível com muitos webhooks do Teams)
        $sections = [];
        $text = "**" . ($payload['class'] ?? '') . "**\n" . ($payload['message'] ?? '');
        if (!empty($payload['file'])) {
            $text .= "\nFile: " . $payload['file'] . ":" . ($payload['line'] ?? '?');
        }
        if (!empty($payload['suggestion'])) {
            $text .= "\n\nSuggestion:\n" . $payload['suggestion'];
        }

        $body = [
            '@type' => 'MessageCard',
            '@context' => 'http://schema.org/extensions',
            'summary' => $title,
            // use Eva primary color (without #, Teams expects hex RGB)
            'themeColor' => 'd6b006',
            'title' => $title,
            'sections' => [
                [
                    'activityTitle' => $payload['title'] ?? '',
                    'text' => $text,
                ]
            ]
        ];

        $json = json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // Use Guzzle se estiver disponível
        if (class_exists('\GuzzleHttp\Client')) {
            try {
                $client = new \GuzzleHttp\Client(['timeout' => 5]);
                $res = $client->post($webhook, [
                    'headers' => ['Content-Type' => 'application/json'],
                    'body' => $json,
                ]);
                return $res->getStatusCode() >= 200 && $res->getStatusCode() < 300;
            } catch (\Throwable $_) {
                return false;
            }
        }

        // fallback: file_get_contents com stream context
        try {
            $opts = [
                'http' => [
                    'method'  => 'POST',
                    'header'  => "Content-Type: application/json\r\n",
                    'content' => $json,
                    'timeout' => 5,
                ]
            ];
            $context = stream_context_create($opts);
            $result = @file_get_contents($webhook, false, $context);
            return $result !== false;
        } catch (\Throwable $_) {
            return false;
        }
    }
}
