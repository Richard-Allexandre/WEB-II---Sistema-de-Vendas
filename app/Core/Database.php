<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * DESIGN PATTERN: Singleton.
 * Garante uma única conexão PDO por requisição, reutilizada por todos os repositórios.
 */
final class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dsn = Config::get('db.dsn');
            $user = Config::get('db.user');
            $pass = Config::get('db.pass');

            if (!$dsn) {
                $dsn = sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                    Config::get('db.host'),
                    Config::get('db.port'),
                    Config::get('db.name'),
                    Config::get('db.charset')
                );
            }

            self::$instance = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }

        return self::$instance;
    }
}
