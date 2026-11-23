<?php $u = usuario(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($titulo ?? 'Loja') ?> | Spes · Gratia Plena</title>
  <link rel="icon" href="<?= BASE ?>assets/img/favicon.png">
  <!-- Fontes da identidade visual -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=Lato:wght@300;400;700&family=Pinyon+Script&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= BASE ?>assets/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light navbar-spes sticky-top">
  <div class="container">
    <a class="navbar-brand" href="<?= BASE ?>index.php">
      <img src="<?= BASE ?>assets/img/logo-horizontal.png" alt="Spes | Gratia Plena" class="logo-menu">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="menu">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="<?= BASE ?>index.php">Loja</a></li>
        <?php if ($u && $u['perfil'] === 'admin'): ?>
          <li class="nav-item"><a class="nav-link" href="<?= BASE ?>admin/index.php">Painel admin</a></li>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav align-items-lg-center gap-lg-2">
        <li class="nav-item">
          <a class="nav-link" href="<?= BASE ?>carrinho.php">Carrinho
            <span class="badge badge-dourado"><?= qtdCarrinho() ?></span>
          </a>
        </li>
        <?php if ($u): ?>
          <li class="nav-item"><span class="nav-link">Olá, <?= e(explode(' ', $u['nome'])[0]) ?></span></li>
          <li class="nav-item">
            <form action="<?= BASE ?>logout.php" method="post" class="d-inline">
              <?= csrf_campo() ?>
              <button class="btn btn-sm btn-outline-primary">Sair</button>
            </form>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?= BASE ?>login.php">Entrar</a></li>
          <li class="nav-item"><a class="btn btn-sm btn-dourado" href="<?= BASE ?>cadastro.php">Criar conta</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<main class="container py-4">
  <?php mostrarFlash(); ?>
