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

        // === Erros de variável/array ===
        if (str_contains($msg, 'undefined variable')) {
            $hints[] = "Variável não definida: inicialize antes do uso ou cheque o escopo.";
        }
        if (str_contains($msg, 'undefined property') || str_contains($msg, 'trying to get property')) {
            $hints[] = "Verifique se o objeto foi instanciado antes de acessar a propriedade.";
        }
        if (str_contains($msg, 'undefined index') || str_contains($msg, 'offset does not exist')) {
            $hints[] = "Cheque se o índice existe no array com 'isset' ou 'array_key_exists'.";
        }

        // === Classes / Autoload ===
        if (str_contains($msg,'class') && str_contains($msg,'not found')) {
            $hints[] = "Classe não encontrada: confira namespace e rode 'composer dump-autoload'.";
        }
        if (str_contains($msg, 'trait') && str_contains($msg,'not found')) {
            $hints[] = "Trait não encontrada: confirme namespace e autoload.";
        }
        if (str_contains($msg,'interface') && str_contains($msg,'not found')) {
            $hints[] = "Interface não encontrada: valide autoload e dependências.";
        }

        // === Funções / Métodos ===
        if (str_contains($msg, 'call to undefined function')) {
            $hints[] = "Função inexistente: verifique se a extensão PHP está habilitada ou se a função existe.";
        }
        if (str_contains($msg, 'call to a member function')) {
            $hints[] = "Chamada em objeto null: certifique-se de que o objeto foi instanciado.";
        }
        if (str_contains($msg, 'argument')) {
            $hints[] = "Quantidade de argumentos incorreta: ajuste parâmetros na chamada.";
        }

        // === Tipos ===
        if (str_contains($msg, 'must be of type')) {
            $hints[] = "Verifique o tipo declarado e converta antes de passar o valor.";
        }
        if (str_contains($msg, 'type error')) {
            $hints[] = "Erro de tipo: ajuste a tipagem ou sanitize os dados antes.";
        }

        // === Banco de dados ===
        if (str_contains($class, 'pdoexception') || str_contains($msg, 'sqlstate')) {
            $hints[] = "Erro de banco: confira credenciais, query e parâmetros.";
        }
        if (str_contains($msg, 'syntax error at or near')) {
            $hints[] = "Erro SQL: revise sintaxe da query, aspas e vírgulas.";
        }
        if (str_contains($msg, 'no such table')) {
            $hints[] = "Tabela inexistente: rode migrations ou confira o nome.";
        }
        if (str_contains($msg, 'column not found')) {
            $hints[] = "Coluna não existe: ajuste migrations ou query.";
        }
        if (str_contains($msg, 'duplicate entry')) {
            $hints[] = "Violação de chave única: adicione validação antes de inserir.";
        }
        if (str_contains($msg, 'foreign key constraint')) {
            $hints[] = "Erro de chave estrangeira: confira integridade de dados e ordem de inserts.";
        }

        // === Filesystem ===
        if (str_contains($msg, 'no such file or directory')) {
            $hints[] = "Arquivo/pasta inexistente: confira caminho ou permissões.";
        }
        if (str_contains($msg, 'permission denied')) {
            $hints[] = "Erro de permissão: ajuste chmod/chown do arquivo/pasta.";
        }
        if (str_contains($msg, 'failed to open stream')) {
            $hints[] = "Falha ao abrir arquivo: verifique path, permissões e se o arquivo existe.";
        }

        // === Rede / API ===
        if (str_contains($msg,'connection refused')) {
            $hints[] = "Conexão recusada: verifique se o serviço destino está ativo e porta correta.";
        }
        if (str_contains($msg,'could not resolve host')) {
            $hints[] = "Erro DNS: confira nome do host ou conectividade de rede.";
        }
        if (str_contains($msg,'timeout')) {
            $hints[] = "Timeout: aumente limite ou otimize a requisição.";
        }

        // === Laravel específicos ===
        if (str_contains($msg, 'view') && str_contains($msg, 'not found')) {
            $hints[] = "View não encontrada: confirme nome/namespace da view.";
        }
        if (str_contains($msg, 'route') && str_contains($msg, 'not defined')) {
            $hints[] = "Rota inexistente: confira web.php/api.php e nome do route().";
        }
        if (str_contains($msg, 'target class') && str_contains($msg, 'does not exist')) {
            $hints[] = "Binding de classe não existe: confira Service Providers.";
        }
        if (str_contains($msg, 'class') && str_contains($msg, 'does not exist')) {
            $hints[] = "Classe referida no container não existe: ajuste namespace.";
        }
        if (str_contains($msg,'csrf token mismatch')) {
            $hints[] = "Token CSRF inválido: confira formulários e sessão.";
        }
        if (str_contains($msg,'session store not set')) {
            $hints[] = "Sessão não inicializada: configure driver de session no .env.";
        }
        if (str_contains($msg,'no application encryption key')) {
            $hints[] = "App key não configurada: rode 'php artisan key:generate'.";
        }
        if (str_contains($msg,'access denied for user')) {
            $hints[] = "Erro MySQL: credenciais incorretas no .env.";
        }
        if (str_contains($msg,'migrate') && str_contains($msg,'base table or view not found')) {
            $hints[] = "Banco não migrado: rode 'php artisan migrate'.";
        }

        // === PHP básico ===
        if (str_contains($msg,'syntax error') || str_contains($msg,'parse error')) {
            $hints[] = "Erro de sintaxe: revise o arquivo com 'php -l'.";
        }
        if (str_contains($msg,'memory exhausted')) {
            $hints[] = "Memória esgotada: aumente memory_limit ou otimize o código.";
        }
        if (str_contains($msg,'maximum execution time')) {
            $hints[] = "Tempo máximo excedido: aumente max_execution_time ou use jobs assíncronos.";
        }

        if (empty($hints)) {
            return "Nenhuma sugestão automática específica. Revisar stack trace, reproduzir localmente e verificar dependências.";
        }

        return implode(' ', $hints);
    }
}
