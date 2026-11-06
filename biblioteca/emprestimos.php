<?php

session_start();

if (!isset($_SESSION['funcionario_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'config/conexao.php';

$erro = '';
$mensagem = '';

// Processa o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $leitor_id = filter_input(INPUT_POST, 'leitor_id', FILTER_VALIDATE_INT);
    $livro_id = filter_input(INPUT_POST, 'livro_id', FILTER_VALIDATE_INT);
    $data_prevista = $_POST['data_prevista'] ?? '';

    if (!$leitor_id || !$livro_id || $data_prevista === '') {
        $erro = 'Preencha todos os campos corretamente!';
    } else {

        try {

            // Inicia uma transação no banco
            $conexao->beginTransaction();

            // Verifica se o leitor existe
            $sql = "SELECT id FROM leitores WHERE id = :id";
            $comando = $conexao->prepare($sql);
            $comando->execute([':id' => $leitor_id]);

            if (!$comando->fetch()) {
                throw new Exception('Leitor não encontrado!');
            }

            // Bloqueia o registro do livro durante a operação
            $sql = "SELECT quantidade_disponivel
                    FROM livros
                    WHERE id = :id
                    FOR UPDATE";

            $comando = $conexao->prepare($sql);
            $comando->execute([':id' => $livro_id]);

            $livro = $comando->fetch(PDO::FETCH_ASSOC);

            if (!$livro) {
                throw new Exception('Livro não encontrado!');
            }

            if ($livro['quantidade_disponivel'] < 1) {
                throw new Exception('Não há exemplares disponíveis desse livro!');
            }

            // Registra o empréstimo
            $sql = "INSERT INTO emprestimos
                    (leitor_id, livro_id, funcionario_id, data_emprestimo, data_prevista)
                    VALUES
                    (:leitor, :livro, :funcionario, CURDATE(), :data_prevista)";

            $comando = $conexao->prepare($sql);

            $comando->execute([
                ':leitor' => $leitor_id,
                ':livro' => $livro_id,
                ':funcionario' => $_SESSION['funcionario_id'],
                ':data_prevista' => $data_prevista
            ]);

            // Diminui a quantidade disponível
            $sql = "UPDATE livros
                    SET quantidade_disponivel = quantidade_disponivel - 1
                    WHERE id = :id";

            $comando = $conexao->prepare($sql);
            $comando->execute([':id' => $livro_id]);

            // Confirma as operações
            $conexao->commit();

            header('Location: emprestimos.php?sucesso=1');
            exit;


        } catch (PDOException $erro) {

            if ($conexao->inTransaction()) {
                $conexao->rollBack();
            }

            $mensagem = 'Erro ao registrar empréstimo no banco de dados.';
      
        } catch (Exception $erro) {

            // Desfaz as operações caso algo dê errado
            if ($conexao->inTransaction()) {
                $conexao->rollBack();
            }

            $mensagem = $erro->getMessage();

        } 
    }
}

// Busca leitores para o formulário
$sql = "SELECT id, nome FROM leitores ORDER BY nome ASC";
$comando = $conexao->query($sql);
$leitores = $comando->fetchAll(PDO::FETCH_ASSOC);

// Busca livros disponíveis
$sql = "SELECT id, titulo, quantidade_disponivel
        FROM livros
        WHERE quantidade_disponivel > 0
        ORDER BY titulo ASC";

$comando = $conexao->query($sql);
$livros = $comando->fetchAll(PDO::FETCH_ASSOC);

// Busca histórico de empréstimos
$sql = "SELECT
            e.id,
            l.nome AS leitor,
            b.titulo AS livro,
            f.nome AS funcionario,
            e.data_emprestimo,
            e.data_prevista
        FROM emprestimos e
        INNER JOIN leitores l ON e.leitor_id = l.id
        INNER JOIN livros b ON e.livro_id = b.id
        INNER JOIN funcionarios f ON e.funcionario_id = f.id
        WHERE e.data_devolucao IS NULL
        ORDER BY e.id DESC";

$comando = $conexao->query($sql);
$emprestimos = $comando->fetchAll(PDO::FETCH_ASSOC);

?>

<?php
$tituloPagina = 'Empréstimos - Biblioteca';
require_once 'includes/header.php';
?>

    <h1>Gerenciamento de Empréstimos</h1>

    <p>
        Funcionário:
        <?= htmlspecialchars($_SESSION['funcionario_nome'], ENT_QUOTES, 'UTF-8') ?>
    </p>

    <a href="painel.php">Voltar ao painel</a>

    <hr>

    <h2>Registrar novo empréstimo</h2>

    <?php if (isset($_GET['sucesso'])): ?>
        <p style="color: green;">Empréstimo registrado com sucesso!</p>
    <?php endif; ?>

    <?php if ($erro !== ''): ?>
        <p style="color: red;">
            <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <?php if (isset($mensagem)): ?>
        <p style="color: red;">
            <?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="">

        <label>Selecione o leitor:</label><br>

        <select name="leitor_id" required>
            <option value="">Escolha um leitor</option>

            <?php foreach ($leitores as $leitor): ?>
                <option value="<?= $leitor['id'] ?>">
                    <?= htmlspecialchars($leitor['nome'], ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>

        </select>

        <br><br>

        <label>Selecione o livro:</label><br>

        <select name="livro_id" required>
            <option value="">Escolha um livro</option>

            <?php foreach ($livros as $livro): ?>
                <option value="<?= $livro['id'] ?>">
                    <?= htmlspecialchars($livro['titulo'], ENT_QUOTES, 'UTF-8') ?>
                    (<?= $livro['quantidade_disponivel'] ?> disponíveis)
                </option>
            <?php endforeach; ?>

        </select>

        <br><br>

        <label>Data prevista para devolução:</label><br>
        <input type="date" name="data_prevista" required>

        <br><br>

        <button type="submit">Registrar empréstimo</button>

    </form>

    <hr>

    <h2>Empréstimos em aberto</h2>

    <?php if (count($emprestimos) > 0): ?>

        <table border="1" cellpadding="8">

            <tr>
                <th>ID</th>
                <th>Leitor</th>
                <th>Livro</th>
                <th>Funcionário</th>
                <th>Data do empréstimo</th>
                <th>Devolução prevista</th>
            </tr>

            <?php foreach ($emprestimos as $emprestimo): ?>

                <tr>
                    <td><?= $emprestimo['id'] ?></td>

                    <td><?= htmlspecialchars($emprestimo['leitor'], ENT_QUOTES, 'UTF-8') ?></td>

                    <td><?= htmlspecialchars($emprestimo['livro'], ENT_QUOTES, 'UTF-8') ?></td>

                    <td><?= htmlspecialchars($emprestimo['funcionario'], ENT_QUOTES, 'UTF-8') ?></td>

                    <td><?= $emprestimo['data_emprestimo'] ?></td>

                    <td><?= $emprestimo['data_prevista'] ?></td>
                </tr>

            <?php endforeach; ?>

        </table>

    <?php else: ?>

        <p>Nenhum empréstimo em aberto.</p>

    <?php endif; ?>

<?php require_once 'includes/footer.php'; ?>