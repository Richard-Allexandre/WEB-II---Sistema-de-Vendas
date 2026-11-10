<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Cliente;
use App\Repositories\ClienteRepository;

/** CONTROLLER do CRUD de clientes: recebe a requisição, usa o Repository e escolhe a View. */
final class ClienteController extends Controller
{
    public function __construct(private ClienteRepository $clientes) {}

    public function index(): void
    {
        $busca = trim((string) ($_GET['q'] ?? ''));
        $this->view('clientes/index', [
            'title'    => 'Clientes',
            'clientes' => $this->clientes->all($busca),
            'busca'    => $busca,
        ]);
    }

    public function create(): void
    {
        $this->formulario(new Cliente(), [], '/clientes', 'Novo cliente');
    }

    public function store(): void
    {
        $this->validateCsrf();
        $cliente = Cliente::fromInput($_POST);
        $erros = $this->validar($cliente);

        if ($erros) {
            $this->formulario($cliente, $erros, '/clientes', 'Novo cliente');
            return;
        }

        $this->clientes->create($cliente);
        $this->flash('success', 'Cliente cadastrado com sucesso.');
        $this->redirect('/clientes');
    }

    public function edit(int $id): void
    {
        $cliente = $this->clientes->find($id);
        if ($cliente === null) {
            $this->flash('error', 'Cliente não encontrado.');
            $this->redirect('/clientes');
        }
        $this->formulario($cliente, [], "/clientes/{$id}/atualizar", 'Editar cliente');
    }

    public function update(int $id): void
    {
        $this->validateCsrf();
        if ($this->clientes->find($id) === null) {
            $this->flash('error', 'Cliente não encontrado.');
            $this->redirect('/clientes');
        }

        $cliente = Cliente::fromInput($_POST);
        $cliente->id = $id;
        $erros = $this->validar($cliente);

        if ($erros) {
            $this->formulario($cliente, $erros, "/clientes/{$id}/atualizar", 'Editar cliente');
            return;
        }

        $this->clientes->update($cliente);
        $this->flash('success', 'Cliente atualizado com sucesso.');
        $this->redirect('/clientes');
    }

    public function destroy(int $id): void
    {
        $this->validateCsrf();
        $this->clientes->delete($id);
        $this->flash('success', 'Cliente excluído. As vendas dele foram mantidas como "Consumidor".');
        $this->redirect('/clientes');
    }

    /** @return array<string,string> */
    private function validar(Cliente $cliente): array
    {
        $erros = $cliente->validar();
        if (!isset($erros['cpf']) && $this->clientes->cpfExiste($cliente->cpf, $cliente->id)) {
            $erros['cpf'] = 'Já existe um cliente com este CPF.';
        }
        return $erros;
    }

    private function formulario(Cliente $cliente, array $erros, string $action, string $titulo): void
    {
        $this->view('clientes/form', [
            'title'   => $titulo,
            'cliente' => $cliente,
            'erros'   => $erros,
            'action'  => $action,
        ]);
    }
}
