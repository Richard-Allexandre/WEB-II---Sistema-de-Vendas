<?php
declare(strict_types=1);

namespace App\Patterns\Strategy;

/**
 * DESIGN PATTERN: Strategy.
 * Define a família de algoritmos de desconto; a venda escolhe qual usar em tempo de execução.
 */
interface DescontoStrategy
{
    /** Retorna o VALOR do desconto (em reais) para o subtotal informado. */
    public function calcular(float $subtotal): float;

    public function descricao(): string;
}
