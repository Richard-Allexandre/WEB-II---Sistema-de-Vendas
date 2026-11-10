<?php
// Funções auxiliares usadas pelas Views e pelo Controller base.

use App\Core\Config;

/** Escapa saída HTML (proteção contra XSS). */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function money(float|int|string|null $value): string
{
    return 'R$ ' . number_format((float) $value, 2, ',', '.');
}

function url(string $path = ''): string
{
    return rtrim((string) Config::get('app.base_url', ''), '/') . '/' . ltrim($path, '/');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

/** Retorna e limpa as mensagens flash. */
function flash_pull(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function date_br(?string $value): string
{
    return $value ? date('d/m/Y H:i', strtotime($value)) : '-';
}
