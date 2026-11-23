<?php
// CADASTRO: cria conta de cliente com a senha criptografada (password_hash)
require 'includes/funcoes.php';

$voltar = $_GET['voltar'] ?? $_POST['voltar'] ?? 'index.php';
if (!preg_match('/^[a-z_]+\.php$/', $voltar)) {
    $voltar = 'index.php';
}

$erros = [];
$nome = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    if ($nome === '') $erros[] = 'Digite o seu nome.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = 'Digite um e-mail válido.';
    if (strlen($senha) < 6) $erros[] = 'A senha precisa ter pelo menos 6 caracteres.';
    if ($senha !== $confirmar) $erros[] = 'As senhas não são iguais.';

    if (!$erros) {
        try {
            $stmt = db()->prepare("INSERT INTO usuarios (nome, email, senha_hash, perfil) VALUES (?, ?, ?, 'cliente')");
            $stmt->execute([$nome, $email, password_hash($senha, PASSWORD_DEFAULT)]);

            session_regenerate_id(true);
            $_SESSION['usuario'] = [
                'id'     => (int) db()->lastInsertId(),
                'nome'   => $nome,
                'email'  => $email,
                'perfil' => 'cliente',
            ];
            flash('success', 'Conta criada! Bem-vindo(a), ' . $nome . '!');
            redirecionar($voltar);
        } catch (PDOException $ex) {
            if ($ex->getCode() === '23000') {
                $erros[] = 'Esse e-mail já está cadastrado.';
            } else {
                throw $ex;
            }
        }
    }
}

$titulo = 'Criar conta';
require 'includes/header.php';
?>

<div class="mx-auto" style="max-width: 460px;">
  <h1 class="h3 mb-4 text-center">Criar conta</h1>
  <?php foreach ($erros as $erro): ?><div class="alert alert-danger py-2"><?= e($erro) ?></div><?php endforeach; ?>

  <form method="post" class="card card-body shadow-sm">
    <?= csrf_campo() ?>
    <input type="hidden" name="voltar" value="<?= e($voltar) ?>">
    <label class="form-label" for="nome">Nome</label>
    <input type="text" id="nome" name="nome" class="form-control mb-3" value="<?= e($nome) ?>" required>
    <label class="form-label" for="email">E-mail</label>
    <input type="email" id="email" name="email" class="form-control mb-3" value="<?= e($email) ?>" required>
    <label class="form-label" for="senha">Senha (mínimo 6 caracteres)</label>
    <input type="password" id="senha" name="senha" class="form-control mb-3" minlength="6" required>
    <label class="form-label" for="confirmar">Confirmar senha</label>
    <input type="password" id="confirmar" name="confirmar" class="form-control mb-3" minlength="6" required>
    <button class="btn btn-primary w-100">Criar conta</button>
  </form>

  <p class="text-center mt-3">Já tem conta? <a href="login.php?voltar=<?= e(urlencode($voltar)) ?>">Entrar</a></p>
</div>

<?php require 'includes/footer.php'; ?>
