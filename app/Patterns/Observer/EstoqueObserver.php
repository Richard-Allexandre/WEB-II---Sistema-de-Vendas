<?php
declare(strict_types=1);

namespace App\Patterns\Observer;

use App\Models\Venda;
use App\Repositories\ProdutoRepository;

/** Observer que baixa o estoque ao finalizar e repõe ao cancelar a venda. */
final class EstoqueObserver implements VendaObserver
{
    public function __construct(private ProdutoRepository $produtos) {}

    public function atualizar(string $evento, Venda $venda): void
    {
        foreach ($venda->itens as $item) {
            if ($evento === self::FINALIZADA) {
                $this->produtos->baixarEstoque($item->produtoId, $item->quantidade, $item->nome);
            } elseif ($evento === self::CANCELADA) {
                $this->produtos->reporEstoque($item->produtoId, $item->quantidade);
            }
        }
    }
}
