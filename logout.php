<?php
// SAIR: apaga a sessão
require 'includes/funcoes.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $_SESSION = [];
    session_destroy();
}
redirecionar('index.php');
