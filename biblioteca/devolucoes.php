<?php

session_start();

if (!isset($_SESSION['funcionario_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'config/conexao.php';

$mensagem = '';
$erro = '';

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $emprestimo_id = filter_input(
        INPUT_POST,
        'emprestimo_id',
        FILTER_VALIDATE_INT
    );

    if (!$emprestimo_id) {

        $erro = 'Empréstimo inválido!';

    } else {

        try {

            // Inicia uma transação
            $conexao->beginTransaction();

            // Busca e bloqueia o empréstimo
            $sql = "SELECT id, livro_id, data_devolucao
                    FROM emprestimos
                    WHERE id = :id
                    FOR UPDATE";

            $comando = $conexao->prepare($sql);

            $comando->execute([
                ':id' => $emprestimo_id
            ]);

            $emprestimo = $comando->fetch(PDO::FETCH_ASSOC);

            if (!$emprestimo) {

                throw new Exception('Empréstimo não encontrado!');

            }

            if ($emprestimo['data_devolucao'] !== null) {

                throw new Exception('Esse empréstimo já foi devolvido!');

            }

            // Registra a data da devolução
            $sql = "UPDATE emprestimos
                    SET data_devolucao = CURDATE()
                    WHERE id = :id";

            $comando = $conexao->prepare($sql);

            $comando->execute([
                ':id' => $emprestimo_id
            ]);

            // Devolve um exemplar ao estoque
            $sql = "UPDATE livros
                    SET quantidade_disponivel = quantidade_disponivel + 1
                    WHERE id = :id
                    AND quantidade_disponivel < quantidade_total";

            $comando = $conexao->prepare($sql);

            $comando->execute([
                ':id' => $emprestimo['livro_id']
            ]);

            if ($comando->rowCount() !== 1) {
                throw new Exception('Não foi possível atualizar o estoque!');
            }

            // Confirma as operações
            $conexao->commit();

            header('Location: devolucoes.php?sucesso=1');
            exit;

        } catch (PDOException $erro) {

            if ($conexao->inTransaction()) {
                $conexao->rollBack();
            }

            $mensagem = 'Erro ao registrar a devolução no banco de dados.';

        } catch (Exception $erro) {

            if ($conexao->inTransaction()) {
                $conexao->rollBack();
            }

            $mensagem = $erro->getMessage();
        }
    }
}

// Busca empréstimos que ainda não foram devolvidos
$sql = "SELECT
            e.id,
            l.nome AS leitor,
            b.titulo AS livro,
            e.data_emprestimo,
            e.data_prevista
        FROM emprestimos e
        INNER JOIN leitores l ON e.leitor_id = l.id
        INNER JOIN livros b ON e.livro_id = b.id
        WHERE e.data_devolucao IS NULL
        ORDER BY e.data_prevista ASC";

$comando = $conexao->query($sql);

$emprestimos = $comando->fetchAll(PDO::FETCH_ASSOC);

?>

<?php
$tituloPagina = 'Devoluções - Biblioteca';
require_once 'includes/header.php';
?>

    <h1>Gerenciamento de Devoluções</h1>

    <p>
        Funcionário:
        <?= htmlspecialchars($_SESSION['funcionario_nome'], ENT_QUOTES, 'UTF-8') ?>
    </p>

    <a href="painel.php">Voltar ao painel</a>

    <hr>

    <h2>Empréstimos aguardando devolução</h2>

    <?php if (isset($_GET['sucesso'])): ?>

        <p style="color: green;">
            Devolução registrada com sucesso!
        </p>

    <?php endif; ?>

    <?php if ($erro !== ''): ?>

        <p style="color: red;">
            <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
        </p>

    <?php endif; ?>

    <?php if ($mensagem !== ''): ?>

        <p style="color: red;">
            <?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?>
        </p>

    <?php endif; ?>

    <?php if (count($emprestimos) > 0): ?>

        <table border="1" cellpadding="8">

            <tr>
                <th>ID do empréstimo</th>
                <th>Leitor</th>
                <th>Livro</th>
                <th>Data do empréstimo</th>
                <th>Devolução prevista</th>
                <th>Ação</th>
            </tr>

            <?php foreach ($emprestimos as $emprestimo): ?>

                <tr>

                    <td><?= $emprestimo['id'] ?></td>

                    <td>
                        <?= htmlspecialchars($emprestimo['leitor'], ENT_QUOTES, 'UTF-8') ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($emprestimo['livro'], ENT_QUOTES, 'UTF-8') ?>
                    </td>

                    <td><?= $emprestimo['data_emprestimo'] ?></td>

                    <td><?= $emprestimo['data_prevista'] ?></td>

                    <td>

                        <form method="POST" action="">

                            <input
                                type="hidden"
                                name="emprestimo_id"
                                value="<?= $emprestimo['id'] ?>"
                            >

                            <button type="submit">
                                Registrar devolução
                            </button>

                        </form>

                    </td>

                </tr>

            <?php endforeach; ?>

        </table>

    <?php else: ?>

        <p>Não existem empréstimos aguardando devolução.</p>

    <?php endif; ?>
    
<?php require_once 'includes/footer.php'; ?>