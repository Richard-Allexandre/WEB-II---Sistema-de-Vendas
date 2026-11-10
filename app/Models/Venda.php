<?php
declare(strict_types=1);

namespace App\Models;

/** MODEL: entidade Venda (cabeçalho + itens). */
final class Venda
{
    public const FORMAS_PAGAMENTO = [
        'dinheiro'       => 'Dinheiro',
        'pix'            => 'Pix',
        'cartao_credito' => 'Cartão de crédito',
        'cartao_debito'  => 'Cartão de débito',
    ];

    public const STATUS_FINALIZADA = 'finalizada';
    public const STATUS_CANCELADA = 'cancelada';

    /** @param ItemVenda[] $itens */
    public function __construct(
        public ?int $id = null,
        public ?int $clienteId = null,
        public ?string $clienteNome = null,
        public float $subtotal = 0.0,
        public string $tipoDesconto = 'nenhum',
        public float $descontoInformado = 0.0,
        public float $desconto = 0.0,
        public float $total = 0.0,
        public string $formaPagamento = 'dinheiro',
        public string $status = self::STATUS_FINALIZADA,
        public ?string $criadoEm = null,
        public array $itens = [],
    ) {}

    public static function fromRow(array $r): self
    {
        return new self(
            id: (int) $r['id'],
            clienteId: $r['cliente_id'] !== null ? (int) $r['cliente_id'] : null,
            clienteNome: $r['cliente_nome'] ?? null,
            subtotal: (float) $r['subtotal'],
            tipoDesconto: $r['tipo_desconto'],
            descontoInformado: (float) $r['valor_desconto_informado'],
            desconto: (float) $r['desconto'],
            total: (float) $r['total'],
            formaPagamento: $r['forma_pagamento'],
            status: $r['status'],
            criadoEm: $r['criado_em'],
        );
    }

    public function finalizada(): bool
    {
        return $this->status === self::STATUS_FINALIZADA;
    }

    public function formaPagamentoLabel(): string
    {
        return self::FORMAS_PAGAMENTO[$this->formaPagamento] ?? $this->formaPagamento;
    }
}
