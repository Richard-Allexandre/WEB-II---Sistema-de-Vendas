<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Cliente;
use PDO;

/** DESIGN PATTERN: Repository — isola o SQL de Cliente do resto da aplicação. */
final class ClienteRepository
{
    public function __construct(private PDO $pdo) {}

    /** @return Cliente[] */
    public function all(string $busca = ''): array
    {
        if ($busca !== '') {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM clientes
                 WHERE nome LIKE :q1 OR cpf LIKE :q2 OR email LIKE :q3
                 ORDER BY nome'
            );
            $like = '%' . $busca . '%';
            $stmt->execute(['q1' => $like, 'q2' => $like, 'q3' => $like]);
        } else {
            $stmt = $this->pdo->query('SELECT * FROM clientes ORDER BY nome');
        }
        return array_map([Cliente::class, 'fromRow'], $stmt->fetchAll());
    }

    public function find(int $id): ?Cliente
    {
        $stmt = $this->pdo->prepare('SELECT * FROM clientes WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? Cliente::fromRow($row) : null;
    }

    public function cpfExiste(string $cpf, ?int $ignorarId = null): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM clientes WHERE cpf = :cpf AND id <> :id');
        $stmt->execute(['cpf' => $cpf, 'id' => $ignorarId ?? 0]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function create(Cliente $c): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO clientes (nome, cpf, email, telefone, endereco)
             VALUES (:nome, :cpf, :email, :telefone, :endereco)'
        );
        $stmt->execute($this->params($c));
        return (int) $this->pdo->lastInsertId();
    }

    public function update(Cliente $c): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE clientes
             SET nome = :nome, cpf = :cpf, email = :email, telefone = :telefone, endereco = :endereco
             WHERE id = :id'
        );
        $stmt->execute($this->params($c) + ['id' => $c->id]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM clientes WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    private function params(Cliente $c): array
    {
        return [
            'nome'     => $c->nome,
            'cpf'      => $c->cpf,
            'email'    => $c->email,
            'telefone' => $c->telefone,
            'endereco' => $c->endereco,
        ];
    }
}
