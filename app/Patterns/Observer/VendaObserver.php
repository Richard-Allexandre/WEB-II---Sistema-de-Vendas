<?php
declare(strict_types=1);

namespace App\Patterns\Observer;

use App\Models\Venda;

/**
 * DESIGN PATTERN: Observer.
 * O VendaService (Subject) notifica todos os observers quando uma venda é
 * finalizada ou cancelada, sem conhecer o que cada um faz.
 */
interface VendaObserver
{
    public const FINALIZADA = 'venda.finalizada';
    public const CANCELADA = 'venda.cancelada';

    public function atualizar(string $evento, Venda $venda): void;
}
