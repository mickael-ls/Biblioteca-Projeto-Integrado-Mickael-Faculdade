<?php

session_start();

if (!isset($_SESSION['funcionario_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'config/conexao.php';

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titulo = trim($_POST['titulo'] ?? '');
    $autor = trim($_POST['autor'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT);

    // Valida os campos
    if ($titulo === '' || $autor === '' || $quantidade === false || $quantidade === null || $quantidade < 1) {
        header('Location: livros.php?erro=campos');
        exit;
    }

    try {

        $sql = "INSERT INTO livros
                (titulo, autor, isbn, quantidade_total, quantidade_disponivel)
                VALUES
                (:titulo, :autor, :isbn, :total, :disponivel)";

        $comando = $conexao->prepare($sql);

        $comando->execute([
            ':titulo' => $titulo,
            ':autor' => $autor,
            ':isbn' => $isbn !== '' ? $isbn : null,
            ':total' => $quantidade,
            ':disponivel' => $quantidade
        ]);

        header('Location: livros.php?sucesso=1');
        exit;

    } catch (PDOException $erro) {
        die('Erro ao cadastrar livro: ' . $erro->getMessage());
    }
}

// Consulta os livros cadastrados
$sql = "SELECT * FROM livros ORDER BY titulo ASC";
$comando = $conexao->query($sql);
$livros = $comando->fetchAll(PDO::FETCH_ASSOC);

?>

<?php
$tituloPagina = 'Gerenciar Livros';
require_once 'includes/header.php';
?>

    <h1>Gerenciamento de Livros</h1>

    <p>
        Funcionário:
        <?= htmlspecialchars($_SESSION['funcionario_nome'], ENT_QUOTES, 'UTF-8') ?>
    </p>

    <a href="painel.php">Voltar ao painel</a>

    <hr>

    <h2>Cadastrar novo livro</h2>

    <?php if (isset($_GET['sucesso'])): ?>
        <p style="color: green;">Livro cadastrado com sucesso!</p>
    <?php endif; ?>

    <?php if (isset($_GET['erro'])): ?>
        <p style="color: red;">Preencha os campos corretamente. A quantidade deve ser pelo menos 1.</p>
    <?php endif; ?>

    <form method="POST" action="">

        <label>Título:</label><br>
        <input type="text" name="titulo" required>

        <br><br>

        <label>Autor:</label><br>
        <input type="text" name="autor" required>

        <br><br>

        <label>ISBN:</label><br>
        <input type="text" name="isbn">

        <br><br>

        <label>Quantidade de exemplares:</label><br>
        <input type="number" name="quantidade" min="1" required>

        <br><br>

        <button type="submit">Cadastrar livro</button>

    </form>

    <hr>

    <h2>Livros cadastrados</h2>

    <?php if (count($livros) > 0): ?>

        <table border="1" cellpadding="8">

            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Autor</th>
                <th>ISBN</th>
                <th>Quantidade total</th>
                <th>Disponíveis</th>
            </tr>

            <?php foreach ($livros as $livro): ?>

                <tr>
                    <td><?= $livro['id'] ?></td>

                    <td><?= htmlspecialchars($livro['titulo'], ENT_QUOTES, 'UTF-8') ?></td>

                    <td><?= htmlspecialchars($livro['autor'], ENT_QUOTES, 'UTF-8') ?></td>

                    <td><?= htmlspecialchars($livro['isbn'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>

                    <td><?= $livro['quantidade_total'] ?></td>

                    <td><?= $livro['quantidade_disponivel'] ?></td>
                </tr>

            <?php endforeach; ?>

        </table>

    <?php else: ?>

        <p>Nenhum livro cadastrado ainda.</p>

    <?php endif; ?>

<?php require_once 'includes/footer.php'; ?>