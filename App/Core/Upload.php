<?php

namespace App\Core;

class Upload
{
    private string $baseDir;
    private int $maxSize;
    private array $allowedExt;

    public function __construct()
    {
        $this->baseDir = rtrim($_ENV['UPLOAD_DIR'] ?? '', '/');

        if ($this->baseDir === '') {
            throw new \RuntimeException('UPLOAD_DIR não está definido no .env');
        }

        $this->maxSize = (int) ($_ENV['UPLOAD_MAX_SIZE'] ?? 0);
        $exts = $_ENV['UPLOAD_ALLOWED_EXT'] ?? '';
        $this->allowedExt = array_filter(array_map('strtolower', array_map('trim', explode(',', $exts))));

        // Garantir diretório
        if (!is_dir($this->baseDir)) {
            if (!mkdir($this->baseDir, 0775, true) && !is_dir($this->baseDir)) {
                throw new \RuntimeException('Não foi possível criar diretório de upload: ' . $this->baseDir);
            }
        }
    }

    /**
     * Faz upload de um ficheiro e devolve o caminho relativo (para gravar na BD).
     */
    public function uploadFile(array $file, string $subdir = ''): string
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new \RuntimeException('Erro de upload inválido.');
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException($this->translateError($file['error']));
        }

        if ($this->maxSize > 0 && $file['size'] > $this->maxSize) {
            throw new \RuntimeException('Ficheiro excede o tamanho máximo permitido.');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $this->allowedExt, true)) {
            throw new \RuntimeException('Extensão de ficheiro não permitida.');
        }

        // Diretório organizado por subdir e ano/mês: documentos/2025/01
        $subdir = trim($subdir, '/');
        $dateDir = date('Y/m');
        $targetDir = $this->baseDir
            . ($subdir ? '/' . $subdir : '')
            . '/' . $dateDir;

        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
                throw new \RuntimeException('Não foi possível criar diretório: ' . $targetDir);
            }
        }

        // Nome seguro e único
        $basename = bin2hex(random_bytes(16)); // 32 chars
        $filename = $basename . '.' . $ext;

        $targetPath = $targetDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new \RuntimeException('Falha ao mover ficheiro enviado.');
        }

        // Caminho relativo para BD (ex: documentos/2025/01/abc123.pdf)
        $relative = ($subdir ? $subdir . '/' : '') . $dateDir . '/' . $filename;

        return $relative;
    }

    private function translateError(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE,
            UPLOAD_ERR_FORM_SIZE => 'Ficheiro demasiado grande.',
            UPLOAD_ERR_PARTIAL   => 'Upload incompleto.',
            UPLOAD_ERR_NO_FILE   => 'Nenhum ficheiro foi enviado.',
            UPLOAD_ERR_NO_TMP_DIR=> 'Pasta temporária em falta.',
            UPLOAD_ERR_CANT_WRITE=> 'Falha ao escrever ficheiro em disco.',
            UPLOAD_ERR_EXTENSION => 'Upload bloqueado por extensão.',
            default              => 'Erro desconhecido no upload.',
        };
    }
}