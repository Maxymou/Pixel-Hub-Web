<?php

namespace App\Controllers;

use App\Core\Controller;

class BackupViewController extends Controller
{
    /**
     * Affiche la page de gestion des sauvegardes
     */
    public function index()
    {
        // Vérifier les permissions
        if (!$this->hasPermission('backup.view')) {
            return $this->redirect('/dashboard');
        }

        // Charger la vue
        return $this->view('backups/index');
    }

    /**
     * Vérification des permissions
     */
    private function hasPermission($permission)
    {
        return $this->auth->hasPermission($permission);
    }
} 