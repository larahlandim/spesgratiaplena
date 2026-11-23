<?php
// PAINEL ADMIN: lista de produtos (só administradores entram)
define('BASE', '../');
require '../includes/funcoes.php';
exigirAdmin();

$produtos = db()->query(
    'SELECT p.id, p.nome, p.preco, p.estoque, p.ativo, p.imagem, c.nome AS categoria
     FROM produtos p
     JOIN categorias c ON c.id = p.categoria_id
     ORDER BY p.ativo DESC, p.nome'
)->fetchAll();

$titulo = 'Painel admin';
require '../includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <h1 class="h3 mb-0">Painel administrativo</h1>
  <div class="d-flex gap-2">
    <a href="pedidos.php" class="btn btn-outline-primary">📦 Ver pedidos</a>
    <a href="produto_form.php" class="btn btn-success">+ Novo produto</a>
  </div>
</div>

<div class="table-responsive">
  <table class="table table-hover align-middle">
    <thead class="table-light">
      <tr><th>#</th><th>Produto</th><th>Categoria</th><th>Preço</th><th>Estoque</th><th>Status</th><th>Ações</th></tr>
    </thead>
    <tbody>
      <?php foreach ($produtos as $p): ?>
        <tr class="<?= $p['ativo'] ? '' : 'table-secondary' ?>">
          <td><?= $p['id'] ?></td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <img src="<?= imgProduto($p['imagem']) ?>" class="img-mini" alt="">
              <?= e($p['nome']) ?>
            </div>
          </td>
          <td><?= e($p['categoria']) ?></td>
          <td><?= moeda($p['preco']) ?></td>
          <td><?= $p['estoque'] ?></td>
          <td><?= $p['ativo'] ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-secondary">Desativado</span>' ?></td>
          <td>
            <div class="d-flex flex-wrap gap-1">
              <a href="produto_form.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
              <form action="produto_acao.php" method="post">
                <?= csrf_campo() ?>
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <input type="hidden" name="acao" value="alternar">
                <button class="btn btn-sm btn-outline-secondary"><?= $p['ativo'] ? 'Desativar' : 'Ativar' ?></button>
              </form>
              <form action="produto_acao.php" method="post" onsubmit="return confirm('Excluir este produto?');">
                <?= csrf_campo() ?>
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <input type="hidden" name="acao" value="excluir">
                <button class="btn btn-sm btn-outline-danger">Excluir</button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require '../includes/footer.php'; ?>
