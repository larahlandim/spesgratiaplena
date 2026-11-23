<?php
// PÁGINA DO PRODUTO: código, preço, descrição, imagem e estoque
require 'includes/funcoes.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare(
    'SELECT p.*, c.nome AS categoria
     FROM produtos p
     JOIN categorias c ON c.id = p.categoria_id
     WHERE p.id = ? AND p.ativo = 1'
);
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) {
    flash('danger', 'Produto não encontrado.');
    redirecionar('index.php');
}

$titulo = $p['nome'];
require 'includes/header.php';
?>

<a href="index.php" class="btn btn-link px-0 mb-3">← Voltar para a loja</a>

<div class="row g-4 align-items-start">
  <div class="col-12 col-md-5">
    <img src="<?= imgProduto($p['imagem']) ?>" class="img-produto" alt="<?= e($p['nome']) ?>">
  </div>

  <div class="col-12 col-md-7">
    <span class="badge bg-light text-dark"><?= e($p['categoria']) ?></span>
    <h1 class="h2 mt-2"><?= e($p['nome']) ?></h1>
    <p class="text-muted small">Código do produto: #<?= str_pad((string) $p['id'], 4, '0', STR_PAD_LEFT) ?></p>
    <p class="preco fs-3"><?= moeda($p['preco']) ?> <small class="fs-6 text-muted">cada</small></p>

    <p>
      <?php if ($p['estoque'] > 5): ?>
        <span class="badge bg-success">Em estoque: <?= $p['estoque'] ?> unidades</span>
      <?php elseif ($p['estoque'] > 0): ?>
        <span class="badge bg-warning text-dark">Últimas <?= $p['estoque'] ?> unidades!</span>
      <?php else: ?>
        <span class="badge bg-danger">Esgotado</span>
      <?php endif; ?>
    </p>

    <p><?= nl2br(e($p['descricao'])) ?></p>

    <?php if ($p['estoque'] > 0): ?>
      <form action="carrinho.php" method="post" class="row g-2 mt-3">
        <?= csrf_campo() ?>
        <input type="hidden" name="acao" value="adicionar">
        <input type="hidden" name="id" value="<?= $p['id'] ?>">
        <div class="col-4 col-sm-3">
          <input type="number" name="quantidade" value="1" min="1" max="<?= $p['estoque'] ?>" class="form-control" aria-label="Quantidade">
        </div>
        <div class="col-8 col-sm-6 d-grid">
          <button class="btn btn-primary">🛒 Adicionar ao carrinho</button>
        </div>
      </form>
    <?php endif; ?>
  </div>
</div>

<?php require 'includes/footer.php'; ?>
