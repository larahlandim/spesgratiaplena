<?php
// Funções usadas em todas as páginas da loja.
require_once __DIR__ . '/../config/db.php';

// BASE = caminho até a raiz da loja. Páginas dentro de /admin definem BASE como '../'.
if (!defined('BASE')) {
    define('BASE', '');
}

// Inicia a sessão (guarda login e carrinho)
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

// ---------- Ajudantes ----------

// Escapa texto antes de mostrar no HTML (proteção contra XSS)
function e($texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

function moeda($valor): string
{
    return 'R$ ' . number_format((float) $valor, 2, ',', '.');
}

function redirecionar(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function imgProduto(?string $arquivo): string
{
    return BASE . 'assets/img/produtos/' . rawurlencode($arquivo ?: 'sem-imagem.svg');
}

// Mensagens que aparecem uma vez no topo da página
function flash(string $tipo, string $mensagem): void
{
    $_SESSION['flash'][] = [$tipo, $mensagem];
}

function mostrarFlash(): void
{
    foreach ($_SESSION['flash'] ?? [] as [$tipo, $mensagem]) {
        echo '<div class="alert alert-' . e($tipo) . '">' . e($mensagem) . '</div>';
    }
    unset($_SESSION['flash']);
}

// ---------- Proteção CSRF (formulários só valem se vierem do próprio site) ----------

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_campo(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function csrf_verificar(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(400);
        die('Requisição inválida. Volte e tente de novo.');
    }
}

// ---------- Login e controle de acesso ----------

function usuario(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

function exigirLogin(string $voltarPara = 'index.php'): void
{
    if (!usuario()) {
        flash('warning', 'Entre na sua conta para continuar.');
        redirecionar(BASE . 'login.php?voltar=' . urlencode($voltarPara));
    }
}

// Validação feita no SERVIDOR: só admin passa daqui
function exigirAdmin(): void
{
    if (!usuario()) {
        flash('warning', 'Entre com uma conta de administrador.');
        redirecionar(BASE . 'login.php');
    }
    if (usuario()['perfil'] !== 'admin') {
        http_response_code(403);
        die('Acesso negado: área exclusiva para administradores.');
    }
}

// ---------- Carrinho (guardado em $_SESSION['carrinho'] no formato [id_produto => quantidade]) ----------

function carrinho(): array
{
    return $_SESSION['carrinho'] ?? [];
}

function qtdCarrinho(): int
{
    return array_sum(carrinho());
}

// Busca no banco os produtos do carrinho e calcula o subtotal de cada um
function itensCarrinho(): array
{
    $carrinho = carrinho();
    if (!$carrinho) {
        return [];
    }

    $ids = array_keys($carrinho);
    $marcadores = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare("SELECT id, nome, preco, estoque, imagem FROM produtos WHERE ativo = 1 AND id IN ($marcadores)");
    $stmt->execute($ids);

    $itens = [];
    foreach ($stmt as $p) {
        $p['quantidade'] = $carrinho[$p['id']];
        $p['subtotal'] = $p['quantidade'] * $p['preco'];
        $itens[] = $p;
    }
    return $itens;
}

function totalCarrinho(array $itens): float
{
    return array_sum(array_column($itens, 'subtotal'));
}
