<?php
// PEDIDOS: lista todos os pedidos com seus itens e total
define('BASE', '../');
require '../includes/funcoes.php';
exigirAdmin();

$pedidos = db()->query(
    'SELECT p.id, p.data_pedido, p.forma_pagamento, p.total, u.nome AS cliente, u.email
     FROM pedidos p
     JOIN usuarios u ON u.id = p.usuario_id
     ORDER BY p.data_pedido DESC, p.id DESC'
)->fetchAll();

// Busca todos os itens de uma vez e agrupa por pedido
$itensPorPedido = [];
$itens = db()->query(
    'SELECT i.pedido_id, i.quantidade, i.preco_unitario, pr.nome
     FROM itens_pedido i
     JOIN produtos pr ON pr.id = i.produto_id'
)->fetchAll();
foreach ($itens as $item) {
    $itensPorPedido[$item['pedido_id']][] = $item;
}

$nomesPagamento = ['PIX' => 'PIX', 'Boleto' => 'Boleto', 'Cartao' => 'Cartão de crédito'];
$titulo = 'Pedidos';
require '../includes/header.php';
?>

<a href="index.php" class="btn btn-link px-0 mb-3">← Voltar ao painel</a>
<h1 class="h3 mb-4">📦 Pedidos realizados (<?= count($pedidos) ?>)</h1>

<?php if (!$pedidos): ?>
  <div class="alert alert-info">Nenhum pedido ainda.</div>
<?php endif; ?>

<?php foreach ($pedidos as $p): ?>
  <div class="card mb-3 shadow-sm">
    <div class="card-header d-flex flex-wrap justify-content-between gap-2">
      <strong>Pedido nº <?= $p['id'] ?></strong>
      <span><?= date('d/m/Y H:i', strtotime($p['data_pedido'])) ?></span>
    </div>
    <div class="card-body">
      <p class="mb-2">
        <strong>Cliente:</strong> <?= e($p['cliente']) ?> (<?= e($p['email']) ?>)<br>
        <strong>Pagamento:</strong> <?= e($nomesPagamento[$p['forma_pagamento']] ?? $p['forma_pagamento']) ?>
      </p>
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead><tr><th>Produto</th><th>Qtd.</th><th>Preço unitário</th><th>Subtotal</th></tr></thead>
          <tbody>
            <?php foreach ($itensPorPedido[$p['id']] ?? [] as $item): ?>
              <tr>
                <td><?= e($item['nome']) ?></td>
                <td><?= $item['quantidade'] ?></td>
                <td><?= moeda($item['preco_unitario']) ?></td>
                <td><?= moeda($item['quantidade'] * $item['preco_unitario']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot><tr><th colspan="3" class="text-end">Total:</th><th><?= moeda($p['total']) ?></th></tr></tfoot>
        </table>
      </div>
    </div>
  </div>
<?php endforeach; ?>

<?php require '../includes/footer.php'; ?>
