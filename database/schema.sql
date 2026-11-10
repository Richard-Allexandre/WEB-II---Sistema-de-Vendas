-- PDV MVC — esquema do banco (MySQL 5.7+ / MariaDB 10.3+)
CREATE DATABASE IF NOT EXISTS pdv CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pdv;

DROP TABLE IF EXISTS itens_venda;
DROP TABLE IF EXISTS vendas;
DROP TABLE IF EXISTS produtos;
DROP TABLE IF EXISTS clientes;

CREATE TABLE clientes (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome       VARCHAR(120) NOT NULL,
    cpf        CHAR(11)     NOT NULL UNIQUE,
    email      VARCHAR(120) NULL,
    telefone   VARCHAR(20)  NULL,
    endereco   VARCHAR(200) NULL,
    criado_em  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Mesmas colunas da tabela `produtos` usada na aula (cod, categoria, validade, preco_unitario, preco_pacote, imagem)
CREATE TABLE produtos (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cod             VARCHAR(30)    NOT NULL UNIQUE,
    nome            VARCHAR(120)   NOT NULL,
    categoria       VARCHAR(60)    NOT NULL,
    validade        DATE           NOT NULL,
    quantidade      INT            NOT NULL DEFAULT 0,
    preco_unitario  DECIMAL(10,2)  NOT NULL,
    preco_pacote    DECIMAL(10,2)  NOT NULL DEFAULT 0,
    imagem          VARCHAR(255)   NULL
) ENGINE=InnoDB;

CREATE TABLE vendas (
    id                       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id               INT UNSIGNED  NULL,
    subtotal                 DECIMAL(10,2) NOT NULL,
    tipo_desconto            VARCHAR(20)   NOT NULL DEFAULT 'nenhum',
    valor_desconto_informado DECIMAL(10,2) NOT NULL DEFAULT 0,
    desconto                 DECIMAL(10,2) NOT NULL DEFAULT 0,
    total                    DECIMAL(10,2) NOT NULL,
    forma_pagamento          VARCHAR(20)   NOT NULL,
    status                   VARCHAR(20)   NOT NULL DEFAULT 'finalizada',
    criado_em                DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_vendas_cliente FOREIGN KEY (cliente_id)
        REFERENCES clientes(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE itens_venda (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    venda_id        INT UNSIGNED  NOT NULL,
    produto_id      INT UNSIGNED  NOT NULL,
    nome_produto    VARCHAR(120)  NOT NULL,
    preco_unitario  DECIMAL(10,2) NOT NULL,
    quantidade      INT           NOT NULL,
    subtotal        DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_itens_venda  FOREIGN KEY (venda_id)   REFERENCES vendas(id)   ON DELETE CASCADE,
    CONSTRAINT fk_itens_produto FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE RESTRICT
) ENGINE=InnoDB;
