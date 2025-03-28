<?php

namespace App\Controllers;

use App\Core\Controller;

class UpdateViewController extends Controller
{
    /**
     * Affiche la page de gestion des mises à jour
     */
    public function index()
    {
        // Vérifier les permissions
        if (!$this->hasPermission('update.view')) {
            return $this->redirect('/dashboard');
        }

        // Charger la vue
        return $this->view('updates/index');
    }

    /**
     * Vérification des permissions
     */
    private function hasPermission($permission)
    {
        return $this->auth->hasPermission($permission);
    }
} 