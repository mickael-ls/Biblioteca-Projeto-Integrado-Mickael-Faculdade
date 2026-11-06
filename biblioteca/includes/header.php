
<?php
$tituloPagina = $tituloPagina ?? 'Sistema de Biblioteca';
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="css/style.css">

    <title><?= htmlspecialchars($tituloPagina, ENT_QUOTES, 'UTF-8') ?></title>
</head>

<body>

    <header class="topo">

        <div class="topo-conteudo">

            <a href="painel.php" class="logo">
                Biblioteca
            </a>

            <?php if (isset($_SESSION['funcionario_id'])): ?>

                <nav>
                    <a href="painel.php">Início</a>
                    <a href="clientes.php">Leitores</a>
                    <a href="livros.php">Livros</a>
                    <a href="emprestimos.php">Empréstimos</a>
                    <a href="devolucoes.php">Devoluções</a>
                    <a href="sair.php">Sair</a>
                </nav>

            <?php endif; ?>

        </div>

    </header>

    <main class="container">
