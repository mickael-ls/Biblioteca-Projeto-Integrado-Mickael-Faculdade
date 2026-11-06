<?php

session_start();

if (!isset($_SESSION['funcionario_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'config/conexao.php';

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome'] ?? '');
    $cpf = trim($_POST['cpf'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($nome === '') {
        header('Location: clientes.php?erro=nome');
        exit;
    }

    try {

        $sql = "INSERT INTO leitores (nome, cpf, telefone, email)
                VALUES (:nome, :cpf, :telefone, :email)";

        $comando = $conexao->prepare($sql);

        $comando->execute([
            ':nome' => $nome,
            ':cpf' => $cpf !== '' ? $cpf : null,
            ':telefone' => $telefone !== '' ? $telefone : null,
            ':email' => $email !== '' ? $email : null
        ]);

        header('Location: clientes.php?sucesso=1');
        exit;

    } catch (PDOException $erro) {

        // Código 23000 indica violação de integridade, como CPF duplicado
        if ($erro->getCode() === '23000') {
            header('Location: clientes.php?erro=cpf');
            exit;
        }

        die('Erro ao cadastrar leitor: ' . $erro->getMessage());
    }
}

// Busca todos os leitores cadastrados
$sql = "SELECT * FROM leitores ORDER BY nome ASC";
$comando = $conexao->query($sql);
$leitores = $comando->fetchAll(PDO::FETCH_ASSOC);

?>

<?php
$tituloPagina = 'Gerenciar Leitores';
require_once 'includes/header.php';
?>

    <h1>Gerenciamento de Leitores</h1>

    <p>
        Funcionário: 
        <?= htmlspecialchars($_SESSION['funcionario_nome'], ENT_QUOTES, 'UTF-8') ?>
    </p>

    <a href="painel.php">Voltar ao painel</a>

    <hr>

    <h2>Cadastrar novo leitor</h2>

    <?php if (isset($_GET['sucesso'])): ?>
        <p style="color: green;">Leitor cadastrado com sucesso!</p>
    <?php endif; ?>

    <?php if (isset($_GET['erro']) && $_GET['erro'] === 'cpf'): ?>
        <p style="color: red;">Esse CPF já está cadastrado!</p>
    <?php endif; ?>

    <?php if (isset($_GET['erro']) && $_GET['erro'] === 'nome'): ?>
        <p style="color: red;">O nome é obrigatório!</p>
    <?php endif; ?>

    <form method="POST" action="">

        <label>Nome completo:</label><br>
        <input type="text" name="nome" required>

        <br><br>

        <label>CPF:</label><br>
        <input type="text" name="cpf" placeholder="000.000.000-00">

        <br><br>

        <label>Telefone:</label><br>
        <input type="text" name="telefone" placeholder="(00) 00000-0000">

        <br><br>

        <label>E-mail:</label><br>
        <input type="email" name="email">

        <br><br>

        <button type="submit">Cadastrar leitor</button>

    </form>

    <hr>

    <h2>Leitores cadastrados</h2>

    <?php if (count($leitores) > 0): ?>

        <table border="1" cellpadding="8">

            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>CPF</th>
                <th>Telefone</th>
                <th>E-mail</th>
            </tr>

            <?php foreach ($leitores as $leitor): ?>

                <tr>
                    <td><?= $leitor['id'] ?></td>

                    <td>
                        <?= htmlspecialchars($leitor['nome'], ENT_QUOTES, 'UTF-8') ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($leitor['cpf'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($leitor['telefone'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($leitor['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </td>
                </tr>

            <?php endforeach; ?>

        </table>

    <?php else: ?>

        <p>Nenhum leitor cadastrado ainda.</p>

    <?php endif; ?>

<?php require_once 'includes/footer.php'; ?>