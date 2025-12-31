<?php
namespace app\Controllers;

use app\Core\Auth;
use app\Core\Database;
use app\Core\Helpers;

class DocumentController
{
    public function index()
    {
        Auth::verificarLogin();

        $db = Database::conectar();
        $docs = $db->query("SELECT d.*, u.nome AS autor 
                            FROM documentos d
                            JOIN usuarios u ON u.id = d.criado_por
                            ORDER BY d.criado_em DESC")->fetchAll();

        return view('documentos/index.twig', [
            'documentos' => $docs,
            'title' => 'Documentos'
        ]);
    }

    public function uploadForm()
    {
        Auth::verificarLogin();
        return view('documentos/upload.twig', ['title' => 'Enviar Documento']);
    }

    public function upload()
    {
        Auth::verificarLogin();

        if (!isset($_FILES['ficheiro']) || $_FILES['ficheiro']['error'] !== 0) {
            $_SESSION['flash'] = ['mensagem' => 'Erro ao enviar ficheiro.', 'tipo' => 'danger'];
            header("Location: " . Helpers::url('/documentos/upload'));
            exit;
        }

        $titulo = $_POST['titulo'] ?? '';
        $ficheiro = $_FILES['ficheiro'];

        $ext = pathinfo($ficheiro['name'], PATHINFO_EXTENSION);
        $nomeUnico = uniqid() . '.' . $ext;

        move_uploaded_file($ficheiro['tmp_name'], __DIR__ . '/../../storage/documentos/' . $nomeUnico);

        $db = Database::conectar();
        $stmt = $db->prepare("INSERT INTO documentos (titulo, ficheiro, criado_por) VALUES (:t, :f, :u)");
        $stmt->execute([
            ':t' => $titulo,
            ':f' => $nomeUnico,
            ':u' => $_SESSION['usuario_id']
        ]);

        $_SESSION['flash'] = ['mensagem' => 'Documento enviado com sucesso!', 'tipo' => 'info'];
        header("Location: " . Helpers::url('/documentos'));
        exit;
    }
}