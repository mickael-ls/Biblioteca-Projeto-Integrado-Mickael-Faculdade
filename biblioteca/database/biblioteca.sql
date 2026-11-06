
CREATE DATABASE IF NOT EXISTS biblioteca_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE biblioteca_db;

-- Tabela de funcionários
CREATE TABLE funcionarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL
);

-- Tabela de leitores
CREATE TABLE leitores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(14) UNIQUE,
    telefone VARCHAR(20),
    email VARCHAR(100),
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de livros
CREATE TABLE livros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    autor VARCHAR(100) NOT NULL,
    isbn VARCHAR(20),
    quantidade_total INT NOT NULL,
    quantidade_disponivel INT NOT NULL
);

-- Tabela de empréstimos
CREATE TABLE emprestimos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    leitor_id INT NOT NULL,
    livro_id INT NOT NULL,
    funcionario_id INT NOT NULL,
    data_emprestimo DATE NOT NULL,
    data_prevista DATE NOT NULL,
    data_devolucao DATE NULL,

    FOREIGN KEY (leitor_id) REFERENCES leitores(id),
    FOREIGN KEY (livro_id) REFERENCES livros(id),
    FOREIGN KEY (funcionario_id) REFERENCES funcionarios(id)
);
