<?php

/*
 |--------------------------------------------------------------------------
 | EVA - Configurações
 |--------------------------------------------------------------------------
 |
 | Aqui você pode configurar o comportamento do pacote EVA.
 | - `module`: nome do módulo/aplicação que aparecerá no assunto
 | - `recipients`: lista de e-mails que receberão os alertas
 | - `max_message_length`: limita o tamanho da mensagem de exceção
 | - `sync_send`: se true, envia synchronousamente com Mail::send, senão usa queue()
 | - `detail_level`: 'minimal' | 'normal' | 'full' (controla stack trace enviado)
 |
 */

return [
    // Nome do módulo/serviço (aparecerá no assunto do e-mail)
    'module' => env('EVA_MODULE', 'Aplicação'),

    // Destinatários do alerta. Deixe vazio para desabilitar envio automático.
    'recipients' => array_filter([
        env('EVA_RECIPIENT_1'),
        env('EVA_RECIPIENT_2'),
    ]),

    // Caso queira forçar um remetente específico para os e-mails do EVA,
    // defina aqui. Caso contrário, o valor de `mail.from` do Laravel será usado.
    'from' => env('EVA_FROM', null),
    'from_name' => env('EVA_FROM_NAME', null),

    // Limite de caracteres para mensagens longas
    'max_message_length' => (int) env('EVA_MAX_MESSAGE_LENGTH', 2000),

    // Enviar sincronamente (true) ou enfileirar (false) os e-mails
    'sync_send' => (bool) env('EVA_SYNC_SEND', true),

    // Nível de detalhe do stack trace: 'minimal' | 'normal' | 'full'
    'detail_level' => env('EVA_DETAIL_LEVEL', 'normal'),
    // Notificações via Microsoft Teams (Webhook)
    'teams' => [
        // Habilitar envio para Teams
        'enabled' => (bool) env('EVA_TEAMS_ENABLED', false),
        // Webhook URL do canal do Teams
        'webhook_url' => env('EVA_TEAMS_WEBHOOK', null),
        // Título do cartão
        'title' => env('EVA_TEAMS_TITLE', 'EVA Alert'),
    ],
    // Notificações via Slack (Incoming Webhook ou Bot)
    'slack' => [
        'enabled' => (bool) env('EVA_SLACK_ENABLED', false),
        // Incoming Webhook URL (hooks.slack.com/services/...)
        'webhook_url' => env('EVA_SLACK_WEBHOOK', null),
        'username' => env('EVA_SLACK_USERNAME', 'EVA'),
            // allow an URL for a custom icon (preferred) or an emoji fallback
            'icon_url' => env('EVA_SLACK_ICON_URL', null),
            'icon_emoji' => env('EVA_SLACK_ICON_EMOJI', ':robot_face:'),
        // Deduplication TTL (segundos) para evitar spam
        'dedupe_ttl' => (int) env('EVA_SLACK_DEDUPE_TTL', 300),
    ],
];
