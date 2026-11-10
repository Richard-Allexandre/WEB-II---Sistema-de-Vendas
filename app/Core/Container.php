<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Container de Injeção de Dependência (composition root).
 * Cada serviço é registrado como uma "fábrica" e criado uma única vez, sob demanda.
 */
final class Container
{
    /** @var array<string, callable> */
    private array $factories = [];
    /** @var array<string, object> */
    private array $instances = [];

    public function set(string $id, callable $factory): void
    {
        $this->factories[$id] = $factory;
    }

    public function get(string $id): object
    {
        if (!isset($this->instances[$id])) {
            if (!isset($this->factories[$id])) {
                throw new RuntimeException("Serviço não registrado: {$id}");
            }
            $this->instances[$id] = ($this->factories[$id])($this);
        }
        return $this->instances[$id];
    }
}
