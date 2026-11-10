<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\ItemVenda;
use App\Repositories\ProdutoRepository;
use DomainException;

/**
 * Regras do carrinho de vendas. O carrinho vive na sessão como [produto_id => quantidade];
 * preços e nomes são sempre relidos do banco (o cliente nunca informa preço).
 */
final class CarrinhoService
{
    public function __construct(private ProdutoRepository $produtos) {}

    /** CREATE: adiciona (ou soma) um produto no carrinho. */
    public function adicionar(int $produtoId, int $quantidade): void
    {
        if ($quantidade < 1) {
            throw new DomainException('A quantidade deve ser de pelo menos 1.');
        }
        $produto = $this->produtos->find($produtoId) ?? throw new DomainException('Produto não encontrado.');

        $novaQtd = ($_SESSION['carrinho'][$produtoId] ?? 0) + $quantidade;
        if ($novaQtd > $produto->quantidade) {
            throw new DomainException("Estoque insuficiente para \"{$produto->nome}\" (disponível: {$produto->quantidade}).");
        }
        $_SESSION['carrinho'][$produtoId] = $novaQtd;
    }

    /** UPDATE: define a nova quantidade (0 remove o item). */
    public function atualizar(int $produtoId, int $quantidade): void
    {
        if (!isset($_SESSION['carrinho'][$produtoId])) {
            throw new DomainException('Item não está no carrinho.');
        }
        if ($quantidade < 1) {
            $this->remover($produtoId);
            return;
        }
        $produto = $this->produtos->find($produtoId) ?? throw new DomainException('Produto não encontrado.');
        if ($quantidade > $produto->quantidade) {
            throw new DomainException("Estoque insuficiente para \"{$produto->nome}\" (disponível: {$produto->quantidade}).");
        }
        $_SESSION['carrinho'][$produtoId] = $quantidade;
    }

    /** DELETE: remove um item. */
    public function remover(int $produtoId): void
    {
        unset($_SESSION['carrinho'][$produtoId]);
    }

    /** DELETE: esvazia o carrinho. */
    public function limpar(): void
    {
        $_SESSION['carrinho'] = [];
    }

    /**
     * READ: itens do carrinho com dados atuais do banco.
     * @return ItemVenda[]
     */
    public function itens(): array
    {
        $itens = [];
        foreach ($_SESSION['carrinho'] ?? [] as $produtoId => $qtd) {
            $produto = $this->produtos->find((int) $produtoId);
            if ($produto === null) {
                unset($_SESSION['carrinho'][$produtoId]);
                continue;
            }
            $itens[] = new ItemVenda($produto->id, $produto->nome, $produto->precoUnitario, (int) $qtd);
        }
        return $itens;
    }

    public function subtotal(): float
    {
        return round(array_sum(array_map(fn (ItemVenda $i) => $i->subtotal(), $this->itens())), 2);
    }
}
