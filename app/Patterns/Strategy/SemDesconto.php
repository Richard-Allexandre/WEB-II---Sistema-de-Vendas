<?php
declare(strict_types=1);

namespace App\Patterns\Strategy;

final class SemDesconto implements DescontoStrategy
{
    public function calcular(float $subtotal): float
    {
        return 0.0;
    }

    public function descricao(): string
    {
        return 'Sem desconto';
    }
}
