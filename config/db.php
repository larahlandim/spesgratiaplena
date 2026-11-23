<?php
// Conexão com o MySQL usando PDO. Os dados vêm do arquivo .env.
require_once __DIR__ . '/env.php';

function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            env('DB_HOST', 'localhost'),
            env('DB_PORT', '3306'),
            env('DB_NAME')
        );

        try {
            $pdo = new PDO($dsn, env('DB_USER', 'root'), env('DB_PASS'), [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false, // prepared statements reais
            ]);
        } catch (PDOException $e) {
            die('Erro ao conectar no banco de dados. Confira se o MySQL está ligado no XAMPP e se o .env está certo.');
        }
    }

    return $pdo;
}
