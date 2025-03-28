<?php

namespace App\Core\Backup;

use App\Core\Logging\Logger;

class BackupScheduler
{
    private $logger;
    private $backupManager;
    private $schedulesFile = 'storage/backups/schedules.json';

    public function __construct()
    {
        $this->logger = new Logger();
        $this->backupManager = new BackupManager();
        $this->initializeSchedulesFile();
    }

    /**
     * Crée une nouvelle planification
     */
    public function createSchedule($data)
    {
        try {
            $schedules = $this->getAllSchedules();
            $scheduleId = uniqid('schedule_');

            $schedule = [
                'id' => $scheduleId,
                'name' => $data['name'],
                'type' => $data['type'],
                'targets' => $data['targets'] ?? [],
                'frequency' => $data['frequency'],
                'time' => $data['time'],
                'retention' => $data['retention'] ?? 7,
                'enabled' => true,
                'last_run' => null,
                'next_run' => $this->calculateNextRun($data['frequency'], $data['time']),
                'created_at' => date('Y-m-d H:i:s')
            ];

            $schedules[$scheduleId] = $schedule;
            $this->saveSchedules($schedules);

            // Logger l'action
            $this->logger->info("Planification créée: {$scheduleId}", [
                'name' => $data['name'],
                'frequency' => $data['frequency']
            ]);

            return $schedule;

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la création de la planification: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Supprime une planification
     */
    public function deleteSchedule($id)
    {
        try {
            $schedules = $this->getAllSchedules();
            if (!isset($schedules[$id])) {
                throw new \Exception('Planification non trouvée');
            }

            unset($schedules[$id]);
            $this->saveSchedules($schedules);

            // Logger l'action
            $this->logger->info("Planification supprimée: {$id}");

        } catch (\Exception $e) {
            $this->logger->error("Erreur lors de la suppression de la planification {$id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Active/désactive une planification
     */
    public function toggleSchedule($id)
    {
        try {
            $schedules = $this->getAllSchedules();
            if (!isset($schedules[$id])) {
                throw new \Exception('Planification non trouvée');
            }

            $schedules[$id]['enabled'] = !$schedules[$id]['enabled'];
            $this->saveSchedules($schedules);

            // Logger l'action
            $this->logger->info("Planification {$id} " . ($schedules[$id]['enabled'] ? 'activée' : 'désactivée'));

        } catch (\Exception $e) {
            $this->logger->error("Erreur lors de la modification de la planification {$id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Exécute les sauvegardes planifiées
     */
    public function runScheduledBackups()
    {
        try {
            $schedules = $this->getAllSchedules();
            $now = time();

            foreach ($schedules as $schedule) {
                if (!$schedule['enabled']) {
                    continue;
                }

                if ($schedule['next_run'] <= $now) {
                    $this->executeBackup($schedule);
                }
            }

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'exécution des sauvegardes planifiées: ' . $e->getMessage());
        }
    }

    /**
     * Récupère toutes les planifications
     */
    public function getAllSchedules()
    {
        if (!file_exists($this->schedulesFile)) {
            return [];
        }

        return json_decode(file_get_contents($this->schedulesFile), true) ?? [];
    }

    /**
     * Récupère une planification spécifique
     */
    public function getSchedule($id)
    {
        $schedules = $this->getAllSchedules();
        return $schedules[$id] ?? null;
    }

    /**
     * Méthodes privées utilitaires
     */
    private function initializeSchedulesFile()
    {
        if (!file_exists($this->schedulesFile)) {
            file_put_contents($this->schedulesFile, json_encode([]));
        }
    }

    private function saveSchedules($schedules)
    {
        file_put_contents($this->schedulesFile, json_encode($schedules));
    }

    private function calculateNextRun($frequency, $time)
    {
        $now = time();
        $nextRun = $now;

        switch ($frequency) {
            case 'daily':
                $nextRun = strtotime("tomorrow {$time}");
                break;
            case 'weekly':
                $nextRun = strtotime("next monday {$time}");
                break;
            case 'monthly':
                $nextRun = strtotime("first day of next month {$time}");
                break;
            case 'custom':
                // Format attendu: "0 2 * * *" (cron)
                $nextRun = $this->calculateNextCronRun($time);
                break;
        }

        return $nextRun;
    }

    private function calculateNextCronRun($cronExpression)
    {
        // Implémentation simple pour l'exemple
        // Dans un environnement de production, utiliser une bibliothèque comme cron-expression
        return strtotime('+1 hour');
    }

    private function executeBackup($schedule)
    {
        try {
            // Créer la sauvegarde
            $backup = $this->backupManager->createBackup(
                $schedule['type'],
                $schedule['targets'],
                "Sauvegarde automatique: {$schedule['name']}"
            );

            // Mettre à jour la planification
            $schedules = $this->getAllSchedules();
            $schedules[$schedule['id']]['last_run'] = date('Y-m-d H:i:s');
            $schedules[$schedule['id']]['next_run'] = $this->calculateNextRun(
                $schedule['frequency'],
                $schedule['time']
            );
            $this->saveSchedules($schedules);

            // Nettoyer les anciennes sauvegardes
            $this->cleanupOldBackups($schedule);

            // Logger l'action
            $this->logger->info("Sauvegarde planifiée exécutée: {$backup['id']}", [
                'schedule_id' => $schedule['id'],
                'schedule_name' => $schedule['name']
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Erreur lors de l'exécution de la sauvegarde planifiée {$schedule['id']}: " . $e->getMessage());
        }
    }

    private function cleanupOldBackups($schedule)
    {
        $backups = $this->backupManager->getAllBackups();
        $retention = $schedule['retention'] * 24 * 60 * 60; // Conversion en secondes

        foreach ($backups as $backup) {
            if (strpos($backup['description'], "Sauvegarde automatique: {$schedule['name']}") === 0) {
                $backupTime = strtotime($backup['created_at']);
                if (time() - $backupTime > $retention) {
                    $this->backupManager->deleteBackup($backup['id']);
                }
            }
        }
    }
} 