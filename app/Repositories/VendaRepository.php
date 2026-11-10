<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\ItemVenda;
use App\Models\Venda;
use PDO;

/** DESIGN PATTERN: Repository — persistência e consultas de relatório de Vendas. */
final class VendaRepository
{
    public function __construct(private PDO $pdo) {}

    public function create(Venda $v): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO vendas
                (cliente_id, subtotal, tipo_desconto, valor_desconto_informado, desconto, total, forma_pagamento, status)
             VALUES
                (:cliente_id, :subtotal, :tipo, :informado, :desconto, :total, :forma, :status)'
        );
        $stmt->execute([
            'cliente_id' => $v->clienteId,
            'subtotal'   => $v->subtotal,
            'tipo'       => $v->tipoDesconto,
            'informado'  => $v->descontoInformado,
            'desconto'   => $v->desconto,
            'total'      => $v->total,
            'forma'      => $v->formaPagamento,
            'status'     => $v->status,
        ]);
        $v->id = (int) $this->pdo->lastInsertId();

        $item = $this->pdo->prepare(
            'INSERT INTO itens_venda (venda_id, produto_id, nome_produto, preco_unitario, quantidade, subtotal)
             VALUES (:venda, :produto, :nome, :preco, :qtd, :subtotal)'
        );
        foreach ($v->itens as $i) {
            $item->execute([
                'venda'    => $v->id,
                'produto'  => $i->produtoId,
                'nome'     => $i->nome,
                'preco'    => $i->precoUnitario,
                'qtd'      => $i->quantidade,
                'subtotal' => $i->subtotal(),
            ]);
        }

        return $v->id;
    }

    public function find(int $id): ?Venda
    {
        $stmt = $this->pdo->prepare(
            'SELECT v.*, c.nome AS cliente_nome
             FROM vendas v LEFT JOIN clientes c ON c.id = v.cliente_id
             WHERE v.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        $venda = Venda::fromRow($row);
        $itens = $this->pdo->prepare('SELECT * FROM itens_venda WHERE venda_id = :id ORDER BY id');
        $itens->execute(['id' => $id]);
        $venda->itens = array_map([ItemVenda::class, 'fromRow'], $itens->fetchAll());

        return $venda;
    }

    /** @return Venda[] */
    public function buscar(array $filtros): array
    {
        [$where, $params] = $this->where($filtros);
        $stmt = $this->pdo->prepare(
            "SELECT v.*, c.nome AS cliente_nome
             FROM vendas v LEFT JOIN clientes c ON c.id = v.cliente_id
             WHERE {$where}
             ORDER BY v.criado_em DESC, v.id DESC"
        );
        $stmt->execute($params);
        return array_map([Venda::class, 'fromRow'], $stmt->fetchAll());
    }

    /** @return array{qtd:int,faturamento:float,descontos:float,canceladas:int} */
    public function resumo(array $filtros): array
    {
        [$where, $params] = $this->where($filtros);
        $stmt = $this->pdo->prepare(
            "SELECT
                COALESCE(SUM(CASE WHEN v.status = 'finalizada' THEN 1 ELSE 0 END), 0)         AS qtd,
                COALESCE(SUM(CASE WHEN v.status = 'finalizada' THEN v.total ELSE 0 END), 0)    AS faturamento,
                COALESCE(SUM(CASE WHEN v.status = 'finalizada' THEN v.desconto ELSE 0 END), 0) AS descontos,
                COALESCE(SUM(CASE WHEN v.status = 'cancelada' THEN 1 ELSE 0 END), 0)           AS canceladas
             FROM vendas v WHERE {$where}"
        );
        $stmt->execute($params);
        $r = $stmt->fetch();

        return [
            'qtd'         => (int) $r['qtd'],
            'faturamento' => (float) $r['faturamento'],
            'descontos'   => (float) $r['descontos'],
            'canceladas'  => (int) $r['canceladas'],
        ];
    }

    /** Faturamento por dia (somente vendas finalizadas). */
    public function porDia(array $filtros): array
    {
        [$where, $params] = $this->where($filtros, true);
        $stmt = $this->pdo->prepare(
            "SELECT DATE(v.criado_em) AS dia, COUNT(*) AS qtd, SUM(v.total) AS total
             FROM vendas v
             WHERE {$where} AND v.status = 'finalizada'
             GROUP BY DATE(v.criado_em)
             ORDER BY dia DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Produtos mais vendidos (somente vendas finalizadas). */
    public function topProdutos(array $filtros, int $limite = 5): array
    {
        [$where, $params] = $this->where($filtros, true);
        $stmt = $this->pdo->prepare(
            "SELECT i.nome_produto AS nome, SUM(i.quantidade) AS qtd, SUM(i.subtotal) AS total
             FROM itens_venda i JOIN vendas v ON v.id = i.venda_id
             WHERE {$where} AND v.status = 'finalizada'
             GROUP BY i.produto_id, i.nome_produto
             ORDER BY qtd DESC, total DESC
             LIMIT " . (int) $limite
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function atualizarStatus(int $id, string $status): void
    {
        $stmt = $this->pdo->prepare('UPDATE vendas SET status = :s WHERE id = :id');
        $stmt->execute(['s' => $status, 'id' => $id]);
    }

    public function atualizarDados(int $id, ?int $clienteId, string $forma): void
    {
        $stmt = $this->pdo->prepare('UPDATE vendas SET cliente_id = :c, forma_pagamento = :f WHERE id = :id');
        $stmt->execute(['c' => $clienteId, 'f' => $forma, 'id' => $id]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM itens_venda WHERE venda_id = :id');
        $stmt->execute(['id' => $id]);
        $stmt = $this->pdo->prepare('DELETE FROM vendas WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /** @return array{0:string,1:array} cláusula WHERE + parâmetros */
    private function where(array $f, bool $ignorarStatus = false): array
    {
        $cond = ['1 = 1'];
        $params = [];

        if (!empty($f['de'])) {
            $cond[] = 'DATE(v.criado_em) >= :de';
            $params['de'] = $f['de'];
        }
        if (!empty($f['ate'])) {
            $cond[] = 'DATE(v.criado_em) <= :ate';
            $params['ate'] = $f['ate'];
        }
        if (!empty($f['cliente_id'])) {
            $cond[] = 'v.cliente_id = :cliente_id';
            $params['cliente_id'] = (int) $f['cliente_id'];
        }
        if (!empty($f['forma_pagamento'])) {
            $cond[] = 'v.forma_pagamento = :forma';
            $params['forma'] = $f['forma_pagamento'];
        }
        if (!$ignorarStatus && !empty($f['status'])) {
            $cond[] = 'v.status = :status';
            $params['status'] = $f['status'];
        }

        return [implode(' AND ', $cond), $params];
    }
}
