<?php
declare(strict_types=1);

namespace App\Core;

/** Renderiza uma View dentro do layout principal. */
final class View
{
    public static function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);

        ob_start();
        require VIEW_PATH . '/' . $view . '.php';
        $content = ob_get_clean();

        require VIEW_PATH . '/layout/main.php';
    }
}
