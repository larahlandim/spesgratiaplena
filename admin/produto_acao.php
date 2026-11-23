<?php
// AÇÕES DO PAINEL: ativar/desativar e excluir produto
define('BASE', '../');
require '../includes/funcoes.php';
exigirAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('index.php');
}
csrf_verificar();

$id = (int) ($_POST['id'] ?? 0);
$acao = $_POST['acao'] ?? '';

if ($acao === 'alternar') {
    db()->prepare('UPDATE produtos SET ativo = 1 - ativo WHERE id = ?')->execute([$id]);
    flash('success', 'Status do produto alterado.');
} elseif ($acao === 'excluir') {
    // Se o produto já foi vendido, só desativa (para não apagar o histórico dos pedidos)
    $stmt = db()->prepare('SELECT COUNT(*) FROM itens_pedido WHERE produto_id = ?');
    $stmt->execute([$id]);

    if ($stmt->fetchColumn() > 0) {
        db()->prepare('UPDATE produtos SET ativo = 0 WHERE id = ?')->execute([$id]);
        flash('warning', 'Este produto já foi vendido, então ele foi desativado em vez de excluído (para manter o histórico).');
    } else {
        db()->prepare('DELETE FROM produtos WHERE id = ?')->execute([$id]);
        flash('success', 'Produto excluído.');
    }
}

redirecionar('index.php');
