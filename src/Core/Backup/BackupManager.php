<?php

namespace App\Core\Backup;

use App\Core\Logging\Logger;
use App\Core\Storage\FileManager;

class BackupManager
{
    private $logger;
    private $fileManager;
    private $backupPath = 'storage/backups/';
    private $tempPath = 'storage/temp/';

    public function __construct()
    {
        $this->logger = new Logger();
        $this->fileManager = new FileManager();
        $this->initializeDirectories();
    }

    /**
     * Crée une nouvelle sauvegarde
     */
    public function createBackup($type, $targets = [], $description = '', $compression = true)
    {
        try {
            // Générer un identifiant unique
            $backupId = uniqid('backup_');
            $timestamp = date('Y-m-d_H-i-s');
            $filename = "{$backupId}_{$timestamp}.tar";

            // Créer le répertoire temporaire
            $tempDir = $this->tempPath . $backupId;
            mkdir($tempDir, 0755, true);

            // Copier les fichiers selon le type
            if ($type === 'full') {
                $this->backupFull($tempDir);
            } else {
                $this->backupPartial($tempDir, $targets);
            }

            // Créer l'archive
            $archivePath = $this->backupPath . $filename;
            $this->createArchive($tempDir, $archivePath, $compression);

            // Calculer le hash pour l'intégrité
            $hash = $this->calculateHash($archivePath);

            // Enregistrer les métadonnées
            $this->saveMetadata($backupId, [
                'id' => $backupId,
                'type' => $type,
                'filename' => $filename,
                'path' => $archivePath,
                'size' => filesize($archivePath),
                'hash' => $hash,
                'description' => $description,
                'created_at' => date('Y-m-d H:i:s'),
                'targets' => $targets
            ]);

            // Nettoyer
            $this->cleanup($tempDir);

            // Logger l'action
            $this->logger->info("Sauvegarde créée: {$backupId}", [
                'type' => $type,
                'size' => filesize($archivePath)
            ]);

            return $this->getBackup($backupId);

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la création de la sauvegarde: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Restaure une sauvegarde
     */
    public function restoreBackup($id)
    {
        try {
            $backup = $this->getBackup($id);
            if (!$backup) {
                throw new \Exception('Sauvegarde non trouvée');
            }

            // Vérifier l'intégrité
            if (!$this->checkIntegrity($id)) {
                throw new \Exception('La sauvegarde est corrompue');
            }

            // Créer un répertoire temporaire
            $tempDir = $this->tempPath . 'restore_' . $id;
            mkdir($tempDir, 0755, true);

            // Extraire l'archive
            $this->extractArchive($backup['path'], $tempDir);

            // Restaurer les fichiers
            if ($backup['type'] === 'full') {
                $this->restoreFull($tempDir);
            } else {
                $this->restorePartial($tempDir, $backup['targets']);
            }

            // Nettoyer
            $this->cleanup($tempDir);

            // Logger l'action
            $this->logger->info("Sauvegarde restaurée: {$id}");

            return true;

        } catch (\Exception $e) {
            $this->logger->error("Erreur lors de la restauration de la sauvegarde {$id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Vérifie l'intégrité d'une sauvegarde
     */
    public function checkIntegrity($id)
    {
        $backup = $this->getBackup($id);
        if (!$backup) {
            return false;
        }

        $currentHash = $this->calculateHash($backup['path']);
        return $currentHash === $backup['hash'];
    }

    /**
     * Récupère toutes les sauvegardes
     */
    public function getAllBackups()
    {
        $backups = [];
        $metadataFile = $this->backupPath . 'metadata.json';

        if (file_exists($metadataFile)) {
            $backups = json_decode(file_get_contents($metadataFile), true) ?? [];
        }

        return $backups;
    }

    /**
     * Récupère une sauvegarde spécifique
     */
    public function getBackup($id)
    {
        $backups = $this->getAllBackups();
        return $backups[$id] ?? null;
    }

    /**
     * Supprime une sauvegarde
     */
    public function deleteBackup($id)
    {
        $backup = $this->getBackup($id);
        if (!$backup) {
            throw new \Exception('Sauvegarde non trouvée');
        }

        // Supprimer le fichier
        if (file_exists($backup['path'])) {
            unlink($backup['path']);
        }

        // Mettre à jour les métadonnées
        $backups = $this->getAllBackups();
        unset($backups[$id]);
        $this->saveMetadata(null, $backups);

        // Logger l'action
        $this->logger->info("Sauvegarde supprimée: {$id}");
    }

    /**
     * Méthodes privées utilitaires
     */
    private function initializeDirectories()
    {
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
        if (!is_dir($this->tempPath)) {
            mkdir($this->tempPath, 0755, true);
        }
    }

    private function backupFull($tempDir)
    {
        // Sauvegarder la base de données
        $this->backupDatabase($tempDir);

        // Sauvegarder les fichiers
        $this->backupFiles($tempDir, [
            'src',
            'config',
            'public',
            'storage/logs',
            'storage/uploads'
        ]);
    }

    private function backupPartial($tempDir, $targets)
    {
        foreach ($targets as $target) {
            if (strpos($target, 'db:') === 0) {
                $this->backupDatabase($tempDir, substr($target, 3));
            } else {
                $this->backupFiles($tempDir, [$target]);
            }
        }
    }

    private function backupDatabase($tempDir, $table = null)
    {
        $dbConfig = $this->getDatabaseConfig();
        $filename = $table ? "db_{$table}.sql" : 'db_full.sql';
        $command = sprintf(
            'mysqldump -h %s -u %s -p%s %s %s > %s',
            $dbConfig['host'],
            $dbConfig['username'],
            $dbConfig['password'],
            $table ? "--tables {$table}" : '',
            $dbConfig['database'],
            $tempDir . '/' . $filename
        );

        exec($command, $output, $returnVar);
        if ($returnVar !== 0) {
            throw new \Exception('Erreur lors de la sauvegarde de la base de données');
        }
    }

    private function backupFiles($tempDir, $paths)
    {
        foreach ($paths as $path) {
            if (!is_dir($path) && !is_file($path)) {
                continue;
            }

            $targetDir = $tempDir . '/' . basename($path);
            if (is_dir($path)) {
                $this->fileManager->copyDirectory($path, $targetDir);
            } else {
                copy($path, $targetDir);
            }
        }
    }

    private function restoreFull($tempDir)
    {
        // Restaurer la base de données
        $this->restoreDatabase($tempDir . '/db_full.sql');

        // Restaurer les fichiers
        $this->restoreFiles($tempDir);
    }

    private function restorePartial($tempDir, $targets)
    {
        foreach ($targets as $target) {
            if (strpos($target, 'db:') === 0) {
                $table = substr($target, 3);
                $this->restoreDatabase($tempDir . "/db_{$table}.sql", $table);
            } else {
                $this->restoreFiles($tempDir . '/' . basename($target));
            }
        }
    }

    private function restoreDatabase($sqlFile, $table = null)
    {
        $dbConfig = $this->getDatabaseConfig();
        $command = sprintf(
            'mysql -h %s -u %s -p%s %s < %s',
            $dbConfig['host'],
            $dbConfig['username'],
            $dbConfig['password'],
            $dbConfig['database'],
            $sqlFile
        );

        exec($command, $output, $returnVar);
        if ($returnVar !== 0) {
            throw new \Exception('Erreur lors de la restauration de la base de données');
        }
    }

    private function restoreFiles($source)
    {
        if (is_dir($source)) {
            $this->fileManager->copyDirectory($source, dirname($source));
        } else {
            copy($source, dirname($source));
        }
    }

    private function createArchive($source, $destination, $compression)
    {
        $command = sprintf(
            'tar -czf %s -C %s .',
            $destination,
            $source
        );

        exec($command, $output, $returnVar);
        if ($returnVar !== 0) {
            throw new \Exception('Erreur lors de la création de l\'archive');
        }
    }

    private function extractArchive($source, $destination)
    {
        $command = sprintf(
            'tar -xzf %s -C %s',
            $source,
            $destination
        );

        exec($command, $output, $returnVar);
        if ($returnVar !== 0) {
            throw new \Exception('Erreur lors de l\'extraction de l\'archive');
        }
    }

    private function calculateHash($file)
    {
        return hash_file('sha256', $file);
    }

    private function saveMetadata($id, $data)
    {
        $metadataFile = $this->backupPath . 'metadata.json';
        $metadata = [];

        if (file_exists($metadataFile)) {
            $metadata = json_decode(file_get_contents($metadataFile), true) ?? [];
        }

        if ($id) {
            $metadata[$id] = $data;
        } else {
            $metadata = $data;
        }

        file_put_contents($metadataFile, json_encode($metadata));
    }

    private function cleanup($path)
    {
        if (is_dir($path)) {
            $this->fileManager->cleanDirectory($path);
        }
    }

    private function getDatabaseConfig()
    {
        return [
            'host' => $_ENV['DB_HOST'],
            'username' => $_ENV['DB_USER'],
            'password' => $_ENV['DB_PASS'],
            'database' => $_ENV['DB_NAME']
        ];
    }
} 