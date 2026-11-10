<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Produto;
use App\Repositories\ProdutoRepository;
use App\Services\UploadService;
use DomainException;

/** CONTROLLER do CRUD de produtos (cadastro, edição, exclusão e busca da aula, agora em MVC). */
final class ProdutoController extends Controller
{
    public function __construct(private ProdutoRepository $produtos, private UploadService $upload) {}

    public function index(): void
    {
        $busca = trim((string) ($_GET['q'] ?? ''));
        $this->view('produtos/index', [
            'title'    => 'Produtos',
            'produtos' => $this->produtos->all($busca),
            'busca'    => $busca,
        ]);
    }

    public function create(): void
    {
        $this->formulario(new Produto(), [], '/produtos', 'Novo produto');
    }

    public function store(): void
    {
        $this->validateCsrf();
        $produto = Produto::fromInput($_POST);
        $erros = $this->validar($produto);

        if (!$erros) {
            try {
                $produto->imagem = $this->upload->salvar($_FILES['imagem'] ?? null);
            } catch (DomainException $e) {
                $erros['imagem'] = $e->getMessage();
            }
        }
        if ($erros) {
            $this->formulario($produto, $erros, '/produtos', 'Novo produto');
            return;
        }

        $this->produtos->create($produto);
        $this->flash('success', 'Produto cadastrado com sucesso.');
        $this->redirect('/produtos');
    }

    public function edit(int $id): void
    {
        $produto = $this->produtos->find($id);
        if ($produto === null) {
            $this->flash('error', 'Produto não encontrado.');
            $this->redirect('/produtos');
        }
        $this->formulario($produto, [], "/produtos/{$id}/atualizar", 'Editar produto');
    }

    public function update(int $id): void
    {
        $this->validateCsrf();
        $atual = $this->produtos->find($id);
        if ($atual === null) {
            $this->flash('error', 'Produto não encontrado.');
            $this->redirect('/produtos');
        }

        $produto = Produto::fromInput($_POST);
        $produto->id = $id;
        $produto->imagem = $atual->imagem;
        $erros = $this->validar($produto);

        $nova = null;
        if (!$erros) {
            try {
                $nova = $this->upload->salvar($_FILES['imagem'] ?? null);
            } catch (DomainException $e) {
                $erros['imagem'] = $e->getMessage();
            }
        }
        if ($erros) {
            $this->formulario($produto, $erros, "/produtos/{$id}/atualizar", 'Editar produto');
            return;
        }

        if ($nova !== null) {
            $this->upload->remover($atual->imagem);
            $produto->imagem = $nova;
        }
        $this->produtos->update($produto);
        $this->flash('success', 'Produto atualizado com sucesso.');
        $this->redirect('/produtos');
    }

    public function destroy(int $id): void
    {
        $this->validateCsrf();
        $produto = $this->produtos->find($id);

        if ($produto === null) {
            $this->flash('error', 'Produto não encontrado.');
        } elseif ($this->produtos->foiVendido($id)) {
            $this->flash('error', 'Este produto já tem vendas registradas e não pode ser excluído. Zere o estoque, se preferir.');
        } else {
            $this->produtos->delete($id);
            $this->upload->remover($produto->imagem);
            $this->flash('success', 'Produto excluído.');
        }
        $this->redirect('/produtos');
    }

    /** @return array<string,string> */
    private function validar(Produto $produto): array
    {
        $erros = $produto->validar();
        if (!isset($erros['cod']) && $this->produtos->codExiste($produto->cod, $produto->id)) {
            $erros['cod'] = 'Já existe um produto com este código.';
        }
        return $erros;
    }

    private function formulario(Produto $produto, array $erros, string $action, string $titulo): void
    {
        $this->view('produtos/form', [
            'title'   => $titulo,
            'produto' => $produto,
            'erros'   => $erros,
            'action'  => $action,
        ]);
    }
}
