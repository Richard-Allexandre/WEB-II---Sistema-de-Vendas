<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Venda;
use App\Patterns\Factory\DescontoFactory;
use App\Repositories\ClienteRepository;
use App\Repositories\ProdutoRepository;
use App\Services\CarrinhoService;
use App\Services\VendaService;
use DomainException;
use InvalidArgumentException;

/** CONTROLLER do carrinho de vendas (tela de PDV). */
final class CarrinhoController extends Controller
{
    public function __construct(
        private CarrinhoService $carrinho,
        private ProdutoRepository $produtos,
        private ClienteRepository $clientes,
        private VendaService $vendas,
    ) {}

    public function index(): void
    {
        $busca = trim((string) ($_GET['q'] ?? ''));
        $this->view('carrinho/index', [
            'title'    => 'Carrinho de vendas',
            'busca'    => $busca,
            'produtos' => $this->produtos->all($busca),
            'itens'    => $this->carrinho->itens(),
            'subtotal' => $this->carrinho->subtotal(),
            'clientes' => $this->clientes->all(),
            'formas'   => Venda::FORMAS_PAGAMENTO,
            'tiposDesconto' => DescontoFactory::TIPOS,
        ]);
    }

    public function adicionar(): void
    {
        $this->validateCsrf();
        $this->executar(fn () => $this->carrinho->adicionar((int) $this->post('produto_id'), (int) $this->post('quantidade', '1')), 'Produto adicionado ao carrinho.');
    }

    public function atualizar(int $id): void
    {
        $this->validateCsrf();
        $this->executar(fn () => $this->carrinho->atualizar($id, (int) $this->post('quantidade')), 'Quantidade atualizada.');
    }

    public function remover(int $id): void
    {
        $this->validateCsrf();
        $this->carrinho->remover($id);
        $this->flash('success', 'Item removido.');
        $this->redirect('/carrinho');
    }

    public function limpar(): void
    {
        $this->validateCsrf();
        $this->carrinho->limpar();
        $this->flash('success', 'Carrinho esvaziado.');
        $this->redirect('/carrinho');
    }

    public function finalizar(): void
    {
        $this->validateCsrf();

        try {
            $clienteId = (int) $this->post('cliente_id');
            if ($clienteId > 0 && $this->clientes->find($clienteId) === null) {
                throw new DomainException('Cliente não encontrado.');
            }

            $venda = $this->vendas->finalizar(
                $this->carrinho->itens(),
                $clienteId > 0 ? $clienteId : null,
                $this->post('forma_pagamento'),
                $this->post('tipo_desconto', 'nenhum'),
                $this->toFloat($this->post('valor_desconto')),
            );
        } catch (DomainException | InvalidArgumentException $e) {
            $this->flash('error', $e->getMessage());
            $this->redirect('/carrinho');
        }

        $this->carrinho->limpar();
        $this->flash('success', "Venda #{$venda->id} finalizada com sucesso!");
        $this->redirect("/relatorios/{$venda->id}");
    }

    private function executar(callable $acao, string $mensagemSucesso): void
    {
        try {
            $acao();
            $this->flash('success', $mensagemSucesso);
        } catch (DomainException $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect('/carrinho');
    }
}
