<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Storage\FileManager;

class IconController extends Controller
{
    private $fileManager;
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
    private $maxSize = 1048576; // 1MB
    private $uploadPath = 'public/uploads/icons/';

    public function __construct()
    {
        parent::__construct();
        $this->fileManager = new FileManager();
    }

    /**
     * Upload une nouvelle icône
     */
    public function upload()
    {
        try {
            // Vérifier l'authentification
            if (!$this->auth->isAuthenticated()) {
                return $this->json(['error' => 'Non authentifié'], 401);
            }

            // Vérifier la présence du fichier
            if (!isset($_FILES['icon'])) {
                return $this->json(['error' => 'Aucun fichier fourni'], 400);
            }

            $file = $_FILES['icon'];
            $appId = $_POST['app_id'] ?? null;

            if (!$appId) {
                return $this->json(['error' => 'ID d\'application manquant'], 400);
            }

            // Valider le fichier
            $this->validateFile($file);

            // Générer un nom de fichier unique
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = sprintf('%s_%s.%s', $appId, uniqid(), $extension);
            $filepath = $this->uploadPath . $filename;

            // Déplacer le fichier
            if (!$this->fileManager->moveUploadedFile($file['tmp_name'], $filepath)) {
                throw new \Exception('Erreur lors de l\'upload du fichier');
            }

            // Mettre à jour la base de données
            $this->updateAppIcon($appId, $filepath);

            return $this->json([
                'success' => true,
                'icon_url' => '/' . $filepath
            ]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Supprime une icône
     */
    public function delete($appId)
    {
        try {
            // Vérifier l'authentification
            if (!$this->auth->isAuthenticated()) {
                return $this->json(['error' => 'Non authentifié'], 401);
            }

            // Récupérer l'icône actuelle
            $currentIcon = $this->getAppIcon($appId);
            if ($currentIcon) {
                // Supprimer le fichier
                $this->fileManager->deleteFile($currentIcon);
            }

            // Mettre à jour la base de données
            $this->updateAppIcon($appId, null);

            return $this->json(['success' => true]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Récupère toutes les icônes personnalisées
     */
    public function getAll()
    {
        try {
            // Vérifier l'authentification
            if (!$this->auth->isAuthenticated()) {
                return $this->json(['error' => 'Non authentifié'], 401);
            }

            $icons = $this->getAllCustomIcons();
            return $this->json($icons);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Valide un fichier uploadé
     */
    private function validateFile($file)
    {
        // Vérifier le type MIME
        if (!in_array($file['type'], $this->allowedTypes)) {
            throw new \Exception('Type de fichier non autorisé');
        }

        // Vérifier la taille
        if ($file['size'] > $this->maxSize) {
            throw new \Exception('Fichier trop volumineux (max 1MB)');
        }

        // Vérifier les erreurs d'upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception('Erreur lors de l\'upload du fichier');
        }
    }

    /**
     * Met à jour l'icône d'une application dans la base de données
     */
    private function updateAppIcon($appId, $iconPath)
    {
        $sql = "UPDATE apps SET icon_path = ? WHERE id = ?";
        $this->db->execute($sql, [$iconPath, $appId]);
    }

    /**
     * Récupère l'icône d'une application
     */
    private function getAppIcon($appId)
    {
        $sql = "SELECT icon_path FROM apps WHERE id = ?";
        $result = $this->db->query($sql, [$appId]);
        return $result['icon_path'] ?? null;
    }

    /**
     * Récupère toutes les icônes personnalisées
     */
    private function getAllCustomIcons()
    {
        $sql = "SELECT id, icon_path FROM apps WHERE icon_path IS NOT NULL";
        $results = $this->db->queryAll($sql);
        
        $icons = [];
        foreach ($results as $row) {
            $icons[$row['id']] = '/' . $row['icon_path'];
        }
        
        return $icons;
    }
} 