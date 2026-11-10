<?php
declare(strict_types=1);

namespace App\Patterns\Strategy;

final class DescontoValorFixo implements DescontoStrategy
{
    public function __construct(private float $valor)
    {
        $this->valor = max(0.0, $valor);
    }

    public function calcular(float $subtotal): float
    {
        // Nunca desconta mais do que o subtotal.
        return round(min($this->valor, $subtotal), 2);
    }

    public function descricao(): string
    {
        return 'Desconto de R$ ' . number_format($this->valor, 2, ',', '.');
    }
}
