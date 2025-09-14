<?php
// Simple script to test Slack webhook notifier
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
} else {
    require_once __DIR__ . '/../src/Notifications/SlackNotifier.php';
}

use Eva\Notifications\SlackNotifier;

// If we're running outside Laravel, provide a minimal `config()` helper so
// SlackNotifier::send() can read `eva.slack` from env var `EVA_SLACK_WEBHOOK`.
if (!function_exists('config')) {
    function config($key = null, $default = null)
    {
        if ($key === 'eva.slack') {
            $webhook = getenv('EVA_SLACK_WEBHOOK') ?: null;
            return ['enabled' => !empty($webhook), 'webhook_url' => $webhook];
        }
        if ($key === 'app.env') {
            return getenv('APP_ENV') ?: 'local';
        }
        return $default;
    }
}

$payload = [
    'title' => 'Teste de Integração - EVA',
    'module' => 'Backend',
    'class' => 'RuntimeException',
    'message' => 'Teste enviado pelo script de exemplo',
    'file' => __FILE__,
    'line' => __LINE__,
    'suggestion' => 'Verifique logs e reproduza localmente',
    'code_snippet' => "function test(){\n  return true;\n}",
    'stack' => "#0 /path/to/file.php(123): foo()\n#1 {main}",
    'view_url' => null,
    'issue_url' => null,
    'host' => gethostname(),
];

$res = SlackNotifier::send($payload);
echo $res ? "Slack: enviado\n" : "Slack: falha ao enviar\n";
