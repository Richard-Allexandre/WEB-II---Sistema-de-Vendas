<?php
declare(strict_types=1);

namespace App\Models;

/** MODEL: linha de uma venda (usada no carrinho e na venda gravada). */
final class ItemVenda
{
    public function __construct(
        public int $produtoId,
        public string $nome,
        public float $precoUnitario,
        public int $quantidade,
    ) {}

    public function subtotal(): float
    {
        return round($this->precoUnitario * $this->quantidade, 2);
    }

    public static function fromRow(array $r): self
    {
        return new self((int) $r['produto_id'], $r['nome_produto'], (float) $r['preco_unitario'], (int) $r['quantidade']);
    }
}
