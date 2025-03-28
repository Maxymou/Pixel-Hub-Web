<?php

namespace App\Core\Process;

use App\Core\Logging\Logger;

class ProcessManager
{
    private $logger;
    private $processes = [];
    private $processFile = 'storage/processes.json';

    public function __construct()
    {
        $this->logger = new Logger();
        $this->loadProcesses();
    }

    /**
     * Démarre un processus
     */
    public function startProcess($appId)
    {
        try {
            // Récupérer les informations de l'application
            $app = $this->getAppInfo($appId);
            if (!$app) {
                throw new \Exception('Application non trouvée');
            }

            // Vérifier si le processus est déjà en cours
            if (isset($this->processes[$appId])) {
                throw new \Exception('Le processus est déjà en cours d\'exécution');
            }

            // Préparer la commande
            $command = $this->prepareCommand($app);

            // Démarrer le processus
            $process = $this->executeCommand($command, $app['working_directory']);
            if (!$process) {
                throw new \Exception('Échec du démarrage du processus');
            }

            // Sauvegarder les informations du processus
            $this->processes[$appId] = [
                'pid' => $process['pid'],
                'started_at' => time(),
                'command' => $command
            ];
            $this->saveProcesses();

            // Mettre à jour le statut de l'application
            $this->updateAppStatus($appId, 'running');

            // Logger l'action
            $this->logger->info("Processus démarré pour l'application {$app['name']}", [
                'app_id' => $appId,
                'pid' => $process['pid']
            ]);

            return true;

        } catch (\Exception $e) {
            $this->logger->error("Erreur lors du démarrage du processus: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Arrête un processus
     */
    public function stopProcess($appId)
    {
        try {
            if (!isset($this->processes[$appId])) {
                throw new \Exception('Processus non trouvé');
            }

            $process = $this->processes[$appId];
            
            // Arrêter le processus
            if (!$this->terminateProcess($process['pid'])) {
                throw new \Exception('Échec de l\'arrêt du processus');
            }

            // Supprimer le processus de la liste
            unset($this->processes[$appId]);
            $this->saveProcesses();

            // Mettre à jour le statut de l'application
            $this->updateAppStatus($appId, 'stopped');

            // Logger l'action
            $this->logger->info("Processus arrêté pour l'application {$appId}");

            return true;

        } catch (\Exception $e) {
            $this->logger->error("Erreur lors de l'arrêt du processus: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie l'état d'un processus
     */
    public function checkProcess($appId)
    {
        if (!isset($this->processes[$appId])) {
            return false;
        }

        $process = $this->processes[$appId];
        return $this->isProcessRunning($process['pid']);
    }

    /**
     * Récupère les statistiques d'un processus
     */
    public function getProcessStats($appId)
    {
        if (!isset($this->processes[$appId])) {
            return null;
        }

        $process = $this->processes[$appId];
        return $this->getProcessResourceUsage($process['pid']);
    }

    /**
     * Méthodes privées utilitaires
     */
    private function loadProcesses()
    {
        if (file_exists($this->processFile)) {
            $this->processes = json_decode(file_get_contents($this->processFile), true) ?? [];
        }
    }

    private function saveProcesses()
    {
        file_put_contents($this->processFile, json_encode($this->processes));
    }

    private function getAppInfo($appId)
    {
        $sql = "SELECT * FROM apps WHERE id = ?";
        return $this->db->query($sql, [$appId]);
    }

    private function prepareCommand($app)
    {
        $command = $app['command'];
        
        // Ajouter les variables d'environnement
        if (!empty($app['env_vars'])) {
            $envVars = json_decode($app['env_vars'], true);
            foreach ($envVars as $key => $value) {
                $command = "export {$key}={$value} && " . $command;
            }
        }

        return $command;
    }

    private function executeCommand($command, $workingDirectory)
    {
        $descriptorspec = [
            0 => ['pipe', 'r'],  // stdin
            1 => ['pipe', 'w'],  // stdout
            2 => ['pipe', 'w']   // stderr
        ];

        $process = proc_open($command, $descriptorspec, $pipes, $workingDirectory);
        if (!is_resource($process)) {
            return false;
        }

        $status = proc_get_status($process);
        if (!$status['running']) {
            proc_close($process);
            return false;
        }

        return [
            'pid' => $status['pid'],
            'process' => $process,
            'pipes' => $pipes
        ];
    }

    private function terminateProcess($pid)
    {
        if (!$this->isProcessRunning($pid)) {
            return true;
        }

        // Essayer d'abord un arrêt gracieux
        posix_kill($pid, SIGTERM);
        sleep(2);

        // Si le processus est toujours en cours, forcer l'arrêt
        if ($this->isProcessRunning($pid)) {
            posix_kill($pid, SIGKILL);
        }

        return !$this->isProcessRunning($pid);
    }

    private function isProcessRunning($pid)
    {
        return posix_kill($pid, 0);
    }

    private function getProcessResourceUsage($pid)
    {
        if (!$this->isProcessRunning($pid)) {
            return null;
        }

        $stats = [];
        
        // CPU
        $stats['cpu'] = $this->getProcessCPUUsage($pid);
        
        // Mémoire
        $stats['memory'] = $this->getProcessMemoryUsage($pid);
        
        // Temps d'exécution
        $stats['uptime'] = $this->getProcessUptime($pid);

        return $stats;
    }

    private function getProcessCPUUsage($pid)
    {
        $cmd = "ps -p {$pid} -o %cpu";
        exec($cmd, $output);
        return floatval($output[1] ?? 0);
    }

    private function getProcessMemoryUsage($pid)
    {
        $cmd = "ps -p {$pid} -o %mem";
        exec($cmd, $output);
        return floatval($output[1] ?? 0);
    }

    private function getProcessUptime($pid)
    {
        if (!isset($this->processes[$pid])) {
            return 0;
        }

        return time() - $this->processes[$pid]['started_at'];
    }

    private function updateAppStatus($appId, $status)
    {
        $sql = "UPDATE apps SET status = ?, updated_at = NOW() WHERE id = ?";
        $this->db->execute($sql, [$status, $appId]);
    }
} 