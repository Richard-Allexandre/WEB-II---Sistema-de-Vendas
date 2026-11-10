<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Venda;
use App\Repositories\ClienteRepository;
use App\Repositories\VendaRepository;
use App\Services\VendaService;
use DomainException;

/** CONTROLLER dos relatórios de vendas (consulta, edição, cancelamento, exclusão, exportação). */
final class RelatorioController extends Controller
{
    public function __construct(
        private VendaRepository $vendas,
        private VendaService $service,
        private ClienteRepository $clientes,
    ) {}

    public function index(): void
    {
        $filtros = $this->filtros();
        $resumo = $this->vendas->resumo($filtros);
        $resumo['ticket_medio'] = $resumo['qtd'] > 0 ? round($resumo['faturamento'] / $resumo['qtd'], 2) : 0.0;

        $this->view('relatorios/index', [
            'title'    => 'Relatórios de vendas',
            'filtros'  => $filtros,
            'resumo'   => $resumo,
            'vendas'   => $this->vendas->buscar($filtros),
            'porDia'   => $this->vendas->porDia($filtros),
            'top'      => $this->vendas->topProdutos($filtros),
            'clientes' => $this->clientes->all(),
            'formas'   => Venda::FORMAS_PAGAMENTO,
        ]);
    }

    public function show(int $id): void
    {
        $venda = $this->vendas->find($id);
        if ($venda === null) {
            $this->flash('error', 'Venda não encontrada.');
            $this->redirect('/relatorios');
        }

        $this->view('relatorios/show', [
            'title'    => "Venda #{$id}",
            'venda'    => $venda,
            'clientes' => $this->clientes->all(),
            'formas'   => Venda::FORMAS_PAGAMENTO,
        ]);
    }

    public function update(int $id): void
    {
        $this->validateCsrf();
        $venda = $this->vendas->find($id);

        if ($venda === null || !$venda->finalizada()) {
            $this->flash('error', 'Somente vendas finalizadas podem ser editadas.');
            $this->redirect("/relatorios/{$id}");
        }

        $forma = $this->post('forma_pagamento');
        if (!isset(Venda::FORMAS_PAGAMENTO[$forma])) {
            $this->flash('error', 'Forma de pagamento inválida.');
            $this->redirect("/relatorios/{$id}");
        }

        $clienteId = (int) $this->post('cliente_id');
        if ($clienteId > 0 && $this->clientes->find($clienteId) === null) {
            $this->flash('error', 'Cliente não encontrado.');
            $this->redirect("/relatorios/{$id}");
        }

        $this->vendas->atualizarDados($id, $clienteId > 0 ? $clienteId : null, $forma);
        $this->flash('success', 'Venda atualizada.');
        $this->redirect("/relatorios/{$id}");
    }

    public function cancelar(int $id): void
    {
        $this->validateCsrf();
        try {
            $this->service->cancelar($id);
            $this->flash('success', "Venda #{$id} cancelada e estoque reposto.");
        } catch (DomainException $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect("/relatorios/{$id}");
    }

    public function destroy(int $id): void
    {
        $this->validateCsrf();
        try {
            $this->service->excluir($id);
            $this->flash('success', "Venda #{$id} excluída.");
            $this->redirect('/relatorios');
        } catch (DomainException $e) {
            $this->flash('error', $e->getMessage());
            $this->redirect("/relatorios/{$id}");
        }
    }

    public function exportar(): void
    {
        $vendas = $this->vendas->buscar($this->filtros());

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="relatorio-vendas-' . date('Ymd-His') . '.csv"');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF"); // BOM para o Excel reconhecer UTF-8
        fputcsv($out, ['Venda', 'Data', 'Cliente', 'Pagamento', 'Subtotal', 'Desconto', 'Total', 'Status'], ';');
        foreach ($vendas as $v) {
            fputcsv($out, [
                $v->id,
                date_br($v->criadoEm),
                $v->clienteNome ?? 'Consumidor',
                $v->formaPagamentoLabel(),
                number_format($v->subtotal, 2, ',', ''),
                number_format($v->desconto, 2, ',', ''),
                number_format($v->total, 2, ',', ''),
                $v->status,
            ], ';');
        }
        fclose($out);
        exit;
    }

    /** Lê e saneia os filtros da query string. */
    private function filtros(): array
    {
        $data = static fn (string $k): string =>
            preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) ($_GET[$k] ?? '')) ? $_GET[$k] : '';

        $status = (string) ($_GET['status'] ?? '');
        $forma = (string) ($_GET['forma_pagamento'] ?? '');

        return [
            'de'              => $data('de'),
            'ate'             => $data('ate'),
            'cliente_id'      => (int) ($_GET['cliente_id'] ?? 0) ?: '',
            'status'          => in_array($status, [Venda::STATUS_FINALIZADA, Venda::STATUS_CANCELADA], true) ? $status : '',
            'forma_pagamento' => isset(Venda::FORMAS_PAGAMENTO[$forma]) ? $forma : '',
        ];
    }
}
