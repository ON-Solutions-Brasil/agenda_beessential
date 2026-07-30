<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Setting;
use App\Models\ActivityLog;

class SettingsController extends Controller
{
    private Setting $settingModel;

    public function __construct()
    {
        $this->settingModel = new Setting();
    }

    /**
     * Exibe a página de configurações.
     */
    public function index(): void
    {
        $this->requirePermission('settings.view');

        $settings = $this->settingModel->allGrouped();

        $this->view('settings/index', [
            'settings' => $settings,
        ]);
    }

    /**
     * Salva as configurações.
     */
    public function save(): void
    {
        $this->requirePermission('settings.edit');

        if (!$this->validateCsrf()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/settings');
            return;
        }

        $settings = $_POST['settings'] ?? [];
        if (!is_array($settings)) {
            Session::flash('error', 'Dados inválidos.');
            $this->redirect('/settings');
            return;
        }

        $this->settingModel->saveMultiple($settings);

        $log = new ActivityLog();
        $log->log('settings.updated', 'settings', null, 'Configurações do sistema atualizadas');

        Session::flash('success', 'Configurações salvas com sucesso!');
        $this->redirect('/settings');
    }
}
