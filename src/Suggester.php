<?php
namespace Eva;

use Throwable;

class Suggester
{
    public function suggest(Throwable $e): ?string
    {
        $msg = strtolower($e->getMessage());
        $class = strtolower(get_class($e));

        $hints = [];

        if (str_contains($msg, 'undefined variable')) $hints[] = "Variável não definida: inicialize antes do uso ou cheque o escopo.";
        if (str_contains($msg, 'undefined property') || str_contains($msg, 'trying to get property')) $hints[] = "Verifique se o objeto foi instanciado antes de acessar a propriedade.";
        if (str_contains($msg, 'undefined index') || str_contains($msg, 'offset does not exist')) $hints[] = "Cheque se o índice existe no array com 'isset' ou 'array_key_exists'.";
        if (str_contains($msg,'class') && str_contains($msg,'not found')) $hints[] = "Classe não encontrada: confira namespace e rode 'composer dump-autoload'.";
        if (str_contains($msg, 'call to undefined function')) $hints[] = "Função inexistente: verifique se a extensão PHP está habilitada.";
        if (str_contains($msg, 'call to a member function')) $hints[] = "Chamada em objeto null: certifique-se de que o objeto foi instanciado.";
        if (str_contains($msg, 'must be of type')) $hints[] = "Verifique o tipo declarado e converta antes de passar o valor.";
        if (str_contains($msg, 'pdoexception') || str_contains($msg, 'sqlstate')) $hints[] = "Erro de banco: confira credenciais, query e parâmetros.";
        if (str_contains($msg, 'no such table')) $hints[] = "Tabela inexistente: rode migrations ou confira o nome.";
        if (str_contains($msg, 'duplicate entry')) $hints[] = "Violação de chave única: adicione validação antes de inserir.";
        if (str_contains($msg, 'foreign key constraint')) $hints[] = "Erro de chave estrangeira: confira integridade de dados.";
        if (str_contains($msg, 'no such file or directory')) $hints[] = "Arquivo/pasta inexistente: confira caminho ou permissões.";
        if (str_contains($msg, 'permission denied')) $hints[] = "Erro de permissão: ajuste chmod/chown.";
        if (str_contains($msg,'connection refused')) $hints[] = "Conexão recusada: verifique se o serviço está ativo e porta correta.";
        if (str_contains($msg,'could not resolve host')) $hints[] = "Erro DNS: confira host ou rede.";
        if (str_contains($msg,'timeout')) $hints[] = "Timeout: aumente limite ou otimize requisição.";
        if (str_contains($msg,'view') && str_contains($msg,'not found')) $hints[] = "View não encontrada: confirme nome/namespace da view.";
        if (str_contains($msg,'route') && str_contains($msg,'not defined')) $hints[] = "Rota inexistente: confira web.php/api.php.";
        if (str_contains($msg,'csrf token mismatch')) $hints[] = "Token CSRF inválido: confira formulários e sessão.";
        if (str_contains($msg,'session store not set')) $hints[] = "Sessão não inicializada: configure driver de session.";
        if (str_contains($msg,'no application encryption key')) $hints[] = "App key não configurada: rode 'php artisan key:generate'.";
        if (str_contains($msg,'syntax error') || str_contains($msg,'parse error')) $hints[] = "Erro de sintaxe: revise com 'php -l'.";
        if (str_contains($msg,'memory exhausted')) $hints[] = "Memória esgotada: aumente memory_limit ou otimize.";
        if (str_contains($msg,'maximum execution time')) $hints[] = "Tempo máximo excedido: aumente max_execution_time ou use jobs assíncronos.";

        return empty($hints) ? "Nenhuma sugestão automática específica. Revisar stack trace." : implode(' ', $hints);
    }
}
