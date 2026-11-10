<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Roteador do Front Controller: liga "MÉTODO + URL" a "Controller::ação".
 * Parâmetros numéricos são declarados como {id} e repassados à ação.
 */
final class Router
{
    /** @var array<int, array{0:string,1:string,2:array{0:string,1:string}}> */
    private array $routes = [];

    public function __construct(private Container $container) {}

    public function get(string $path, array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    private function add(string $method, string $path, array $handler): void
    {
        $regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>\d+)', $path) . '$#';
        $this->routes[] = [$method, $regex, $handler];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $base = rtrim((string) Config::get('app.base_url', ''), '/');
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }
        $path = '/' . trim($path, '/');

        foreach ($this->routes as [$routeMethod, $regex, $handler]) {
            if ($routeMethod === $method && preg_match($regex, $path, $matches)) {
                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[] = (int) $value;
                    }
                }
                $controller = $this->container->get($handler[0]);
                $controller->{$handler[1]}(...$params);
                return;
            }
        }

        http_response_code(404);
        View::render('errors/404', ['title' => 'Página não encontrada']);
    }
}
