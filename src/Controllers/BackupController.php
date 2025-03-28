<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Backup\BackupManager;
use App\Core\Backup\BackupScheduler;
use App\Core\Validation\Validator;

class BackupController extends Controller
{
    private $backupManager;
    private $backupScheduler;
    private $validator;

    public function __construct()
    {
        parent::__construct();
        $this->backupManager = new BackupManager();
        $this->backupScheduler = new BackupScheduler();
        $this->validator = new Validator();
    }

    /**
     * Liste toutes les sauvegardes et planifications
     */
    public function index()
    {
        try {
            $backups = $this->backupManager->getAllBackups();
            $schedules = $this->backupScheduler->getAllSchedules();

            return $this->json([
                'success' => true,
                'data' => [
                    'backups' => $backups,
                    'schedules' => $schedules
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des sauvegardes'
            ], 500);
        }
    }

    /**
     * Crée une nouvelle sauvegarde manuelle
     */
    public function create()
    {
        try {
            // Vérifier les permissions
            if (!$this->hasPermission('backup.create')) {
                return $this->json([
                    'success' => false,
                    'message' => 'Permission refusée'
                ], 403);
            }

            // Valider les données
            $data = $this->request->getJson();
            $rules = [
                'type' => 'required|in:full,partial',
                'targets' => 'required_if:type,partial|array',
                'description' => 'optional|string|max:255',
                'compression' => 'optional|boolean'
            ];

            if (!$this->validator->validate($data, $rules)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $this->validator->getErrors()
                ], 422);
            }

            // Créer la sauvegarde
            $backup = $this->backupManager->createBackup(
                $data['type'],
                $data['targets'] ?? [],
                $data['description'] ?? '',
                $data['compression'] ?? true
            );

            return $this->json([
                'success' => true,
                'data' => $backup
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la sauvegarde'
            ], 500);
        }
    }

    /**
     * Affiche les détails d'une sauvegarde
     */
    public function show($id)
    {
        try {
            $backup = $this->backupManager->getBackup($id);
            if (!$backup) {
                return $this->json([
                    'success' => false,
                    'message' => 'Sauvegarde non trouvée'
                ], 404);
            }

            // Vérifier l'intégrité
            $integrity = $this->backupManager->checkIntegrity($id);

            return $this->json([
                'success' => true,
                'data' => array_merge($backup, [
                    'integrity' => $integrity
                ])
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la sauvegarde'
            ], 500);
        }
    }

    /**
     * Restaure une sauvegarde
     */
    public function restore($id)
    {
        try {
            // Vérifier les permissions
            if (!$this->hasPermission('backup.restore')) {
                return $this->json([
                    'success' => false,
                    'message' => 'Permission refusée'
                ], 403);
            }

            // Vérifier l'intégrité
            if (!$this->backupManager->checkIntegrity($id)) {
                return $this->json([
                    'success' => false,
                    'message' => 'La sauvegarde est corrompue'
                ], 422);
            }

            // Restaurer la sauvegarde
            $this->backupManager->restoreBackup($id);

            return $this->json([
                'success' => true,
                'message' => 'Sauvegarde restaurée avec succès'
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la restauration de la sauvegarde'
            ], 500);
        }
    }

    /**
     * Supprime une sauvegarde
     */
    public function destroy($id)
    {
        try {
            // Vérifier les permissions
            if (!$this->hasPermission('backup.delete')) {
                return $this->json([
                    'success' => false,
                    'message' => 'Permission refusée'
                ], 403);
            }

            $this->backupManager->deleteBackup($id);

            return $this->json([
                'success' => true,
                'message' => 'Sauvegarde supprimée avec succès'
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la sauvegarde'
            ], 500);
        }
    }

    /**
     * Crée une nouvelle planification
     */
    public function schedule()
    {
        try {
            // Vérifier les permissions
            if (!$this->hasPermission('backup.schedule')) {
                return $this->json([
                    'success' => false,
                    'message' => 'Permission refusée'
                ], 403);
            }

            // Valider les données
            $data = $this->request->getJson();
            $rules = [
                'name' => 'required|string|max:255',
                'type' => 'required|in:full,partial',
                'targets' => 'required_if:type,partial|array',
                'frequency' => 'required|in:daily,weekly,monthly,custom',
                'time' => 'required|string',
                'retention' => 'optional|integer|min:1'
            ];

            if (!$this->validator->validate($data, $rules)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $this->validator->getErrors()
                ], 422);
            }

            // Créer la planification
            $schedule = $this->backupScheduler->createSchedule($data);

            return $this->json([
                'success' => true,
                'data' => $schedule
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la planification'
            ], 500);
        }
    }

    /**
     * Supprime une planification
     */
    public function deleteSchedule($id)
    {
        try {
            // Vérifier les permissions
            if (!$this->hasPermission('backup.schedule')) {
                return $this->json([
                    'success' => false,
                    'message' => 'Permission refusée'
                ], 403);
            }

            $this->backupScheduler->deleteSchedule($id);

            return $this->json([
                'success' => true,
                'message' => 'Planification supprimée avec succès'
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la planification'
            ], 500);
        }
    }

    /**
     * Active/désactive une planification
     */
    public function toggleSchedule($id)
    {
        try {
            // Vérifier les permissions
            if (!$this->hasPermission('backup.schedule')) {
                return $this->json([
                    'success' => false,
                    'message' => 'Permission refusée'
                ], 403);
            }

            $this->backupScheduler->toggleSchedule($id);

            return $this->json([
                'success' => true,
                'message' => 'État de la planification modifié avec succès'
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la modification de la planification'
            ], 500);
        }
    }

    /**
     * Vérification des permissions
     */
    private function hasPermission($permission)
    {
        return $this->auth->hasPermission($permission);
    }
} 