<?php

session_start();

require_once 'config/conexao.php';

// Se já estiver logado, vai direto para o painel
if (isset($_SESSION['funcionario_id'])) {
    header('Location: painel.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $usuario = trim($_POST['usuario']);
    $senha = $_POST['senha'];

    // Busca o funcionário pelo nome de usuário
    $sql = "SELECT * FROM funcionarios WHERE usuario = :usuario";

    $comando = $conexao->prepare($sql);
    $comando->execute([':usuario' => $usuario]);

    $funcionario = $comando->fetch(PDO::FETCH_ASSOC);

    // Verifica se o funcionário existe e se a senha está correta
    if ($funcionario && password_verify($senha, $funcionario['senha'])) {

        session_regenerate_id(true);

        $_SESSION['funcionario_id'] = $funcionario['id'];
        $_SESSION['funcionario_nome'] = $funcionario['nome'];

        header('Location: painel.php');
        exit;

    } else {
        $erro = 'Usuário ou senha incorretos!';
    }
}

?>

<?php
$tituloPagina = 'Login - Biblioteca';
require_once 'includes/header.php';
?>

    <h1>Sistema de Gerenciamento de Biblioteca</h1>
    <h2>Login de Funcionário</h2>

    <?php if ($erro): ?>
        <p style="color: red;">
            <?= htmlspecialchars($erro) ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="">

        <label>Usuário:</label>
        <input type="text" name="usuario" required>

        <br><br>

        <label>Senha:</label>
        <input type="password" name="senha" required>

        <br><br>

        <button type="submit">Entrar</button>

    </form>

<?php require_once 'includes/footer.php'; ?>