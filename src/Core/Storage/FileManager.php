<?php

namespace App\Core\Storage;

class FileManager
{
    /**
     * Déplace un fichier uploadé vers sa destination
     */
    public function moveUploadedFile($source, $destination)
    {
        // Créer le répertoire de destination s'il n'existe pas
        $dir = dirname($destination);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return move_uploaded_file($source, $destination);
    }

    /**
     * Supprime un fichier
     */
    public function deleteFile($filepath)
    {
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        return false;
    }

    /**
     * Vérifie si un fichier existe
     */
    public function fileExists($filepath)
    {
        return file_exists($filepath);
    }

    /**
     * Crée un répertoire
     */
    public function createDirectory($path, $permissions = 0755)
    {
        if (!is_dir($path)) {
            return mkdir($path, $permissions, true);
        }
        return true;
    }

    /**
     * Nettoie un répertoire
     */
    public function cleanDirectory($path)
    {
        if (!is_dir($path)) {
            return false;
        }

        $files = array_diff(scandir($path), ['.', '..']);
        foreach ($files as $file) {
            $filepath = $path . '/' . $file;
            if (is_dir($filepath)) {
                $this->cleanDirectory($filepath);
            } else {
                unlink($filepath);
            }
        }

        return rmdir($path);
    }

    /**
     * Vérifie les permissions d'un répertoire
     */
    public function checkDirectoryPermissions($path)
    {
        if (!is_dir($path)) {
            return false;
        }

        return is_writable($path) && is_readable($path);
    }

    /**
     * Récupère la taille d'un fichier
     */
    public function getFileSize($filepath)
    {
        if (file_exists($filepath)) {
            return filesize($filepath);
        }
        return 0;
    }

    /**
     * Récupère le type MIME d'un fichier
     */
    public function getMimeType($filepath)
    {
        if (file_exists($filepath)) {
            return mime_content_type($filepath);
        }
        return null;
    }
} 