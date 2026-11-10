-- Dados de exemplo para demonstração
USE pdv;

INSERT INTO clientes (nome, cpf, email, telefone, endereco) VALUES
('Maria Souza',   '52998224725', 'maria@email.com', '65999990001', 'Rua das Flores, 100 - Cuiabá/MT'),
('João Pereira',  '11144477735', 'joao@email.com',  '65999990002', 'Av. Brasil, 250 - Várzea Grande/MT'),
('Ana Lima',      '39053344705', 'ana@email.com',   '65999990003', 'Rua do Comércio, 45 - Cuiabá/MT');

INSERT INTO produtos (cod, nome, categoria, validade, quantidade, preco_unitario, preco_pacote) VALUES
('CAF-001', 'Café torrado 500g',        'Mercearia',  DATE_ADD(CURDATE(), INTERVAL 240 DAY), 50,  18.90, 108.00),
('ACU-001', 'Açúcar refinado 1kg',      'Mercearia',  DATE_ADD(CURDATE(), INTERVAL 400 DAY), 80,   5.49,  30.00),
('ARR-001', 'Arroz tipo 1 5kg',         'Mercearia',  DATE_ADD(CURDATE(), INTERVAL 300 DAY), 40,  27.90, 160.00),
('FEI-001', 'Feijão carioca 1kg',       'Mercearia',  DATE_ADD(CURDATE(), INTERVAL 280 DAY), 60,   8.75,  50.00),
('OLE-001', 'Óleo de soja 900ml',       'Mercearia',  DATE_ADD(CURDATE(), INTERVAL 320 DAY), 70,   7.99,  45.00),
('LEI-001', 'Leite integral 1L',        'Laticínios', DATE_ADD(CURDATE(), INTERVAL 90 DAY),  120,  5.20,  29.00),
('PAO-001', 'Pão de forma 500g',        'Padaria',    DATE_ADD(CURDATE(), INTERVAL 10 DAY),  35,   8.49,   0.00),
('MAC-001', 'Macarrão espaguete 500g',  'Mercearia',  DATE_ADD(CURDATE(), INTERVAL 360 DAY), 90,   4.69,  26.00),
('REF-001', 'Refrigerante cola 2L',     'Bebidas',    DATE_ADD(CURDATE(), INTERVAL 180 DAY), 60,   9.99,  55.00),
('SAB-001', 'Sabão em pó 1kg',          'Limpeza',    DATE_ADD(CURDATE(), INTERVAL 500 DAY), 30,  14.50,  80.00);

-- Vendas de exemplo (para o relatório já nascer com dados)
INSERT INTO vendas (cliente_id, subtotal, tipo_desconto, valor_desconto_informado, desconto, total, forma_pagamento, status, criado_em) VALUES
(1, 46.80, 'nenhum',     0,  0.00, 46.80, 'pix',            'finalizada', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(2, 54.30, 'percentual', 10, 5.43, 48.87, 'cartao_credito', 'finalizada', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(NULL, 9.99, 'nenhum',   0,  0.00,  9.99, 'dinheiro',       'cancelada',  DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, 36.65, 'valor',      5,  5.00, 31.65, 'cartao_debito',  'finalizada', NOW());

INSERT INTO itens_venda (venda_id, produto_id, nome_produto, preco_unitario, quantidade, subtotal) VALUES
(1, 3, 'Arroz tipo 1 5kg',        27.90, 1, 27.90),
(1, 1, 'Café torrado 500g',       18.90, 1, 18.90),
(2, 3, 'Arroz tipo 1 5kg',        27.90, 1, 27.90),
(2, 4, 'Feijão carioca 1kg',       8.75, 1,  8.75),
(2, 1, 'Café torrado 500g',       18.90, 1, 18.90),
(3, 9, 'Refrigerante cola 2L',     9.99, 1,  9.99),
(4, 1, 'Café torrado 500g',       18.90, 1, 18.90),
(4, 4, 'Feijão carioca 1kg',       8.75, 1,  8.75),
(4, 5, 'Óleo de soja 900ml',       7.99, 1,  7.99);
