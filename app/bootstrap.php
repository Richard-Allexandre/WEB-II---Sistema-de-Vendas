<?php
declare(strict_types=1);

/**
 * Composition root: aqui as dependências são montadas (Injeção de Dependência).
 * Retorna o Container usado pelo Router para instanciar os Controllers.
 */

use App\Controllers\CarrinhoController;
use App\Controllers\ClienteController;
use App\Controllers\HomeController;
use App\Controllers\ProdutoController;
use App\Controllers\RelatorioController;
use App\Core\Container;
use App\Core\Database;
use App\Patterns\Observer\EstoqueObserver;
use App\Patterns\Observer\LogObserver;
use App\Repositories\ClienteRepository;
use App\Repositories\ProdutoRepository;
use App\Repositories\VendaRepository;
use App\Services\CarrinhoService;
use App\Services\UploadService;
use App\Services\VendaService;

$c = new Container();

// Infraestrutura (Singleton)
$c->set(PDO::class, fn () => Database::getConnection());

// Repositories
$c->set(ClienteRepository::class, fn (Container $c) => new ClienteRepository($c->get(PDO::class)));
$c->set(ProdutoRepository::class, fn (Container $c) => new ProdutoRepository($c->get(PDO::class)));
$c->set(VendaRepository::class, fn (Container $c) => new VendaRepository($c->get(PDO::class)));

// Services (VendaService é o Subject; os Observers são anexados aqui)
$c->set(CarrinhoService::class, fn (Container $c) => new CarrinhoService($c->get(ProdutoRepository::class)));
$c->set(VendaService::class, function (Container $c) {
    $service = new VendaService($c->get(PDO::class), $c->get(VendaRepository::class));
    $service->attach(new EstoqueObserver($c->get(ProdutoRepository::class))); // 1º: pode falhar e desfazer a venda
    $service->attach(new LogObserver(BASE_PATH . '/storage/vendas.log'));
    return $service;
});

$c->set(UploadService::class, fn () => new UploadService(BASE_PATH . '/public/imagens'));

// Controllers
$c->set(ProdutoController::class, fn (Container $c) => new ProdutoController(
    $c->get(ProdutoRepository::class),
    $c->get(UploadService::class)
));
$c->set(HomeController::class, fn () => new HomeController());
$c->set(ClienteController::class, fn (Container $c) => new ClienteController($c->get(ClienteRepository::class)));
$c->set(CarrinhoController::class, fn (Container $c) => new CarrinhoController(
    $c->get(CarrinhoService::class),
    $c->get(ProdutoRepository::class),
    $c->get(ClienteRepository::class),
    $c->get(VendaService::class)
));
$c->set(RelatorioController::class, fn (Container $c) => new RelatorioController(
    $c->get(VendaRepository::class),
    $c->get(VendaService::class),
    $c->get(ClienteRepository::class)
));

return $c;
