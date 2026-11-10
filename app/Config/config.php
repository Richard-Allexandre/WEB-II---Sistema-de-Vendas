<?php
/**
 * Configurações da aplicação.
 * Cada valor pode ser sobrescrito por variável de ambiente (útil em produção).
 */
return [
    'app' => [
        'name'     => 'PDV MVC',
        'base_url' => getenv('BASE_URL') ?: '',   // ex.: /pdv/public quando rodar em subpasta do Apache
        'debug'    => (getenv('APP_DEBUG') ?: '1') === '1',
    ],
    'db' => [
        'dsn'     => getenv('DB_DSN') ?: null,    // opcional: sobrescreve host/port/name (ex.: sqlite para testes)
        'host'    => getenv('DB_HOST') ?: '127.0.0.1',
        'port'    => getenv('DB_PORT') ?: '3306',
        'name'    => getenv('DB_NAME') ?: 'pdv',
        'user'    => getenv('DB_USER') ?: 'root',
        'pass'    => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ],
];
