<?php
namespace app\Core;

class Auth
{
    /*
    |--------------------------------------------------------------------------
    | SESSÃO / LOGIN
    |--------------------------------------------------------------------------
    */

    public static function login(object $user): void
    {
        $_SESSION['user_id']    = $user->id;
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_nome']  = $user->nome ?? '';
    }

    public static function logout(): void
    {
        unset($_SESSION['user_id'], $_SESSION['user_email'], $_SESSION['user_nome'], $_SESSION['2fa_validado']);
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function email(): ?string
    {
        return $_SESSION['user_email'] ?? null;
    }

    public static function user()
    {
        if (!self::check()) {
            return null;
        }

        $db = Conexao::getInstancia();
        $stm = $db->prepare("SELECT * FROM utilizadores WHERE id = :id LIMIT 1");
        $stm->execute(['id' => self::id()]);
        return $stm->fetch(\PDO::FETCH_OBJ);
    }

    /*
    |--------------------------------------------------------------------------
    | TENTATIVAS DE LOGIN / BLOQUEIO
    |--------------------------------------------------------------------------
    */

    public static function registarFalhaLogin(string $email): void
    {
        $email = trim(strtolower($email));
        $db = Conexao::getInstancia();

        $stmt = $db->prepare("SELECT * FROM login_tentativas WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(\PDO::FETCH_OBJ);

        // Primeira tentativa
        if (!$row) {
            $db->prepare("
                INSERT INTO login_tentativas (email, tentativas) 
                VALUES (:email, 1)
            ")->execute(['email' => $email]);

            return;
        }

        $tentativas = $row->tentativas + 1;

        // Bloqueio após 5 falhas
        if ($tentativas >= 5) {
            $db->prepare("
                UPDATE login_tentativas 
                SET tentativas = :t, bloqueado_ate = DATE_ADD(NOW(), INTERVAL 15 MINUTE)
                WHERE email = :email
            ")->execute(['t' => $tentativas, 'email' => $email]);

            self::logAuth(null, $email, 'login_bloqueado');
            return;
        }

        // Apenas incrementa
        $db->prepare("
            UPDATE login_tentativas 
            SET tentativas = :t 
            WHERE email = :email
        ")->execute(['t' => $tentativas, 'email' => $email]);
    }

    public static function limparTentativas(int $userId): void
    {
        $db = Conexao::getInstancia();

        $stm = $db->prepare("SELECT email FROM utilizadores WHERE id = :id");
        $stm->execute(['id' => $userId]);
        $email = $stm->fetchColumn();

        if (!$email) return;

        $db->prepare("DELETE FROM login_tentativas WHERE email = :email")
           ->execute(['email' => $email]);
    }

    public static function estaBloqueado(object $user): bool
    {
        $db = Conexao::getInstancia();

        $stmt = $db->prepare("
            SELECT bloqueado_ate 
            FROM login_tentativas 
            WHERE email = :email
        ");
        $stmt->execute(['email' => $user->email]);
        $row = $stmt->fetch(\PDO::FETCH_OBJ);

        if (!$row || !$row->bloqueado_ate) {
            return false;
        }

        return strtotime($row->bloqueado_ate) > time();
    }

    /*
    |--------------------------------------------------------------------------
    | LOGS DE AUTENTICAÇÃO
    |--------------------------------------------------------------------------
    */

    public static function logAuth(?int $userId, ?string $email, string $acao): void
    {
        $db = Conexao::getInstancia();

        $sql = "
            INSERT INTO logs_auth (user_id, email, acao, ip, user_agent)
            VALUES (:uid, :email, :acao, :ip, :ua)
        ";

        $stm = $db->prepare($sql);
        $stm->execute([
            'uid'   => $userId,
            'email' => $email,
            'acao'  => $acao,
            'ip'    => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'ua'    => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | API TOKENS
    |--------------------------------------------------------------------------
    */

    public static function gerarTokenApi(int $userId): string
    {
        $token = bin2hex(random_bytes(32));

        $db = Conexao::getInstancia();
        $db->prepare("
            INSERT INTO api_tokens (user_id, token) 
            VALUES (:uid, :token)
        ")->execute(['uid' => $userId, 'token' => $token]);

        return $token;
    }
}