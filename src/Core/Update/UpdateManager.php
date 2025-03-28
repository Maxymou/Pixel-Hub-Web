<?php

namespace App\Core\Update;

use App\Core\Logging\Logger;
use App\Core\Backup\BackupManager;
use App\Core\Storage\FileManager;

class UpdateManager
{
    private $logger;
    private $backupManager;
    private $fileManager;
    private $updatePath = 'storage/updates/';
    private $tempPath = 'storage/temp/';
    private $currentVersion;
    private $updateServerUrl;

    public function __construct()
    {
        $this->logger = new Logger();
        $this->backupManager = new BackupManager();
        $this->fileManager = new FileManager();
        $this->currentVersion = $this->getCurrentVersion();
        $this->updateServerUrl = $_ENV['UPDATE_SERVER_URL'] ?? 'https://api.pixel-hub.com/updates';
        $this->initializeDirectories();
    }

    /**
     * Vérifie les mises à jour disponibles
     */
    public function checkForUpdates()
    {
        try {
            $response = $this->fetchUpdateInfo();
            $latestVersion = $response['version'];
            $updateInfo = $response['info'];

            if (version_compare($latestVersion, $this->currentVersion, '>')) {
                $this->logger->info("Mise à jour disponible: {$latestVersion}");
                return [
                    'available' => true,
                    'current_version' => $this->currentVersion,
                    'latest_version' => $latestVersion,
                    'info' => $updateInfo
                ];
            }

            return [
                'available' => false,
                'current_version' => $this->currentVersion,
                'latest_version' => $latestVersion
            ];

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la vérification des mises à jour: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Télécharge une mise à jour
     */
    public function downloadUpdate($version)
    {
        try {
            // Créer un répertoire temporaire pour le téléchargement
            $tempDir = $this->tempPath . 'update_' . $version;
            mkdir($tempDir, 0755, true);

            // Télécharger le fichier de mise à jour
            $updateFile = $this->downloadUpdateFile($version, $tempDir);

            // Vérifier l'intégrité du fichier
            if (!$this->verifyUpdateIntegrity($updateFile, $version)) {
                throw new \Exception('Le fichier de mise à jour est corrompu');
            }

            // Enregistrer les métadonnées
            $this->saveUpdateMetadata($version, [
                'version' => $version,
                'file' => $updateFile,
                'status' => 'downloaded',
                'downloaded_at' => date('Y-m-d H:i:s')
            ]);

            $this->logger->info("Mise à jour {$version} téléchargée avec succès");
            return true;

        } catch (\Exception $e) {
            $this->logger->error("Erreur lors du téléchargement de la mise à jour {$version}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Installe une mise à jour
     */
    public function installUpdate($version)
    {
        try {
            // Vérifier que la mise à jour est téléchargée
            $update = $this->getUpdate($version);
            if (!$update || $update['status'] !== 'downloaded') {
                throw new \Exception('La mise à jour n\'est pas téléchargée');
            }

            // Créer une sauvegarde avant l'installation
            $backup = $this->backupManager->createBackup(
                'full',
                [],
                "Sauvegarde avant mise à jour {$version}"
            );

            // Extraire la mise à jour
            $tempDir = $this->tempPath . 'install_' . $version;
            $this->extractUpdate($update['file'], $tempDir);

            // Vérifier les dépendances
            $this->checkDependencies($tempDir);

            // Installer la mise à jour
            $this->applyUpdate($tempDir);

            // Mettre à jour la base de données
            $this->updateDatabase($tempDir);

            // Mettre à jour les métadonnées
            $this->updateMetadata($version, [
                'status' => 'installed',
                'installed_at' => date('Y-m-d H:i:s'),
                'backup_id' => $backup['id']
            ]);

            $this->logger->info("Mise à jour {$version} installée avec succès");
            return true;

        } catch (\Exception $e) {
            $this->logger->error("Erreur lors de l'installation de la mise à jour {$version}: " . $e->getMessage());
            $this->rollbackUpdate($version);
            throw $e;
        }
    }

    /**
     * Annule une mise à jour
     */
    public function rollbackUpdate($version)
    {
        try {
            $update = $this->getUpdate($version);
            if (!$update || !isset($update['backup_id'])) {
                throw new \Exception('Impossible de restaurer la mise à jour');
            }

            // Restaurer la sauvegarde
            $this->backupManager->restoreBackup($update['backup_id']);

            // Mettre à jour les métadonnées
            $this->updateMetadata($version, [
                'status' => 'rolled_back',
                'rolled_back_at' => date('Y-m-d H:i:s')
            ]);

            $this->logger->info("Mise à jour {$version} annulée avec succès");
            return true;

        } catch (\Exception $e) {
            $this->logger->error("Erreur lors de l'annulation de la mise à jour {$version}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Planifie l'installation d'une mise à jour
     */
    public function scheduleUpdate($version, $scheduledTime)
    {
        try {
            $update = $this->getUpdate($version);
            if (!$update || $update['status'] !== 'downloaded') {
                throw new \Exception('La mise à jour n\'est pas téléchargée');
            }

            // Enregistrer la planification
            $this->updateMetadata($version, [
                'status' => 'scheduled',
                'scheduled_time' => $scheduledTime
            ]);

            $this->logger->info("Installation de la mise à jour {$version} planifiée pour {$scheduledTime}");
            return true;

        } catch (\Exception $e) {
            $this->logger->error("Erreur lors de la planification de la mise à jour {$version}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Exécute les mises à jour planifiées
     */
    public function runScheduledUpdates()
    {
        try {
            $updates = $this->getAllUpdates();
            $now = time();

            foreach ($updates as $update) {
                if ($update['status'] === 'scheduled' && strtotime($update['scheduled_time']) <= $now) {
                    $this->installUpdate($update['version']);
                }
            }

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'exécution des mises à jour planifiées: ' . $e->getMessage());
        }
    }

    /**
     * Récupère toutes les mises à jour
     */
    public function getAllUpdates()
    {
        $updates = [];
        $metadataFile = $this->updatePath . 'metadata.json';

        if (file_exists($metadataFile)) {
            $updates = json_decode(file_get_contents($metadataFile), true) ?? [];
        }

        return $updates;
    }

    /**
     * Récupère une mise à jour spécifique
     */
    public function getUpdate($version)
    {
        $updates = $this->getAllUpdates();
        return $updates[$version] ?? null;
    }

    /**
     * Méthodes privées utilitaires
     */
    private function initializeDirectories()
    {
        if (!is_dir($this->updatePath)) {
            mkdir($this->updatePath, 0755, true);
        }
        if (!is_dir($this->tempPath)) {
            mkdir($this->tempPath, 0755, true);
        }
    }

    private function getCurrentVersion()
    {
        return $_ENV['APP_VERSION'] ?? '1.0.0';
    }

    private function fetchUpdateInfo()
    {
        $response = file_get_contents($this->updateServerUrl . '/latest');
        if ($response === false) {
            throw new \Exception('Impossible de contacter le serveur de mise à jour');
        }

        return json_decode($response, true);
    }

    private function downloadUpdateFile($version, $tempDir)
    {
        $url = $this->updateServerUrl . "/download/{$version}";
        $file = $tempDir . "/update_{$version}.zip";

        $fp = fopen($file, 'w+');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function($ch, $downloadSize, $downloaded, $uploadSize, $uploaded) {
            // TODO: Implémenter le suivi de la progression
        });

        if (!curl_exec($ch)) {
            throw new \Exception('Erreur lors du téléchargement: ' . curl_error($ch));
        }

        curl_close($ch);
        fclose($fp);

        return $file;
    }

    private function verifyUpdateIntegrity($file, $version)
    {
        $response = file_get_contents($this->updateServerUrl . "/checksum/{$version}");
        $checksum = json_decode($response, true)['checksum'];

        return hash_file('sha256', $file) === $checksum;
    }

    private function extractUpdate($file, $tempDir)
    {
        $zip = new \ZipArchive();
        if ($zip->open($file) !== true) {
            throw new \Exception('Impossible d\'ouvrir le fichier de mise à jour');
        }

        $zip->extractTo($tempDir);
        $zip->close();
    }

    private function checkDependencies($tempDir)
    {
        $dependencies = json_decode(file_get_contents($tempDir . '/dependencies.json'), true);
        foreach ($dependencies as $dep) {
            if (!extension_loaded($dep['extension'])) {
                throw new \Exception("Dépendance manquante: {$dep['extension']}");
            }
            if (isset($dep['version'])) {
                if (version_compare(phpversion($dep['extension']), $dep['version'], '<')) {
                    throw new \Exception("Version de {$dep['extension']} insuffisante");
                }
            }
        }
    }

    private function applyUpdate($tempDir)
    {
        // Copier les fichiers
        $this->fileManager->copyDirectory($tempDir . '/files', dirname($tempDir));

        // Exécuter les scripts de mise à jour
        if (file_exists($tempDir . '/update.php')) {
            require_once $tempDir . '/update.php';
        }
    }

    private function updateDatabase($tempDir)
    {
        if (file_exists($tempDir . '/database.sql')) {
            $sql = file_get_contents($tempDir . '/database.sql');
            // TODO: Exécuter les requêtes SQL
        }
    }

    private function saveUpdateMetadata($version, $data)
    {
        $metadataFile = $this->updatePath . 'metadata.json';
        $metadata = [];

        if (file_exists($metadataFile)) {
            $metadata = json_decode(file_get_contents($metadataFile), true) ?? [];
        }

        $metadata[$version] = $data;
        file_put_contents($metadataFile, json_encode($metadata));
    }

    private function updateMetadata($version, $data)
    {
        $metadataFile = $this->updatePath . 'metadata.json';
        $metadata = json_decode(file_get_contents($metadataFile), true) ?? [];

        if (isset($metadata[$version])) {
            $metadata[$version] = array_merge($metadata[$version], $data);
            file_put_contents($metadataFile, json_encode($metadata));
        }
    }
} 