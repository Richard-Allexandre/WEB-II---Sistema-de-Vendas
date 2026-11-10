<?php
declare(strict_types=1);

namespace App\Models;

/** MODEL: entidade Produto (mesmas colunas da tabela `produtos` usada na aula). */
final class Produto
{
    public function __construct(
        public ?int $id = null,
        public string $cod = '',
        public string $nome = '',
        public string $categoria = '',
        public string $validade = '',
        public int $quantidade = 0,
        public float $precoUnitario = 0.0,
        public float $precoPacote = 0.0,
        public ?string $imagem = null,
    ) {}

    public static function fromRow(array $r): self
    {
        return new self(
            id: (int) $r['id'],
            cod: $r['cod'],
            nome: $r['nome'],
            categoria: $r['categoria'],
            validade: (string) $r['validade'],
            quantidade: (int) $r['quantidade'],
            precoUnitario: (float) $r['preco_unitario'],
            precoPacote: (float) $r['preco_pacote'],
            imagem: $r['imagem'] ?: null,
        );
    }

    /** Cria a entidade a partir de $_POST (a imagem é tratada à parte). */
    public static function fromInput(array $in): self
    {
        $num = static fn (string $k): float => (float) str_replace(',', '.', trim((string) ($in[$k] ?? '0')));

        return new self(
            cod: trim((string) ($in['cod'] ?? '')),
            nome: trim((string) ($in['nome'] ?? '')),
            categoria: trim((string) ($in['categoria'] ?? '')),
            validade: trim((string) ($in['validade'] ?? '')),
            quantidade: (int) ($in['quantidade'] ?? 0),
            precoUnitario: $num('preco_unitario'),
            precoPacote: $num('preco_pacote'),
        );
    }

    /** @return array<string,string> campo => mensagem de erro */
    public function validar(): array
    {
        $erros = [];

        if ($this->cod === '' || mb_strlen($this->cod) > 30) {
            $erros['cod'] = 'Informe o código (até 30 caracteres).';
        }
        if (mb_strlen($this->nome) < 2) {
            $erros['nome'] = 'Informe o nome do produto.';
        }
        if ($this->categoria === '') {
            $erros['categoria'] = 'Informe a categoria.';
        }
        $d = \DateTime::createFromFormat('Y-m-d', $this->validade);
        if (!$d || $d->format('Y-m-d') !== $this->validade) {
            $erros['validade'] = 'Informe uma data de validade válida.';
        }
        if ($this->quantidade < 0) {
            $erros['quantidade'] = 'A quantidade não pode ser negativa.';
        }
        if ($this->precoUnitario <= 0) {
            $erros['preco_unitario'] = 'O preço unitário deve ser maior que zero.';
        }
        if ($this->precoPacote < 0) {
            $erros['preco_pacote'] = 'O preço do pacote não pode ser negativo.';
        }

        return $erros;
    }
}
