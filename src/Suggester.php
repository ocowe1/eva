<?php
namespace Eva;

use Throwable;

class Suggester
{
    /**
     * Gera uma sugestão textual baseada na exceção e em contexto opcional.
     *
     * @param Throwable $e
     * @param array{file?:string, line?:int, stack?:string, code?:string} $context
     * @return string|null
     */
    public function suggest(Throwable $e, array $context = []): ?string
    {
        $msg = strtolower($e->getMessage() ?? '');
        $class = strtolower(get_class($e));

        $hints = [];

        // quick helpers
        $hasFile = !empty($context['file']) && file_exists($context['file']);
        $filePath = $context['file'] ?? null;
        $line = isset($context['line']) ? (int) $context['line'] : null;

        // Try to read a small window of source around the error line to provide contextual hints
        $codeWindow = null;
        if (!empty($context['code']) && is_string($context['code'])) {
            $codeWindow = $context['code'];
        } elseif ($hasFile && $line !== null) {
            try {
                $lines = @file($filePath, FILE_IGNORE_NEW_LINES);
                if (is_array($lines)) {
                    $start = max(1, $line - 5);
                    $end = min(count($lines), $line + 5);
                    $slice = array_slice($lines, $start - 1, $end - $start + 1, true);
                    $codeWindow = implode("\n", $slice);
                }
            } catch (\Throwable $_) {
                $codeWindow = null;
            }
        }

        // === Detect patterns in message/class ===
        if (str_contains($msg, 'undefined variable')) {
            $hints[] = "Variável não definida: inicialize antes do uso ou verifique o escopo. Se ocorrer em Blade, evite colisões com variáveis internas como \$message; prefira usar nomes exclusivos como `eva_payload`.";
        }

        if (str_contains($msg, 'undefined property') || str_contains($msg, 'trying to get property')) {
            $hints[] = "Propriedade indefinida: verifique se o objeto foi instanciado (não é null) e debug com `dd()` ou logs para inspecionar o conteúdo.";
        }

        if (str_contains($msg, 'undefined index') || str_contains($msg, 'offset does not exist')) {
            $hints[] = "Índice inexistente em array: proteja com `isset()`/`array_key_exists()` ou use `data_get()`/`Arr::get()` para acesso seguro.";
        }

        // === Blade / View collisions ===
        if (str_contains($msg, 'htmlspecialchars') || str_contains($msg, 'htmlentities')) {
            $hints[] = "Erro relacionado a escape HTML: verifique se está passando uma variável que não é string ou com caracteres inválidos; no Blade, use `{!! !!}` com cuidado ou garanta `e()`/`htmlspecialchars()` antes de renderizar.`";
        }

        if (str_contains($msg, 'syntax error') || str_contains($msg, 'parse error')) {
            $hints[] = "Erro de sintaxe: rode `php -l` no arquivo indicado e revise chaves/parenteses e uso de short tags.";
        }

        // === Functions / helpers ===
        if (str_contains($msg, 'call to undefined function')) {
            $hints[] = "Função inexistente: verifique se a função está definida, se há typo, ou se precisa habilitar uma extensão PHP. Em projetos Laravel, confirme se helpers estão carregados e `composer install` foi executado.";
        }

        if (str_contains($msg, 'call to a member function')) {
            // detect possible object null and provide possible root causes
            $hints[] = "Chamada de método em variável null: verifique injeção/retorno da função. Use `is_null()`/`isset()` antes de chamar, ou cheque o fluxo que deveria instanciar o objeto.";
        }

        // === Common Laravel issues ===
        if (str_contains($msg, 'view') && str_contains($msg, 'not found')) {
            $hints[] = "View não encontrada: confirme o nome/namespace (ex.: 'vendor.package.view'), veja `resources/views/vendor` se for publishable e rode `php artisan view:clear`.";
        }

        if (str_contains($msg, 'route') && str_contains($msg, 'not defined')) {
            $hints[] = "Rota não definida: verifique `routes/web.php`/`routes/api.php`, nomes de rota e middleware que possam alterar group/namespace.";
        }

        if (str_contains($msg, 'target class') && str_contains($msg, 'does not exist')) {
            $hints[] = "Target class não existe: problema de binding/autowiring. Confira namespace, `composer dump-autoload` e bindings em Service Providers.";
        }

        if (str_contains($msg, 'csrf token mismatch')) {
            $hints[] = "CSRF token mismatch: confirme se o form envia `_token` e se a sessão está funcionando (driver de sessão correto e cookies habilitados).";
        }

        // === Database / SQL ===
        if (str_contains($class, 'pdoexception') || str_contains($msg, 'sqlstate')) {
            $hints[] = "Erro de banco: revise query, bindings e credenciais. Para QueryException veja `getSql()` e `getBindings()` no catch para debugar a query real.";
        }

        if (str_contains($msg, 'duplicate entry') || str_contains($msg, 'unique constraint') ) {
            $hints[] = "Violação de unicidade: possível tentativa de inserir registro duplicado. Valide previamente com `exists()`/validação ou use `updateOrCreate()`.";
        }

        if (str_contains($msg, 'no such table') || str_contains($msg, 'base table or view not found')) {
            $hints[] = "Tabela inexistente: execute migrations no ambiente correto (`php artisan migrate`) e confira configuração de conexões/hosts.";
        }

        // === Filesystem / Permissions ===
        if (str_contains($msg, 'no such file or directory')) {
            $hints[] = "Arquivo/pasta não encontrado: verifique path absoluto/relativo e permissões. Em ambientes containerizados valide volumes/mounts.";
        }

        if (str_contains($msg, 'permission denied')) {
            $hints[] = "Permissão negada: ajuste permissões do arquivo/diretório ou usuário do processo (www-data/nginx).";
        }

        // === Network / HTTP ===
        if (str_contains($msg,'connection refused') || str_contains($msg,'could not connect')) {
            $hints[] = "Conexão recusada: verifique se o serviço remoto está ativo, porta e firewall. Teste com `curl`/`telnet` a partir do host.";
        }

        if (str_contains($msg,'could not resolve host')) {
            $hints[] = "Erro DNS: verifique nome do host, /etc/hosts e resolvers do container/host.";
        }

        // === Specific patterns in code window (context-aware hints) ===
        if ($codeWindow !== null) {
            $lowerWindow = strtolower($codeWindow);
            if (str_contains($lowerWindow, 'htmlspecialchars(') || str_contains($lowerWindow, 'htmlentities(')) {
                $hints[] = "Detectado uso de `htmlspecialchars`/`htmlentities` no trecho: confirme os parâmetros (ENT_QUOTES, charset) e que o primeiro argumento é string.";
            }
            if (str_contains($lowerWindow, " e(") || str_contains($lowerWindow, " e(")) {
                $hints[] = "Detectado uso do helper `e()` no template: em alguns contextos Blade esse helper pode colidir; use nomes de variáveis exclusivos para evitar conflito com variáveis internas.";
            }
            if (str_contains($lowerWindow, '\$message')) {
                $hints[] = "Variável `\$message` encontrada no trecho: Blade/SwiftMailer usam essa variável internamente; renomeie a variável enviada para a view para evitar sobrescrita (ex.: `eva_payload`).";
            }
            if (str_contains($lowerWindow, 'file_put_contents') || str_contains($lowerWindow, 'fopen(')) {
                $hints[] = "Operações de I/O detectadas: cheque permissões e caminhos absolutos relativos ao runtime.";
            }
        }

        // === Fallbacks e dicas práticas ===
        $hints[] = "Dica: reproduza localmente com dados mínimos, capture stack trace completo e logue o payload completo (sem dados sensíveis) para identificar origem exata.";

        // Combined message
        $message = implode(' ', array_unique($hints));
        return $message ?: null;
    }
}
