<?php
declare(strict_types=1);

namespace App\Core;

/** Controller base: renderização, redirecionamento, mensagens flash e proteção CSRF. */
abstract class Controller
{
    protected function view(string $view, array $data = []): void
    {
        View::render($view, $data);
    }

    protected function redirect(string $path): never
    {
        header('Location: ' . url($path));
        exit;
    }

    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
    }

    protected function validateCsrf(): void
    {
        $sent = (string) ($_POST['_csrf'] ?? '');
        $token = (string) ($_SESSION['csrf'] ?? '');
        if ($token === '' || !hash_equals($token, $sent)) {
            http_response_code(419);
            View::render('errors/419', ['title' => 'Sessão expirada']);
            exit;
        }
    }

    protected function post(string $key, string $default = ''): string
    {
        return trim((string) ($_POST[$key] ?? $default));
    }

    /** Converte "1.234,56" ou "12,5" em float. */
    protected function toFloat(string $value): float
    {
        $value = trim($value);
        if (str_contains($value, ',')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }
        return is_numeric($value) ? (float) $value : 0.0;
    }
}
