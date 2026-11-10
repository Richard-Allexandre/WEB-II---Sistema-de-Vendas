<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Produto;
use DomainException;
use PDO;

/** DESIGN PATTERN: Repository — CRUD de Produto e controle de estoque (coluna `quantidade`). */
final class ProdutoRepository
{
    public function __construct(private PDO $pdo) {}

    /** @return Produto[] */
    public function all(string $busca = ''): array
    {
        if ($busca !== '') {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM produtos WHERE nome LIKE :q1 OR cod LIKE :q2 OR categoria LIKE :q3 ORDER BY nome'
            );
            $like = '%' . $busca . '%';
            $stmt->execute(['q1' => $like, 'q2' => $like, 'q3' => $like]);
        } else {
            $stmt = $this->pdo->query('SELECT * FROM produtos ORDER BY nome');
        }
        return array_map([Produto::class, 'fromRow'], $stmt->fetchAll());
    }

    public function find(int $id): ?Produto
    {
        $stmt = $this->pdo->prepare('SELECT * FROM produtos WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? Produto::fromRow($row) : null;
    }

    public function codExiste(string $cod, ?int $ignorarId = null): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM produtos WHERE cod = :cod AND id <> :id');
        $stmt->execute(['cod' => $cod, 'id' => $ignorarId ?? 0]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function create(Produto $p): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO produtos (cod, nome, categoria, validade, quantidade, preco_unitario, preco_pacote, imagem)
             VALUES (:cod, :nome, :categoria, :validade, :quantidade, :pu, :pp, :imagem)'
        );
        $stmt->execute($this->params($p));
        return (int) $this->pdo->lastInsertId();
    }

    public function update(Produto $p): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE produtos
             SET cod = :cod, nome = :nome, categoria = :categoria, validade = :validade,
                 quantidade = :quantidade, preco_unitario = :pu, preco_pacote = :pp, imagem = :imagem
             WHERE id = :id'
        );
        $stmt->execute($this->params($p) + ['id' => $p->id]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM produtos WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function foiVendido(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM itens_venda WHERE produto_id = :id');
        $stmt->execute(['id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /** Baixa atômica: só altera se houver saldo suficiente (evita estoque negativo). */
    public function baixarEstoque(int $id, int $quantidade, string $nome = ''): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE produtos SET quantidade = quantidade - :q WHERE id = :id AND quantidade >= :q2'
        );
        $stmt->execute(['q' => $quantidade, 'id' => $id, 'q2' => $quantidade]);

        if ($stmt->rowCount() === 0) {
            throw new DomainException('Estoque insuficiente para "' . ($nome ?: "produto #{$id}") . '".');
        }
    }

    public function reporEstoque(int $id, int $quantidade): void
    {
        $stmt = $this->pdo->prepare('UPDATE produtos SET quantidade = quantidade + :q WHERE id = :id');
        $stmt->execute(['q' => $quantidade, 'id' => $id]);
    }

    private function params(Produto $p): array
    {
        return [
            'cod'        => $p->cod,
            'nome'       => $p->nome,
            'categoria'  => $p->categoria,
            'validade'   => $p->validade,
            'quantidade' => $p->quantidade,
            'pu'         => $p->precoUnitario,
            'pp'         => $p->precoPacote,
            'imagem'     => $p->imagem,
        ];
    }
}
