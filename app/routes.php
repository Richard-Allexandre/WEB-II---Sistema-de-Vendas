<?php
declare(strict_types=1);

/** Tabela de rotas. $router vem do Front Controller (public/index.php). */

use App\Controllers\CarrinhoController;
use App\Controllers\ClienteController;
use App\Controllers\HomeController;
use App\Controllers\ProdutoController;
use App\Controllers\RelatorioController;

$router->get('/', [HomeController::class, 'index']);

// Produtos (CRUD da aula, agora em MVC)
$router->get('/produtos', [ProdutoController::class, 'index']);
$router->get('/produtos/novo', [ProdutoController::class, 'create']);
$router->post('/produtos', [ProdutoController::class, 'store']);
$router->get('/produtos/{id}/editar', [ProdutoController::class, 'edit']);
$router->post('/produtos/{id}/atualizar', [ProdutoController::class, 'update']);
$router->post('/produtos/{id}/excluir', [ProdutoController::class, 'destroy']);

// 1) Cadastro de clientes — CRUD
$router->get('/clientes', [ClienteController::class, 'index']);                 // Read (lista/busca)
$router->get('/clientes/novo', [ClienteController::class, 'create']);           // Create (form)
$router->post('/clientes', [ClienteController::class, 'store']);                // Create (grava)
$router->get('/clientes/{id}/editar', [ClienteController::class, 'edit']);      // Update (form)
$router->post('/clientes/{id}/atualizar', [ClienteController::class, 'update']);// Update (grava)
$router->post('/clientes/{id}/excluir', [ClienteController::class, 'destroy']); // Delete

// 2) Carrinho de vendas — CRUD
$router->get('/carrinho', [CarrinhoController::class, 'index']);                       // Read
$router->post('/carrinho/adicionar', [CarrinhoController::class, 'adicionar']);        // Create
$router->post('/carrinho/{id}/atualizar', [CarrinhoController::class, 'atualizar']);   // Update
$router->post('/carrinho/{id}/remover', [CarrinhoController::class, 'remover']);       // Delete
$router->post('/carrinho/limpar', [CarrinhoController::class, 'limpar']);              // Delete (todos)
$router->post('/carrinho/finalizar', [CarrinhoController::class, 'finalizar']);        // Create (venda)

// 3) Relatórios de vendas — CRUD
$router->get('/relatorios', [RelatorioController::class, 'index']);                    // Read (filtros + resumo)
$router->get('/relatorios/exportar', [RelatorioController::class, 'exportar']);        // Read (CSV)
$router->get('/relatorios/{id}', [RelatorioController::class, 'show']);                // Read (detalhe)
$router->post('/relatorios/{id}/atualizar', [RelatorioController::class, 'update']);   // Update (cliente/pagamento)
$router->post('/relatorios/{id}/cancelar', [RelatorioController::class, 'cancelar']);  // Update (status)
$router->post('/relatorios/{id}/excluir', [RelatorioController::class, 'destroy']);    // Delete
