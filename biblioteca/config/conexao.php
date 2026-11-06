<?php

$host = 'localhost';
$banco = 'biblioteca_db';
$usuario = 'root';
$senha = '';

try {
    $conexao = new PDO(
        "mysql:host=$host;dbname=$banco;charset=utf8mb4",
        $usuario,
        $senha
    );

    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $erro) {
    die("Erro ao conectar ao banco de dados: " . $erro->getMessage());
}