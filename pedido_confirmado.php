<?php
// CONFIRMAÇÃO: mostra o pedido que acabou de ser feito
require 'includes/funcoes.php';
exigirLogin();

$id = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM pedidos WHERE id = ? AND usuario_id = ?');
$stmt->execute([$id, usuario()['id']]);
$pedido = $stmt->fetch();

if (!$pedido) {
    flash('danger', 'Pedido não encontrado.');
    redirecionar('index.php');
}

$stmt = db()->prepare(
    'SELECT i.quantidade, i.preco_unitario, p.nome
     FROM itens_pedido i
     JOIN produtos p ON p.id = i.produto_id
     WHERE i.pedido_id = ?'
);
$stmt->execute([$id]);
$itens = $stmt->fetchAll();

$nomesPagamento = ['PIX' => 'PIX', 'Boleto' => 'Boleto', 'Cartao' => 'Cartão de crédito'];
$titulo = 'Pedido confirmado';
require 'includes/header.php';
?>

<div class="mx-auto text-center" style="max-width: 600px;">
  <div class="display-4">✅</div>
  <h1 class="h3">Pagamento aprovado!</h1>
  <p class="text-muted">Pedido nº <?= $pedido['id'] ?> · <?= date('d/m/Y H:i', strtotime($pedido['data_pedido'])) ?> · <?= e($nomesPagamento[$pedido['forma_pagamento']] ?? $pedido['forma_pagamento']) ?></p>

  <ul class="list-group text-start my-4">
    <?php foreach ($itens as $item): ?>
      <li class="list-group-item d-flex justify-content-between">
        <span><?= $item['quantidade'] ?>x <?= e($item['nome']) ?></span>
        <span><?= moeda($item['quantidade'] * $item['preco_unitario']) ?></span>
      </li>
    <?php endforeach; ?>
    <li class="list-group-item d-flex justify-content-between fw-bold">
      <span>Total</span><span><?= moeda($pedido['total']) ?></span>
    </li>
  </ul>

  <p>"O rosário é como um bálsamo para as almas cansadas. Quem devotamente servir à Santíssima Virgem através da recitação do Rosário, incrivelmente receberá bençãos, conforme suas necessidades espirituais e temporais." Que Nossa Senhora do Rosário continue te fornecendo colo seguro. Obrigado (a) pela compra! </p>
  <a href="index.php" class="btn btn-primary">Voltar para a loja</a>
</div>

<?php require 'includes/footer.php'; ?>
