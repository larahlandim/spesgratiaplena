<?php
// LOGIN: confere a senha com password_verify()
require 'includes/funcoes.php';

// Para onde voltar depois do login (só aceita páginas da própria loja)
$voltar = $_GET['voltar'] ?? $_POST['voltar'] ?? 'index.php';
if (!preg_match('/^[a-z_]+\.php$/', $voltar)) {
    $voltar = 'index.php';
}

$erro = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    $stmt = db()->prepare('SELECT id, nome, email, senha_hash, perfil FROM usuarios WHERE email = ?');
    $stmt->execute([$email]);
    $conta = $stmt->fetch();

    if ($conta && password_verify($senha, $conta['senha_hash'])) {
        session_regenerate_id(true); // novo ID de sessão a cada login (segurança)
        $_SESSION['usuario'] = [
            'id'     => $conta['id'],
            'nome'   => $conta['nome'],
            'email'  => $conta['email'],
            'perfil' => $conta['perfil'],
        ];
        flash('success', 'Bem-vindo(a), ' . $conta['nome'] . '!');

        if ($conta['perfil'] === 'admin' && $voltar === 'index.php') {
            redirecionar('admin/index.php');
        }
        redirecionar($voltar);
    }
    $erro = 'E-mail ou senha incorretos.';
}

$titulo = 'Entrar';
require 'includes/header.php';
?>

<div class="mx-auto" style="max-width: 420px;">
  <h1 class="h3 mb-4 text-center">Entrar</h1>
  <?php if ($erro): ?><div class="alert alert-danger"><?= e($erro) ?></div><?php endif; ?>

  <form method="post" class="card card-body shadow-sm">
    <?= csrf_campo() ?>
    <input type="hidden" name="voltar" value="<?= e($voltar) ?>">
    <label class="form-label" for="email">E-mail</label>
    <input type="email" id="email" name="email" class="form-control mb-3" value="<?= e($email) ?>" required>
    <label class="form-label" for="senha">Senha</label>
    <input type="password" id="senha" name="senha" class="form-control mb-3" required>
    <button class="btn btn-primary w-100">Entrar</button>
  </form>

  <p class="text-center mt-3">Ainda não tem conta? <a href="cadastro.php?voltar=<?= e(urlencode($voltar)) ?>">Criar conta</a></p>
</div>

<?php require 'includes/footer.php'; ?>
