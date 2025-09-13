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
