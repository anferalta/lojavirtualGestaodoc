<?php
namespace App\Core;

class Helpers {

    public static function url(string $path = ''): string {
        $base = rtrim($_ENV['BASE_URL'] ?? '', '/');

        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }

        return $base . '/' . ltrim($path, '/');
    }

    public static function redirecionar(?string $url = null): void {
        $url = $url ?? '/';

        if (preg_match('/^https?:\/\//i', $url)) {
            header("Location: $url");
            exit;
        }

        header("Location: " . self::url($url));
        exit;
    }

    public static function asset(string $path): string {
        return self::url('assets/' . ltrim($path, '/'));
    }

    public static function can(string $permission): bool {
        $uid = Sessao::get('user_id');
        if (!$uid) {
            return false;
        }

        $db = Conexao::getInstancia();
        $perm = new Permission($db);

        return $perm->userHas($uid, $permission);
    }

    public static function paginate(int $total, int $perPage, int $currentPage): array {
        $totalPages = max(1, (int) ceil($total / $perPage));
        $currentPage = max(1, min($currentPage, $totalPages));

        return [
            'total' => $total,
            'per_page' => $perPage,
            'current' => $currentPage,
            'total_pages' => $totalPages,
            'has_prev' => $currentPage > 1,
            'has_next' => $currentPage < $totalPages,
            'prev_page' => $currentPage > 1 ? $currentPage - 1 : null,
            'next_page' => $currentPage < $totalPages ? $currentPage + 1 : null,
        ];
    }

    public static function slug(string $string): string {
        // Converter para minúsculas
        $slug = strtolower($string);

        // Remover acentos
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $slug);

        // Substituir caracteres não permitidos por hífen
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

        // Remover hífens duplicados
        $slug = preg_replace('/-+/', '-', $slug);

        // Remover hífens do início e fim
        return trim($slug, '-');
    }
}
