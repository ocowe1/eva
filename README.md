# EVA

Biblioteca de integração com o EVA AI para monitoramento, relatórios de erros e insights em tempo real em projetos Laravel.

## Instalação

Adicione via Composer:

```shell
composer require ocowe1/eva
```

## Publicação de Configuração e Views

Após instalar, publique os arquivos de configuração e views:

```shell
php artisan vendor:publish --provider="Eva\EvaServiceProvider" --tag=config
php artisan vendor:publish --provider="Eva\EvaServiceProvider" --tag=views
```

## Configuração

Edite `config/eva.php` conforme sua necessidade:
- Recipients (destinatários)
- Módulo
- E-mail remetente
- Nível de detalhe
- Sincronia/envio

## Uso Básico

Dispare um alerta de exceção:

```php
use Eva\Mail\EvaAlertMailable;
use Illuminate\Support\Facades\Mail;

try {
    // ...código que pode falhar...
} catch (\Throwable $e) {
    $payload = [
        'title' => 'Erro Crítico',
        'class' => get_class($e),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'stack' => $e->getTraceAsString(),
        'suggestion' => (new Eva\Suggester())->suggest($e, [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'stack' => $e->getTraceAsString(),
        ]),
    ];
    Mail::to(config('eva.recipients'))->send(new EvaAlertMailable($payload));
}
```

## Preview do E-mail

Abra `docs/preview.html` no navegador para visualizar o layout do e-mail gerado.

## Sugestões Automáticas

A classe `Eva\Suggester` analisa a exceção e contexto, retornando dicas automáticas para resolução do erro.

## Changelog

- 1.0.5: Preview moderno, heurísticas ampliadas no Suggester, highlight.js no preview
- 1.0.4: Correção de colisão Blade, layout do e-mail aprimorado
- 1.0.3: Configuração e publish tags melhorados
- 1.0.2: Versão inicial

## Contribuição

Pull requests são bem-vindos! Para reportar bugs ou sugerir melhorias, abra uma issue.

## Licença

MIT

## Microsoft Teams (Incoming Webhook)

Para enviar alertas também para um canal do Microsoft Teams, configure as variáveis de ambiente no seu `.env`:

```dotenv
EVA_TEAMS_ENABLED=true
EVA_TEAMS_WEBHOOK=https://outlook.office.com/webhook/....
EVA_TEAMS_TITLE="EVA Alert"
```

Quando `EVA_TEAMS_ENABLED` estiver `true` e a webhook definida, o pacote tentará enviar um cartão simples para o Teams com o resumo do erro e a sugestão automática.

Observação: por padrão o pacote usa `file_get_contents` para enviar o POST JSON; se sua aplicação usa `guzzlehttp/guzzle` (recomendado) ele fará uso do Guzzle quando disponível.

## Slack (Incoming Webhook)

Para enviar alertas ao Slack usamos Incoming Webhooks (URL no formato `https://hooks.slack.com/services/...`).

Adicione no seu `.env`:

```dotenv
EVA_SLACK_ENABLED=true
EVA_SLACK_WEBHOOK=https://hooks.slack.com/services/AAA/BBB/CCC
EVA_SLACK_USERNAME=Eva
EVA_SLACK_ICON_EMOJI=":robot_face:"
# Opcional: URL pública para um SVG/PNG personalizado (ex.: raw GitHub URL ou CDN)
EVA_SLACK_ICON_URL=https://raw.githubusercontent.com/ocowe1/eva/master/resources/assets/eva-icon.svg
EVA_SLACK_DEDUPE_TTL=300
```

O pacote inclui um `SlackNotifier` que formata mensagens com Blocks (título, resumo, campos e snippet) e um Job `SendSlackNotification` para enfileirar envios (recomendado). Por padrão o envio é assíncrono quando `eva.sync_send` estiver `false`.

Teste rápido com curl (webhook):

```bash
curl -X POST -H 'Content-type: application/json' \
    --data '{"text":"Teste: webhook do Slack configurado com sucesso"}' \
    "https://hooks.slack.com/services/AAA/BBB/CCC"
```

Notas sobre o ícone personalizado:
- Hospede o SVG em um local público (GitHub raw, CDN, S3) e aponte `EVA_SLACK_ICON_URL` para esse recurso.
- O projeto inclui um ícone `resources/assets/eva-icon.svg` que você pode hospedar e usar.

Recomenda-se usar filas + Redis para dedupe e retry em produção.
