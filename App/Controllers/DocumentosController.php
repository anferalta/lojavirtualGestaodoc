<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Conexao;
use App\Core\Documento;
use App\Core\Validator;
use App\Core\Sessao;
use App\Core\Helpers;
use App\Core\Upload;
use App\Core\Auth;

class DocumentosController extends BaseController
{
    private Documento $docModel;

    public function __construct()
    {
        parent::__construct();
        $this->docModel = new Documento(Conexao::getInstancia());
    }

    /**
     * Listagem de documentos
     */
    public function index()
    {
        $docs = $this->docModel->all();

        return $this->view('documentos/index.twig', [
            'documentos' => $docs,
            'csrf'       => Sessao::csrf(),
        ]);
    }

    /**
     * Formulário de criação
     */
    public function criar()
    {
        return $this->view('documentos/form.twig', [
            'acao'        => 'criar',
            'doc'         => null,
            'form_old'    => [],
            'form_errors' => [],
            'csrf'        => Sessao::csrf(),
        ]);
    }

    /**
     * Guardar novo documento
     */
    public function store()
    {
        // CSRF
        if (!Sessao::validarCsrf($_POST['_csrf'] ?? '')) {
            Sessao::flash('Token CSRF inválido.', 'danger');
            Helpers::redirecionar('/documentos/criar');
        }

        $titulo    = trim($_POST['titulo'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');

        // Validação
        $v = new Validator();
        $v->required('titulo', $titulo, 'O título é obrigatório.');
        $v->min('titulo', $titulo, 3, 'O título deve ter pelo menos 3 caracteres.');

        if (empty($_FILES['ficheiro']) || $_FILES['ficheiro']['error'] === UPLOAD_ERR_NO_FILE) {
            $v->required('ficheiro', '', 'É obrigatório enviar um ficheiro.');
        }

        if ($v->hasErrors()) {
            return $this->view('documentos/form.twig', [
                'acao'        => 'criar',
                'doc'         => null,
                'form_old'    => compact('titulo', 'descricao'),
                'form_errors' => $v->getErrors(),
                'csrf'        => Sessao::csrf(),
            ]);
        }

        // Upload
        try {
            $upload = new Upload();
            $relativePath = $upload->uploadFile($_FILES['ficheiro'], 'documentos');
        } catch (\RuntimeException $e) {
            return $this->view('documentos/form.twig', [
                'acao'        => 'criar',
                'doc'         => null,
                'form_old'    => compact('titulo', 'descricao'),
                'form_errors' => ['ficheiro' => [$e->getMessage()]],
                'csrf'        => Sessao::csrf(),
            ]);
        }

        // Criar documento
        $user = Auth::user();

        $this->docModel->create([
            'titulo'    => $titulo,
            'descricao' => $descricao,
            'caminho'   => $relativePath,
            'owner_id'  => $user?->id,
            'estado'    => 'ativo',
        ]);

        Sessao::flash('Documento carregado com sucesso.', 'success');
        Helpers::redirecionar('/documentos');
    }

    /**
     * Eliminar documento
     */
    public function delete(int $id)
    {
        $doc = $this->docModel->find($id);

        if (!$doc) {
            Sessao::flash('Documento não encontrado.', 'danger');
            Helpers::redirecionar('/documentos');
        }

        $this->docModel->deleteWithFile($id);

        Sessao::flash('Documento eliminado com sucesso.', 'success');
        Helpers::redirecionar('/documentos');
    }

    /**
     * Download
     */
    public function download(int $id)
    {
        $doc = $this->docModel->find($id);

        if (!$doc) {
            (new ErrorController())->error404();
            return;
        }

        $absolute = rtrim($_ENV['UPLOAD_DIR'], '/') . '/' . $doc->caminho;

        if (!is_file($absolute)) {
            (new ErrorController())->error404();
            return;
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($doc->caminho) . '"');
        header('Content-Length: ' . filesize($absolute));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: public');

        readfile($absolute);
        exit;
    }

    /**
     * Preview (imagem ou PDF)
     */
    public function preview(int $id)
    {
        $doc = $this->docModel->find($id);

        if (!$doc) {
            (new ErrorController())->error404();
            return;
        }

        $absolute = rtrim($_ENV['UPLOAD_DIR'], '/') . '/' . $doc->caminho;

        if (!is_file($absolute)) {
            (new ErrorController())->error404();
            return;
        }

        $ext = strtolower(pathinfo($absolute, PATHINFO_EXTENSION));

        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            header('Content-Type: image/' . ($ext === 'jpg' ? 'jpeg' : $ext));
            readfile($absolute);
            exit;
        }

        if ($ext === 'pdf') {
            header('Content-Type: application/pdf');
            readfile($absolute);
            exit;
        }

        (new ErrorController())->error403();
    }

    /**
     * Página de detalhes
     */
    public function show(int $id)
    {
        $doc = $this->docModel->find($id);

        if (!$doc) {
            (new ErrorController())->error404();
            return;
        }

        $absolute = rtrim($_ENV['UPLOAD_DIR'], '/') . '/' . $doc->caminho;
        $preview  = null;

        if (is_file($absolute)) {
            $ext = strtolower(pathinfo($absolute, PATHINFO_EXTENSION));

            if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
                $preview = [
                    'type' => 'image',
                    'url'  => Helpers::url('/documentos/preview/' . $doc->id),
                ];
            } elseif ($ext === 'pdf') {
                $preview = [
                    'type' => 'pdf',
                    'url'  => Helpers::url('/documentos/preview/' . $doc->id),
                ];
            }
        }

        return $this->view('documentos/show.twig', [
            'doc'     => $doc,
            'preview' => $preview,
            'csrf'    => Sessao::csrf(),
        ]);
    }
}