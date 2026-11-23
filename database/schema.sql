-- =========================================================
-- Loja Spes | Gratia Plena - Script do banco de dados (MySQL/MariaDB)
-- Cria o banco, as tabelas e uma carga inicial de teste.
-- Como usar: phpMyAdmin > Importar > escolher este arquivo > Importar
-- =========================================================

CREATE DATABASE IF NOT EXISTS loja_tercos
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE loja_tercos;

-- Apaga as tabelas antigas (para poder rodar o script de novo)
DROP TABLE IF EXISTS itens_pedido;
DROP TABLE IF EXISTS pedidos;
DROP TABLE IF EXISTS produtos;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS usuarios;

-- ---------------------------------------------------------
-- Usuários: clientes e administradores
-- ---------------------------------------------------------
CREATE TABLE usuarios (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome        VARCHAR(100) NOT NULL,
  email       VARCHAR(150) NOT NULL,
  senha_hash  VARCHAR(255) NOT NULL,             -- senha criptografada (password_hash)
  perfil      ENUM('cliente', 'admin') NOT NULL DEFAULT 'cliente',
  criado_em   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_usuarios_email (email)           -- e-mail único
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Categorias dos produtos
-- ---------------------------------------------------------
CREATE TABLE categorias (
  id    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome  VARCHAR(80) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uk_categorias_nome (nome)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Produtos (cada produto pertence a uma categoria)
-- ---------------------------------------------------------
CREATE TABLE produtos (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  categoria_id  INT UNSIGNED NOT NULL,
  nome          VARCHAR(150) NOT NULL,
  descricao     TEXT,
  preco         DECIMAL(10,2) NOT NULL,          -- valor monetário com precisão decimal
  estoque       INT UNSIGNED NOT NULL DEFAULT 0, -- contador inteiro
  imagem        VARCHAR(255) NULL,
  ativo         TINYINT(1) NOT NULL DEFAULT 1,
  criado_em     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_produtos_categoria (categoria_id),
  CONSTRAINT fk_produtos_categoria FOREIGN KEY (categoria_id)
    REFERENCES categorias (id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Pedidos: cliente, data, forma de pagamento e total
-- ---------------------------------------------------------
CREATE TABLE pedidos (
  id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id       INT UNSIGNED NOT NULL,
  data_pedido      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  forma_pagamento  ENUM('PIX', 'Boleto', 'Cartao') NOT NULL,
  total            DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (id),
  KEY idx_pedidos_usuario (usuario_id),
  CONSTRAINT fk_pedidos_usuario FOREIGN KEY (usuario_id)
    REFERENCES usuarios (id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Itens do pedido: resolve a relação N:N entre pedidos e produtos
-- Guarda a quantidade e CONGELA o preço praticado na compra
-- ---------------------------------------------------------
CREATE TABLE itens_pedido (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  pedido_id       INT UNSIGNED NOT NULL,
  produto_id      INT UNSIGNED NOT NULL,
  quantidade      INT UNSIGNED NOT NULL,
  preco_unitario  DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uk_item_pedido_produto (pedido_id, produto_id),
  KEY idx_itens_produto (produto_id),
  CONSTRAINT fk_itens_pedido FOREIGN KEY (pedido_id)
    REFERENCES pedidos (id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_itens_produto FOREIGN KEY (produto_id)
    REFERENCES produtos (id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =========================================================
-- CARGA INICIAL DE TESTE
-- =========================================================

-- Senhas: admin123 e cliente123 (já criptografadas com password_hash)
INSERT INTO usuarios (nome, email, senha_hash, perfil) VALUES
('Administrador', 'admin@spes.com', '$2y$10$QIECVoaa/J3eEdZMjoshDOs4N5QddnXD3pq7kj13u2ZhyJ3KQjFpy', 'admin'),
('Maria Cliente', 'cliente@spes.com', '$2y$10$rDFFeCkMh3x1CbEWqkzgN.KqUB3L.f0FdUvERKjB9S1oNkBjMi3K6', 'cliente');

INSERT INTO categorias (nome) VALUES
('Títulos de Nossa Senhora'),
('Santos de Devoção');

INSERT INTO produtos (categoria_id, nome, descricao, preco, estoque, imagem) VALUES
(1, 'Terço Nossa Senhora de Lourdes', 'Contas peroladas em branco e azul-claro, lembrando o manto de Nossa Senhora de Lourdes e as águas da gruta de Massabielle. Medalha com o monograma mariano e acabamento prateado.', 89.90, 15, 'nossa-senhora-de-lourdes.jpg'),
(1, 'Terço Nossa Senhora do Rosário', 'Contas cor marsala com detalhes dourados, em honra a Nossa Senhora do Rosário, a Rainha do Santo Terço. Medalha com a rosa, símbolo da oração do Rosário.', 94.90, 12, 'nossa-senhora-do-rosario.jpg'),
(1, 'Terço Nossa Senhora da Esperança', 'Contas verde-sálvia, a cor da esperança, com medalha de âncora, símbolo da esperança cristã. Acabamento dourado.', 84.90, 20, 'nossa-senhora-da-esperanca.jpg'),
(1, 'Terço Nossa Senhora de Fátima', 'Contas peroladas brancas com acabamento dourado e medalha com raios de luz, lembrando as aparições de Nossa Senhora aos três pastorinhos em Fátima.', 89.90, 10, 'nossa-senhora-de-fatima.jpg'),
(1, 'Terço Nossa Senhora de Guadalupe', 'Contas rosadas e medalha verde-turquesa com estrelas, inspirada no manto estrelado de Nossa Senhora de Guadalupe, padroeira das Américas.', 92.90, 3, 'nossa-senhora-de-guadalupe.jpg'),
(1, 'Terço Nossa Senhora Aparecida', 'Contas azul-marinho com detalhes dourados e medalha com coroa, em honra a Nossa Senhora Aparecida, Rainha e Padroeira do Brasil.', 99.90, 18, 'nossa-senhora-aparecida.jpg'),
(2, 'Terço Santa Teresinha', 'Contas rosé delicadas e medalha com rosa, lembrando a promessa de Santa Teresinha do Menino Jesus de fazer cair do Céu uma chuva de rosas.', 79.90, 14, 'santa-teresinha.jpg'),
(2, 'Terço São Bento', 'Contas pretas com acabamento prateado e medalha com a cruz de São Bento, sinal de proteção e de confiança em Deus.', 74.90, 25, 'sao-bento.jpg'),
(2, 'Terço São Miguel Arcanjo', 'Contas azul-acinzentadas, acabamento prateado e medalha com espada, em honra a São Miguel Arcanjo, príncipe da milícia celeste.', 84.90, 0, 'sao-miguel-arcanjo.jpg'),
(2, 'Terço São Jorge', 'Contas vermelhas com acabamento prateado e medalha com a cruz de São Jorge, o santo guerreiro, exemplo de coragem e fé.', 79.90, 16, 'sao-jorge.jpg');

-- Um pedido de exemplo (para aparecer no painel admin)
INSERT INTO pedidos (usuario_id, data_pedido, forma_pagamento, total) VALUES
(2, '2026-11-20 10:30:00', 'PIX', 174.80);

INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco_unitario) VALUES
(1, 1, 1, 89.90),
(1, 3, 1, 84.90);
