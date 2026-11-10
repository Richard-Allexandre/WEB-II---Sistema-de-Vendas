<?php
declare(strict_types=1);

namespace App\Services;

use DomainException;

/**
 * Upload seguro de imagem de produto (substitui o move_uploaded_file sem validação da aula):
 * limita tamanho, confere o tipo real do arquivo e grava com nome aleatório.
 */
final class UploadService
{
    private const TIPOS = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    private const MAX_BYTES = 2 * 1024 * 1024;

    public function __construct(private string $diretorio) {}

    /** Retorna o nome do arquivo salvo, ou null se nenhum arquivo foi enviado. */
    public function salvar(?array $arquivo): ?string
    {
        if ($arquivo === null || ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($arquivo['error'] !== UPLOAD_ERR_OK) {
            throw new DomainException('Falha no upload da imagem.');
        }
        if ($arquivo['size'] > self::MAX_BYTES) {
            throw new DomainException('A imagem deve ter no máximo 2 MB.');
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($arquivo['tmp_name']);
        if (!isset(self::TIPOS[$mime])) {
            throw new DomainException('Formato de imagem inválido (use JPG, PNG, WEBP ou GIF).');
        }

        if (!is_dir($this->diretorio)) {
            mkdir($this->diretorio, 0775, true);
        }
        $nome = bin2hex(random_bytes(8)) . '.' . self::TIPOS[$mime];
        if (!move_uploaded_file($arquivo['tmp_name'], $this->diretorio . '/' . $nome)) {
            throw new DomainException('Não foi possível salvar a imagem.');
        }
        return $nome;
    }

    public function remover(?string $nome): void
    {
        if ($nome) {
            $arquivo = $this->diretorio . '/' . basename($nome);
            if (is_file($arquivo)) {
                @unlink($arquivo);
            }
        }
    }
}
