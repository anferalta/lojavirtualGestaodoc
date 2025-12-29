<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Conexao;
use App\Core\Documento;
use App\Core\Validator;
use App\Core\Sessao;
use App\Core\Helpers;
use App\Core\Auth;
use App\Core\Upload;
use App\Core\Versoes;
use App\Core\Auditoria;

class DocumentosController extends BaseController
{
    private Documento $docModel;

    public function __construct()
    {
        parent::__construct();
        $this->docModel = new Documento(Conexao::getInstancia());
    }

    /**
     * LISTAGEM COM PAGINAÇÃO
     */
    public function index(): void
    {
        Helpers::requirePermission('documentos.ver');

        $page = $_GET['page'] ?? 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $total = $this->docModel->countAll();
        $docs  = $this->docModel->getPage($perPage, $offset);
        $pagination = Helpers::paginate($total, $perPage, $page);

        echo $this->twig->render('documentos/index.twig', [
            'documentos' => $docs,
            'pagination' => $pagination
        ]);
    }

    /**
     * FORMULÁRIO DE UPLOAD
     */
    public function upload(): void
    {
        Helpers::requirePermission('documentos.criar');

        echo $this->twig->render('documentos/upload.twig', [
            'csrf' => Sessao::csrf()
        ]);
    }

    /**
     * GUARDAR NOVO DOCUMENTO
     */
    public function store(): void
    {
        Helpers::requirePermission('documentos.criar');

        $titulo    = $_POST['titulo'] ?? '';
        $descricao = $_POST['descricao'] ?? '';
        $estado    = $_POST['estado'] ?? 'ativo';

        $validator = new Validator();
        $validator->required('titulo', $titulo, 'O título é obrigatório.');

        if (!isset($_FILES['ficheiro']) || $_FILES['ficheiro']['error'] !== UPLOAD_ERR_OK) {
            $validator->addError('ficheiro', 'O ficheiro é obrigatório.');
        }

        if ($validator->hasErrors()) {
            echo $this->twig->render('documentos/upload.twig', [
                'errors' => $validator->getErrors(),
                'old'    => ['titulo' => $titulo, 'descricao' => $descricao],
                'csrf'   => Sessao::csrf()
            ]);
            return;
        }

        try {
            // Upload seguro
            $upload = Upload::documento($_FILES['ficheiro']);

            // Criar documento
            $this->docModel->create([
                'titulo'     => $titulo,
                'descricao'  => $descricao,
                'ficheiro'   => $upload['ficheiro'],
                'tipo'       => $upload['extensao'],
                'tamanho'    => $upload['tamanho'],
                'estado'     => $estado,
                'criado_por' => Auth::id()
            ]);

            // Log de auditoria
            Auditoria::log(Auth::id(), 'criar');

            Sessao::setFlash('Documento carregado com sucesso.', 'success');
            Helpers::redirecionar('/documentos');

        } catch (\Throwable $e) {
            Sessao::setFlash($e->getMessage(), 'danger');
            Helpers::redirecionar('/documentos/upload');
        }
    }

    /**
     * FORMULÁRIO DE EDIÇÃO
     */
    public function editar(int $id): void
    {
        Helpers::requirePermission('documentos.editar');

        $doc = $this->docModel->find($id);

        if (!$doc) {
            Sessao::setFlash('Documento não encontrado.', 'danger');
            Helpers::redirecionar('/documentos');
        }

        echo $this->twig->render('documentos/editar.twig', [
            'doc'  => $doc,
            'csrf' => Sessao::csrf()
        ]);
    }

    /**
     * ATUALIZAR DOCUMENTO
     */
    public function update(int $id): void
    {
        Helpers::requirePermission('documentos.editar');

        $doc = $this->docModel->find($id);

        if (!$doc) {
            Sessao::setFlash('Documento não encontrado.', 'danger');
            Helpers::redirecionar('/documentos');
        }

        $titulo    = $_POST['titulo'] ?? '';
        $descricao = $_POST['descricao'] ?? '';
        $estado    = $_POST['estado'] ?? 'ativo';

        $validator = new Validator();
        $validator->required('titulo', $titulo, 'O título é obrigatório.');

        if ($validator->hasErrors()) {
            echo $this->twig->render('documentos/editar.twig', [
                'doc'    => $doc,
                'errors' => $validator->getErrors(),
                'csrf'   => Sessao::csrf()
            ]);
            return;
        }

        // Guardar versão antiga
        Versoes::guardar($doc);

        // Atualizar documento
        $this->docModel->update($id, [
            'titulo'    => $titulo,
            'descricao' => $descricao,
            'estado'    => $estado
        ]);

        // Log de auditoria
        Auditoria::log(Auth::id(), 'editar', $id);

        Sessao::setFlash('Documento atualizado com sucesso.', 'success');
        Helpers::redirecionar('/documentos');
    }

    /**
     * APAGAR DOCUMENTO
     */
    public function delete(int $id): void
    {
        Helpers::requirePermission('documentos.apagar');

        $doc = $this->docModel->find($id);

        if (!$doc) {
            Sessao::setFlash('Documento não encontrado.', 'danger');
            Helpers::redirecionar('/documentos');
        }

        // Apagar ficheiro físico
        $uploadDir = rtrim($_ENV['UPLOAD_DIR'], '/');
        $path = $uploadDir . '/' . $doc->ficheiro;

        if (is_file($path)) {
            unlink($path);
        }

        // Apagar registo
        $this->docModel->delete($id);

        // Log de auditoria
        Auditoria::log(Auth::id(), 'apagar', $id);

        Sessao::setFlash('Documento eliminado com sucesso.', 'success');
        Helpers::redirecionar('/documentos');
    }

    /**
     * DOWNLOAD SEGURO
     */
    public function download(int $id): void
    {
        Helpers::requirePermission('documentos.download');

        $doc = $this->docModel->find($id);

        if (!$doc) {
            Sessao::setFlash('Documento não encontrado.', 'danger');
            Helpers::redirecionar('/documentos');
        }

        $uploadDir = rtrim($_ENV['UPLOAD_DIR'], '/');
        $path = $uploadDir . '/' . $doc->ficheiro;

        if (!is_file($path)) {
            Sessao::setFlash('Ficheiro não encontrado.', 'danger');
            Helpers::redirecionar('/documentos');
        }

        // Log de auditoria
        Auditoria::log(Auth::id(), 'download', $id);

        header('Content-Type: ' . $doc->tipo);
        header('Content-Disposition: attachment; filename="' . $doc->ficheiro . '"');
        header('Content-Length: ' . filesize($path));

        readfile($path);
        exit;
    }
}