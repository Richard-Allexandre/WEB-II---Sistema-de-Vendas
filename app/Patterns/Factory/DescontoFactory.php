<?php
declare(strict_types=1);

namespace App\Patterns\Factory;

use App\Patterns\Strategy\DescontoPercentual;
use App\Patterns\Strategy\DescontoStrategy;
use App\Patterns\Strategy\DescontoValorFixo;
use App\Patterns\Strategy\SemDesconto;
use InvalidArgumentException;

/**
 * DESIGN PATTERN: Factory (Simple Factory).
 * Centraliza a criação da Strategy correta a partir do tipo escolhido na tela,
 * escondendo do Service/Controller as classes concretas.
 */
final class DescontoFactory
{
    public const TIPOS = [
        'nenhum'     => 'Sem desconto',
        'percentual' => 'Percentual (%)',
        'valor'      => 'Valor fixo (R$)',
    ];

    public static function criar(string $tipo, float $valor = 0.0): DescontoStrategy
    {
        return match ($tipo) {
            'nenhum', ''  => new SemDesconto(),
            'percentual'  => new DescontoPercentual($valor),
            'valor'       => new DescontoValorFixo($valor),
            default       => throw new InvalidArgumentException("Tipo de desconto inválido: {$tipo}"),
        };
    }
}
