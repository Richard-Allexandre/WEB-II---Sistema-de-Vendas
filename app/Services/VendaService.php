<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\ItemVenda;
use App\Models\Venda;
use App\Patterns\Factory\DescontoFactory;
use App\Patterns\Observer\VendaObserver;
use App\Repositories\VendaRepository;
use DomainException;
use PDO;
use Throwable;

/**
 * Regras de negócio da venda e SUBJECT do padrão Observer.
 * Usa a Factory para obter a Strategy de desconto e notifica os observers
 * (estoque, log) dentro de uma transação: se algo falhar, tudo é desfeito.
 */
final class VendaService
{
    /** @var VendaObserver[] */
    private array $observers = [];

    public function __construct(private PDO $pdo, private VendaRepository $vendas) {}

    public function attach(VendaObserver $observer): void
    {
        $this->observers[] = $observer;
    }

    private function notify(string $evento, Venda $venda): void
    {
        foreach ($this->observers as $observer) {
            $observer->atualizar($evento, $venda);
        }
    }

    /**
     * CREATE: finaliza o carrinho gerando uma venda.
     * @param ItemVenda[] $itens
     */
    public function finalizar(array $itens, ?int $clienteId, string $forma, string $tipoDesconto, float $valorDesconto): Venda
    {
        if ($itens === []) {
            throw new DomainException('O carrinho está vazio.');
        }
        if (!isset(Venda::FORMAS_PAGAMENTO[$forma])) {
            throw new DomainException('Forma de pagamento inválida.');
        }

        $subtotal = round(array_sum(array_map(fn (ItemVenda $i) => $i->subtotal(), $itens)), 2);

        $strategy = DescontoFactory::criar($tipoDesconto, $valorDesconto);   // Factory -> Strategy
        $desconto = $strategy->calcular($subtotal);

        $venda = new Venda(
            clienteId: $clienteId,
            subtotal: $subtotal,
            tipoDesconto: $tipoDesconto ?: 'nenhum',
            descontoInformado: $tipoDesconto === 'nenhum' ? 0.0 : $valorDesconto,
            desconto: $desconto,
            total: round($subtotal - $desconto, 2),
            formaPagamento: $forma,
            itens: $itens,
        );

        $this->transacao(function () use ($venda): void {
            $this->vendas->create($venda);
            $this->notify(VendaObserver::FINALIZADA, $venda);
        });

        return $venda;
    }

    /** UPDATE: cancela a venda e devolve os itens ao estoque. */
    public function cancelar(int $id): void
    {
        $venda = $this->vendas->find($id) ?? throw new DomainException('Venda não encontrada.');
        if (!$venda->finalizada()) {
            throw new DomainException('Esta venda já está cancelada.');
        }

        $this->transacao(function () use ($venda): void {
            $this->vendas->atualizarStatus($venda->id, Venda::STATUS_CANCELADA);
            $this->notify(VendaObserver::CANCELADA, $venda);
        });
    }

    /** DELETE: só vendas canceladas podem ser excluídas (preserva o histórico financeiro). */
    public function excluir(int $id): void
    {
        $venda = $this->vendas->find($id) ?? throw new DomainException('Venda não encontrada.');
        if ($venda->finalizada()) {
            throw new DomainException('Cancele a venda antes de excluí-la.');
        }
        $this->vendas->delete($id);
    }

    private function transacao(callable $acao): void
    {
        $this->pdo->beginTransaction();
        try {
            $acao();
            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
}
