<?php
declare(strict_types=1);

namespace App\Patterns\Observer;

use App\Models\Venda;

/** Observer que registra um log de auditoria em storage/vendas.log. */
final class LogObserver implements VendaObserver
{
    public function __construct(private string $arquivo) {}

    public function atualizar(string $evento, Venda $venda): void
    {
        $linha = sprintf(
            "[%s] %s venda=#%d total=%s pagamento=%s\n",
            date('Y-m-d H:i:s'),
            $evento,
            (int) $venda->id,
            number_format($venda->total, 2, '.', ''),
            $venda->formaPagamento
        );
        @file_put_contents($this->arquivo, $linha, FILE_APPEND | LOCK_EX);
    }
}
