<?php
// Lê o arquivo .env (linhas no formato NOME=valor) e guarda os valores em $_ENV.
// Assim, usuário e senha do banco NÃO ficam escritos dentro do código.

function carregarEnv(string $caminho): void
{
    if (!is_file($caminho)) {
        die('Arquivo .env não encontrado. Copie o .env.example, renomeie para .env e preencha os dados.');
    }

    foreach (file($caminho, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linha) {
        $linha = trim($linha);
        if ($linha === '' || str_starts_with($linha, '#') || !str_contains($linha, '=')) {
            continue;
        }
        [$nome, $valor] = explode('=', $linha, 2);
        $_ENV[trim($nome)] = trim(trim($valor), "\"'");
    }
}

function env(string $nome, string $padrao = ''): string
{
    return $_ENV[$nome] ?? $padrao;
}

carregarEnv(__DIR__ . '/../.env');
