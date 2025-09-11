<?php
return [
    'module' => env('EVA_MODULE', 'Aplicação'),
    'recipients' => [
        'devops@empresa.com',
        'team@empresa.com',
    ],
    'max_message_length' => 2000,
    'sync_send' => env('EVA_SYNC_SEND', true),
    'detail_level' => env('EVA_DETAIL_LEVEL', 'normal'),
];
