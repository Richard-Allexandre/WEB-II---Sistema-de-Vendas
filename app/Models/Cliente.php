<?php
declare(strict_types=1);

namespace App\Models;

/** MODEL: entidade Cliente + regras de validação do domínio. */
final class Cliente
{
    public function __construct(
        public ?int $id = null,
        public string $nome = '',
        public string $cpf = '',
        public ?string $email = null,
        public ?string $telefone = null,
        public ?string $endereco = null,
        public ?string $criadoEm = null,
    ) {}

    /** Cria a entidade a partir de $_POST (formulário). */
    public static function fromInput(array $in): self
    {
        $email = trim((string) ($in['email'] ?? ''));
        $tel = preg_replace('/\D/', '', (string) ($in['telefone'] ?? ''));
        $end = trim((string) ($in['endereco'] ?? ''));

        return new self(
            nome: trim((string) ($in['nome'] ?? '')),
            cpf: preg_replace('/\D/', '', (string) ($in['cpf'] ?? '')),
            email: $email !== '' ? $email : null,
            telefone: $tel !== '' ? $tel : null,
            endereco: $end !== '' ? $end : null,
        );
    }

    /** Cria a entidade a partir de uma linha do banco. */
    public static function fromRow(array $r): self
    {
        return new self(
            id: (int) $r['id'],
            nome: $r['nome'],
            cpf: $r['cpf'],
            email: $r['email'],
            telefone: $r['telefone'],
            endereco: $r['endereco'],
            criadoEm: $r['criado_em'],
        );
    }

    /** @return array<string,string> campo => mensagem de erro */
    public function validar(): array
    {
        $erros = [];

        if (mb_strlen($this->nome) < 3) {
            $erros['nome'] = 'Informe o nome (mínimo 3 caracteres).';
        }
        if (!self::cpfValido($this->cpf)) {
            $erros['cpf'] = 'CPF inválido.';
        }
        if ($this->email !== null && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $erros['email'] = 'E-mail inválido.';
        }
        if ($this->telefone !== null && !preg_match('/^\d{10,11}$/', $this->telefone)) {
            $erros['telefone'] = 'Telefone deve ter 10 ou 11 dígitos (com DDD).';
        }

        return $erros;
    }

    public static function cpfValido(string $cpf): bool
    {
        if (!preg_match('/^\d{11}$/', $cpf) || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }
        for ($t = 9; $t < 11; $t++) {
            $soma = 0;
            for ($i = 0; $i < $t; $i++) {
                $soma += (int) $cpf[$i] * (($t + 1) - $i);
            }
            $digito = ((10 * $soma) % 11) % 10;
            if ((int) $cpf[$t] !== $digito) {
                return false;
            }
        }
        return true;
    }

    public function cpfFormatado(): string
    {
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $this->cpf);
    }
}
