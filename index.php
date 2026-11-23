<?php
// VITRINE: lista os produtos do banco, com busca e filtro por categoria
require 'includes/funcoes.php';

$busca = trim($_GET['busca'] ?? '');
$categoriaId = (int) ($_GET['categoria'] ?? 0);

$categorias = db()->query('SELECT id, nome FROM categorias ORDER BY nome')->fetchAll();

$sql = 'SELECT p.id, p.nome, p.preco, p.estoque, p.imagem, c.nome AS categoria
        FROM produtos p
        JOIN categorias c ON c.id = p.categoria_id
        WHERE p.ativo = 1';
$parametros = [];

if ($busca !== '') {
    $sql .= ' AND (p.nome LIKE ? OR p.descricao LIKE ?)';
    $parametros[] = "%$busca%";
    $parametros[] = "%$busca%";
}
if ($categoriaId > 0) {
    $sql .= ' AND p.categoria_id = ?';
    $parametros[] = $categoriaId;
}
$sql .= ' ORDER BY p.nome';

$stmt = db()->prepare($sql);
$stmt->execute($parametros);
$produtos = $stmt->fetchAll();

$titulo = 'Loja';
require 'includes/header.php';
?>

<section class="vitrine-topo mb-4">
  <h1 class="visually-hidden">Spes | Gratia Plena</h1>
  <img src="assets/img/logo.png" alt="Spes | Gratia Plena" class="mb-3">
  <p class="mb-0">Ad Maiorem Dei Gloriam.</p>
</section>

<form method="get" class="row g-2 mb-4">
  <div class="col-12 col-md-6">
    <input type="search" name="busca" class="form-control" placeholder="Buscar terço..." value="<?= e($busca) ?>">
  </div>
  <div class="col-8 col-md-4">
    <select name="categoria" class="form-select">
      <option value="0">Todas as categorias</option>
      <?php foreach ($categorias as $c): ?>
        <option value="<?= $c['id'] ?>" <?= (int) $c['id'] === $categoriaId ? 'selected' : '' ?>><?= e($c['nome']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-4 col-md-2 d-grid">
    <button class="btn btn-primary">Filtrar</button>
  </div>
</form>

<?php if (!$produtos): ?>
  <div class="alert alert-info">Nenhum terço encontrado. <a href="index.php">Ver todos</a></div>
<?php endif; ?>

<div class="row g-4">
  <?php foreach ($produtos as $p): ?>
    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
      <div class="card h-100 card-produto shadow-sm">
        <img src="<?= imgProduto($p['imagem']) ?>" class="card-img-top" alt="<?= e($p['nome']) ?>">
        <div class="card-body d-flex flex-column">
          <span class="badge bg-light text-dark mb-2 align-self-start"><?= e($p['categoria']) ?></span>
          <h2 class="h6"><?= e($p['nome']) ?></h2>
          <p class="preco mb-1"><?= moeda($p['preco']) ?></p>
          <p class="small mb-3">
            <?php if ($p['estoque'] > 0): ?>
              <span class="text-success">● Em estoque</span>
            <?php else: ?>
              <span class="text-danger">● Esgotado</span>
            <?php endif; ?>
          </p>
          <a href="produto.php?id=<?= $p['id'] ?>" class="btn btn-outline-primary mt-auto">Ver detalhes</a>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php require 'includes/footer.php'; ?>
