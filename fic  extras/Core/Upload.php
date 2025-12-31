<?php
namespace app\Core;

class Upload
{
    public static function documento(array $file): array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Falha no upload do ficheiro.');
        }

        $maxSize = (int)($_ENV['UPLOAD_MAX_SIZE'] ?? 5242880);
        if ($file['size'] > $maxSize) {
            throw new \RuntimeException('Ficheiro demasiado grande.');
        }

        $allowed = explode(',', $_ENV['UPLOAD_ALLOWED_EXT'] ?? 'pdf,doc,docx');
        $allowed = array_map('strtolower', array_map('trim', $allowed));

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed, true)) {
            throw new \RuntimeException('Tipo de ficheiro não permitido.');
        }

        $uploadDir = rtrim($_ENV['UPLOAD_DIR'] ?? '', DIRECTORY_SEPARATOR);
        if (!$uploadDir || !is_dir($uploadDir)) {
            throw new \RuntimeException('Pasta de upload inválida.');
        }

        $nomeGerado = bin2hex(random_bytes(16)) . '.' . $ext;
        $destino = $uploadDir . DIRECTORY_SEPARATOR . $nomeGerado;

        if (!move_uploaded_file($file['tmp_name'], $destino)) {
            throw new \RuntimeException('Não foi possível guardar o ficheiro.');
        }

        return [
            'original' => $file['name'],
            'ficheiro' => $nomeGerado,
            'extensao' => $ext,
            'tamanho'  => $file['size'],
            'caminho'  => $destino,
        ];
    }
}