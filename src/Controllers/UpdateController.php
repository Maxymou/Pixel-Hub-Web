<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Update\UpdateManager;
use App\Core\Validation\Validator;

class UpdateController extends Controller
{
    private $updateManager;
    private $validator;

    public function __construct()
    {
        parent::__construct();
        $this->updateManager = new UpdateManager();
        $this->validator = new Validator();
    }

    /**
     * Vérifie les mises à jour disponibles
     */
    public function check()
    {
        try {
            $result = $this->updateManager->checkForUpdates();
            return $this->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Télécharge une mise à jour
     */
    public function download()
    {
        try {
            $data = $this->request->getJson();
            
            // Validation
            $rules = [
                'version' => 'required|string'
            ];
            
            if (!$this->validator->validate($data, $rules)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $this->validator->getErrors()
                ], 400);
            }

            $this->updateManager->downloadUpdate($data['version']);
            
            return $this->json([
                'success' => true,
                'message' => 'Mise à jour téléchargée avec succès'
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Installe une mise à jour
     */
    public function install()
    {
        try {
            $data = $this->request->getJson();
            
            // Validation
            $rules = [
                'version' => 'required|string'
            ];
            
            if (!$this->validator->validate($data, $rules)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $this->validator->getErrors()
                ], 400);
            }

            $this->updateManager->installUpdate($data['version']);
            
            return $this->json([
                'success' => true,
                'message' => 'Mise à jour installée avec succès'
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Annule une mise à jour
     */
    public function rollback()
    {
        try {
            $data = $this->request->getJson();
            
            // Validation
            $rules = [
                'version' => 'required|string'
            ];
            
            if (!$this->validator->validate($data, $rules)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $this->validator->getErrors()
                ], 400);
            }

            $this->updateManager->rollbackUpdate($data['version']);
            
            return $this->json([
                'success' => true,
                'message' => 'Mise à jour annulée avec succès'
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Planifie l'installation d'une mise à jour
     */
    public function schedule()
    {
        try {
            $data = $this->request->getJson();
            
            // Validation
            $rules = [
                'version' => 'required|string',
                'scheduled_time' => 'required|date'
            ];
            
            if (!$this->validator->validate($data, $rules)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $this->validator->getErrors()
                ], 400);
            }

            $this->updateManager->scheduleUpdate($data['version'], $data['scheduled_time']);
            
            return $this->json([
                'success' => true,
                'message' => 'Installation de la mise à jour planifiée avec succès'
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère la liste des mises à jour
     */
    public function list()
    {
        try {
            $updates = $this->updateManager->getAllUpdates();
            
            return $this->json([
                'success' => true,
                'data' => $updates
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
} 