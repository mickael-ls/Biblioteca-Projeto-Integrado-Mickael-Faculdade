<?php

session_start();

// Impede o acesso de quem não fez login
if (!isset($_SESSION['funcionario_id'])) {
    header('Location: index.php');
    exit;
}

?>

<?php
$tituloPagina = 'Painel - Biblioteca';
require_once 'includes/header.php';
?>

    <h1>Sistema de Gerenciamento de Biblioteca</h1>

    <h2>
        Bem-vindo(a), 
        <?= htmlspecialchars($_SESSION['funcionario_nome']) ?>!
    </h2>

    <p>Você está conectado ao sistema.</p>

    <hr>

    <h3>Menu principal</h3>

    <ul>
        <li><a href="clientes.php">Gerenciar leitores</a></li>
        <li><a href="livros.php">Gerenciar livros</a></li>
        <li><a href="emprestimos.php">Registrar empréstimos</a></li>
        <li><a href="devolucoes.php">Registrar devoluções</a></li>
    </ul>

    <a href="sair.php">Sair do sistema</a>

<?php require_once 'includes/footer.php'; ?>