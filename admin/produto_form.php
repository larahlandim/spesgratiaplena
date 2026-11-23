<?php
// CADASTRAR ou EDITAR produto
define('BASE', '../');
require '../includes/funcoes.php';
exigirAdmin();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$categorias = db()->query('SELECT id, nome FROM categorias ORDER BY nome')->fetchAll();

$produto = ['nome' => '', 'categoria_id' => 0, 'descricao' => '', 'preco' => '', 'estoque' => 0, 'imagem' => '', 'ativo' => 1];
if ($id > 0) {
    $stmt = db()->prepare('SELECT * FROM produtos WHERE id = ?');
    $stmt->execute([$id]);
    $produto = $stmt->fetch();
    if (!$produto) {
        flash('danger', 'Produto não encontrado.');
        redirecionar('index.php');
    }
}

$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();

    $produto['nome'] = trim($_POST['nome'] ?? '');
    $produto['categoria_id'] = (int) ($_POST['categoria_id'] ?? 0);
    $produto['descricao'] = trim($_POST['descricao'] ?? '');
    $produto['preco'] = str_replace(',', '.', trim($_POST['preco'] ?? ''));
    $produto['estoque'] = (int) ($_POST['estoque'] ?? 0);
    $produto['ativo'] = isset($_POST['ativo']) ? 1 : 0;

    if ($produto['nome'] === '') $erros[] = 'Digite o nome do produto.';
    if (!in_array($produto['categoria_id'], array_map('intval', array_column($categorias, 'id')), true)) $erros[] = 'Escolha uma categoria.';
    if (!is_numeric($produto['preco']) || $produto['preco'] <= 0) $erros[] = 'Digite um preço válido (ex.: 29,90).';
    if ($produto['estoque'] < 0) $erros[] = 'O estoque não pode ser negativo.';

    // Upload da imagem (opcional): só JPG, PNG ou WEBP de até 2 MB
    if (!$erros && ($_FILES['imagem']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $tipos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $tipo = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['imagem']['tmp_name']);

        if (!isset($tipos[$tipo])) {
            $erros[] = 'A imagem precisa ser JPG, PNG ou WEBP.';
        } elseif ($_FILES['imagem']['size'] > 2 * 1024 * 1024) {
            $erros[] = 'A imagem precisa ter no máximo 2 MB.';
        } else {
            $novoNome = 'produto-' . bin2hex(random_bytes(6)) . '.' . $tipos[$tipo];
            move_uploaded_file($_FILES['imagem']['tmp_name'], __DIR__ . '/../assets/img/produtos/' . $novoNome);
            $produto['imagem'] = $novoNome;
        }
    }

    if (!$erros) {
        $dados = [
            $produto['nome'], $produto['categoria_id'], $produto['descricao'],
            $produto['preco'], $produto['estoque'], $produto['imagem'] ?: null, $produto['ativo'],
        ];
        if ($id > 0) {
            $dados[] = $id;
            db()->prepare('UPDATE produtos SET nome = ?, categoria_id = ?, descricao = ?, preco = ?, estoque = ?, imagem = ?, ativo = ? WHERE id = ?')
                ->execute($dados);
            flash('success', 'Produto atualizado.');
        } else {
            db()->prepare('INSERT INTO produtos (nome, categoria_id, descricao, preco, estoque, imagem, ativo) VALUES (?, ?, ?, ?, ?, ?, ?)')
                ->execute($dados);
            flash('success', 'Produto cadastrado.');
        }
        redirecionar('index.php');
    }
}

$titulo = $id ? 'Editar produto' : 'Novo produto';
require '../includes/header.php';
?>

<a href="index.php" class="btn btn-link px-0 mb-3">← Voltar ao painel</a>
<h1 class="h3 mb-4"><?= $id ? 'Editar produto' : 'Novo produto' ?></h1>

<?php foreach ($erros as $erro): ?><div class="alert alert-danger py-2"><?= e($erro) ?></div><?php endforeach; ?>

<form method="post" enctype="multipart/form-data" class="card card-body shadow-sm" style="max-width: 720px;">
  <?= csrf_campo() ?>
  <input type="hidden" name="id" value="<?= $id ?>">

  <div class="row g-3">
    <div class="col-12">
      <label class="form-label" for="nome">Nome</label>
      <input type="text" id="nome" name="nome" class="form-control" value="<?= e($produto['nome']) ?>" required>
    </div>
    <div class="col-12 col-md-6">
      <label class="form-label" for="categoria_id">Categoria</label>
      <select id="categoria_id" name="categoria_id" class="form-select" required>
        <option value="">Escolha...</option>
        <?php foreach ($categorias as $c): ?>
          <option value="<?= $c['id'] ?>" <?= (int) $c['id'] === (int) $produto['categoria_id'] ? 'selected' : '' ?>><?= e($c['nome']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-6 col-md-3">
      <label class="form-label" for="preco">Preço (R$)</label>
      <input type="text" id="preco" name="preco" class="form-control" value="<?= e($produto['preco']) ?>" inputmode="decimal" required>
    </div>
    <div class="col-6 col-md-3">
      <label class="form-label" for="estoque">Estoque</label>
      <input type="number" id="estoque" name="estoque" class="form-control" value="<?= (int) $produto['estoque'] ?>" min="0" required>
    </div>
    <div class="col-12">
      <label class="form-label" for="descricao">Descrição</label>
      <textarea id="descricao" name="descricao" class="form-control" rows="4"><?= e($produto['descricao']) ?></textarea>
    </div>
    <div class="col-12 col-md-8">
      <label class="form-label" for="imagem">Imagem (JPG, PNG ou WEBP, até 2 MB)</label>
      <input type="file" id="imagem" name="imagem" class="form-control" accept=".jpg,.jpeg,.png,.webp">
    </div>
    <div class="col-12 col-md-4 text-center">
      <img src="<?= imgProduto($produto['imagem']) ?>" class="img-mini" style="width: 90px; height: 90px;" alt="Imagem atual">
    </div>
    <div class="col-12 form-check ms-2">
      <input type="checkbox" id="ativo" name="ativo" class="form-check-input" <?= $produto['ativo'] ? 'checked' : '' ?>>
      <label class="form-check-label" for="ativo">Produto ativo (aparece na loja)</label>
    </div>
  </div>

  <button class="btn btn-primary mt-4">Salvar</button>
</form>

<?php require '../includes/footer.php'; ?>
