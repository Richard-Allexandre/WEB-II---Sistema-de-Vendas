<?php
declare(strict_types=1);

/**
 * FRONT CONTROLLER — único ponto de entrada da aplicação.
 * Todas as requisições passam por aqui e são despachadas ao Controller correto.
 */

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('VIEW_PATH', APP_PATH . '/Views');

// Autoloader PSR-4 simples: App\Foo\Bar  ->  app/Foo/Bar.php
spl_autoload_register(static function (string $class): void {
    if (strncmp($class, 'App\\', 4) !== 0) {
        return;
    }
    $file = APP_PATH . '/' . str_replace('\\', '/', substr($class, 4)) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

require APP_PATH . '/Core/helpers.php';

use App\Core\Config;
use App\Core\Router;
use App\Core\View;

Config::load(APP_PATH . '/Config/config.php');
session_start();

try {
    $container = require APP_PATH . '/bootstrap.php';
    $router = new Router($container);
    require APP_PATH . '/routes.php';
    $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
} catch (Throwable $e) {
    http_response_code(500);
    View::render('errors/500', [
        'title'   => 'Erro interno',
        'message' => Config::get('app.debug') ? $e->getMessage() : null,
    ]);
}
