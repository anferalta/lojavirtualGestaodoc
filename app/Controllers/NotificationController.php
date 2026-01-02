<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Auth;
use App\Core\Sessao;
use App\Core\AuditLogger;
use App\Models\Notification;

class NotificationController extends BaseController
{
    /**
     * Listar todas as notificações do utilizador atual
     */
    public function index(): void
    {
        if (!Auth::check()) {
            $this->redirect('login');
        }

        $notificacoes = Notification::allForCurrent();

        $this->view('notificacoes/index', [
            'titulo'       => 'Notificações',
            'notificacoes' => $notificacoes
        ]);
    }

    /**
     * Marcar uma notificação como lida
     */
    public function marcarLida(int $id): void
    {
        if (!Auth::check()) {
            $this->redirect('login');
        }

        if (Notification::markAsRead($id)) {
            AuditLogger::log('notificacao_lida', "ID: $id");
            Sessao::flash('Notificação marcada como lida.', 'success');
        } else {
            Sessao::flash('Não foi possível marcar como lida.', 'error');
        }

        $this->redirect('notificacoes');
    }

    /**
     * Marcar todas como lidas
     */
    public function marcarTodas(): void
    {
        if (!Auth::check()) {
            $this->redirect('login');
        }

        Notification::markAllAsRead();

        AuditLogger::log('notificacoes_lidas_todas');
        Sessao::flash('Todas as notificações foram marcadas como lidas.', 'success');

        $this->redirect('notificacoes');
    }

    /**
     * Apagar uma notificação
     */
    public function apagar(int $id): void
    {
        if (!Auth::check()) {
            $this->redirect('login');
        }

        if (Notification::delete($id)) {
            AuditLogger::log('notificacao_apagada', "ID: $id");
            Sessao::flash('Notificação apagada.', 'info');
        } else {
            Sessao::flash('Não foi possível apagar.', 'error');
        }

        $this->redirect('notificacoes');
    }
}