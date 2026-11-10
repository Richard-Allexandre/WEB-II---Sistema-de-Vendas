<?php
declare(strict_types=1);

namespace App\Patterns\Strategy;

final class DescontoPercentual implements DescontoStrategy
{
    public function __construct(private float $percentual)
    {
        $this->percentual = max(0.0, min(100.0, $percentual));
    }

    public function calcular(float $subtotal): float
    {
        return round($subtotal * $this->percentual / 100, 2);
    }

    public function descricao(): string
    {
        return 'Desconto de ' . rtrim(rtrim(number_format($this->percentual, 2, ',', ''), '0'), ',') . '%';
    }
}
