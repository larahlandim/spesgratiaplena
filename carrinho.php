<?php
// CARRINHO: adicionar, alterar quantidade, remover e calcular totais (tudo em $_SESSION)
require 'includes/funcoes.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();

    $acao = $_POST['acao'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);
    $carrinho = carrinho();

    if ($acao === 'adicionar' || $acao === 'atualizar') {
        $stmt = db()->prepare('SELECT nome, estoque FROM produtos WHERE id = ? AND ativo = 1');
        $stmt->execute([$id]);
        $produto = $stmt->fetch();

        if (!$produto) {
            flash('danger', 'Produto não encontrado.');
        } elseif ($produto['estoque'] <= 0) {
            unset($carrinho[$id]);
            flash('danger', $produto['nome'] . ' está esgotado.');
        } else {
            $quantidade = max(1, (int) ($_POST['quantidade'] ?? 1));
            $nova = $acao === 'adicionar' ? ($carrinho[$id] ?? 0) + $quantidade : $quantidade;

            // Validação: não pode passar do estoque
            if ($nova > $produto['estoque']) {
                $nova = $produto['estoque'];
                flash('warning', 'Só temos ' . $produto['estoque'] . ' unidade(s) de ' . $produto['nome'] . '. A quantidade foi ajustada.');
            } else {
                flash('success', $acao === 'adicionar' ? $produto['nome'] . ' foi adicionado ao carrinho.' : 'Quantidade atualizada.');
            }
            $carrinho[$id] = $nova;
        }
    } elseif ($acao === 'remover') {
        unset($carrinho[$id]);
        flash('info', 'Item removido do carrinho.');
    }

    $_SESSION['carrinho'] = $carrinho;
    redirecionar('carrinho.php');
}

$itens = itensCarrinho();
$total = totalCarrinho($itens);

$titulo = 'Carrinho';
require 'includes/header.php';
?>

<h1 class="h3 mb-4">🛒 Meu carrinho</h1>

<?php if (!$itens): ?>
  <div class="alert alert-info">Seu carrinho está vazio. <a href="index.php">Ver terços</a></div>
<?php else: ?>
  <div class="table-responsive">
    <table class="table align-middle">
      <thead>
        <tr><th>Produto</th><th>Preço</th><th style="width: 190px">Quantidade</th><th>Subtotal</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($itens as $item): ?>
          <tr>
            <td>
              <div class="d-flex align-items-center gap-2">
                <img src="<?= imgProduto($item['imagem']) ?>" class="img-mini" alt="">
                <a href="produto.php?id=<?= $item['id'] ?>"><?= e($item['nome']) ?></a>
              </div>
              <?php if ($item['quantidade'] > $item['estoque']): ?>
                <small class="text-danger">Só restam <?= $item['estoque'] ?> no estoque. Atualize a quantidade.</small>
              <?php endif; ?>
            </td>
            <td><?= moeda($item['preco']) ?></td>
            <td>
              <form method="post" class="d-flex gap-1">
                <?= csrf_campo() ?>
                <input type="hidden" name="acao" value="atualizar">
                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                <input type="number" name="quantidade" value="<?= $item['quantidade'] ?>" min="1" max="<?= $item['estoque'] ?>" class="form-control form-control-sm" aria-label="Quantidade">
                <button class="btn btn-sm btn-outline-primary">Atualizar</button>
              </form>
            </td>
            <td class="fw-semibold"><?= moeda($item['subtotal']) ?></td>
            <td>
              <form method="post">
                <?= csrf_campo() ?>
                <input type="hidden" name="acao" value="remover">
                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                <button class="btn btn-sm btn-outline-danger">Remover</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr><th colspan="3" class="text-end">Total geral:</th><th class="preco"><?= moeda($total) ?></th><th></th></tr>
      </tfoot>
    </table>
  </div>

  <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
    <a href="index.php" class="btn btn-outline-secondary">Continuar comprando</a>
    <a href="checkout.php" class="btn btn-success">Finalizar compra →</a>
  </div>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>
