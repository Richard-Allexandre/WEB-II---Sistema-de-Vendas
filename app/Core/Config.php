<?php
declare(strict_types=1);

namespace App\Core;

/** Acesso centralizado às configurações: Config::get('db.host'). */
final class Config
{
    private static array $data = [];

    public static function load(string $file): void
    {
        self::$data = require $file;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = self::$data;
        foreach (explode('.', $key) as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return $default;
            }
            $value = $value[$part];
        }
        return $value;
    }
}
