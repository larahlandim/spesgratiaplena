<?php
// CHECKOUT: só para quem está logado. Simula o pagamento e finaliza o pedido.
require 'includes/funcoes.php';
exigirLogin('checkout.php');

$itens = itensCarrinho();
if (!$itens) {
    flash('info', 'Seu carrinho está vazio.');
    redirecionar('index.php');
}

$formas = [
    'PIX'    => ['PIX', 'Aprovação na hora'],
    'Boleto' => ['Boleto', 'Compensação simulada imediata'],
    'Cartao' => ['Cartão de crédito', 'Pagamento à vista'],
];
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $forma = $_POST['pagamento'] ?? '';

    if (!isset($formas[$forma])) {
        $erro = 'Escolha uma forma de pagamento.';
    } else {
        $pdo = db();
        try {
            $pdo->beginTransaction(); // tudo ou nada

            // Confere o estoque de novo, travando as linhas dos produtos
            $buscar = $pdo->prepare('SELECT id, nome, preco, estoque FROM produtos WHERE id = ? AND ativo = 1 FOR UPDATE');
            $linhas = [];
            $total = 0;
            foreach (carrinho() as $produtoId => $quantidade) {
                $buscar->execute([$produtoId]);
                $p = $buscar->fetch();
                if (!$p) {
                    throw new RuntimeException('Um produto do seu carrinho não está mais disponível.');
                }
                if ($quantidade > $p['estoque']) {
                    throw new RuntimeException('Estoque insuficiente para ' . $p['nome'] . ' (disponível: ' . $p['estoque'] . ').');
                }
                $linhas[] = [$produtoId, $quantidade, $p['preco']];
                $total += $quantidade * $p['preco'];
            }

            // 1. Cria o pedido
            $pdo->prepare('INSERT INTO pedidos (usuario_id, forma_pagamento, total) VALUES (?, ?, ?)')
                ->execute([usuario()['id'], $forma, round($total, 2)]);
            $pedidoId = (int) $pdo->lastInsertId();

            // 2. Registra cada item (com o preço congelado)  3. Dá baixa no estoque
            $inserirItem = $pdo->prepare('INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)');
            $baixarEstoque = $pdo->prepare('UPDATE produtos SET estoque = estoque - ? WHERE id = ? AND estoque >= ?');
            foreach ($linhas as [$produtoId, $quantidade, $preco]) {
                $inserirItem->execute([$pedidoId, $produtoId, $quantidade, $preco]);
                $baixarEstoque->execute([$quantidade, $produtoId, $quantidade]);
                if ($baixarEstoque->rowCount() !== 1) {
                    throw new RuntimeException('O estoque mudou durante a compra. Tente de novo.');
                }
            }

            $pdo->commit();

            // 4. Limpa o carrinho
            unset($_SESSION['carrinho']);
            redirecionar('pedido_confirmado.php?id=' . $pedidoId);
        } catch (PDOException $ex) {
            // Erro do banco: desfaz tudo e não mostra detalhes técnicos
            $pdo->rollBack();
            $erro = 'Não foi possível finalizar o pedido. Tente novamente.';
        } catch (RuntimeException $ex) {
            // Problema de estoque: desfaz tudo e volta para o carrinho
            $pdo->rollBack();
            flash('warning', $ex->getMessage());
            redirecionar('carrinho.php');
        }
    }
}

$total = totalCarrinho($itens);
$titulo = 'Finalizar compra';
require 'includes/header.php';
?>

<h1 class="h3 mb-4">Finalizar compra</h1>
<?php if ($erro): ?><div class="alert alert-danger"><?= e($erro) ?></div><?php endif; ?>

<div class="row g-4">
  <div class="col-12 col-lg-7">
    <form method="post">
      <?= csrf_campo() ?>
      <h2 class="h5">Forma de pagamento</h2>
      <?php foreach ($formas as $valor => [$nome, $detalhe]): ?>
        <input type="radio" class="btn-check" name="pagamento" id="pg-<?= $valor ?>" value="<?= $valor ?>" required>
        <label class="card card-body opcao-pagamento mb-2" for="pg-<?= $valor ?>">
          <strong><?= e($nome) ?></strong>
          <small class="text-muted"><?= e($detalhe) ?> (simulação)</small>
        </label>
      <?php endforeach; ?>
      <button class="btn btn-success btn-lg w-100 mt-3">Confirmar pagamento de <?= moeda($total) ?></button>
    </form>
  </div>

  <div class="col-12 col-lg-5">
    <div class="card card-body bg-light">
      <h2 class="h5">Resumo do pedido</h2>
      <?php foreach ($itens as $item): ?>
        <div class="d-flex justify-content-between small py-1 border-bottom">
          <span><?= $item['quantidade'] ?>x <?= e($item['nome']) ?></span>
          <span><?= moeda($item['subtotal']) ?></span>
        </div>
      <?php endforeach; ?>
      <div class="d-flex justify-content-between fw-bold mt-2">
        <span>Total</span><span class="preco"><?= moeda($total) ?></span>
      </div>
      <a href="carrinho.php" class="small mt-2">Alterar carrinho</a>
    </div>
  </div>
</div>

<?php require 'includes/footer.php'; ?>
