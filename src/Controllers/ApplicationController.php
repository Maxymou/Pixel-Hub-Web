<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validation\Validator;
use App\Core\Logging\Logger;
use App\Core\Process\ProcessManager;
use App\Core\Stats\StatsCollector;

class ApplicationController extends Controller
{
    private $validator;
    private $logger;
    private $processManager;
    private $statsCollector;

    public function __construct()
    {
        parent::__construct();
        $this->validator = new Validator();
        $this->logger = new Logger();
        $this->processManager = new ProcessManager();
        $this->statsCollector = new StatsCollector();
    }

    /**
     * Liste des applications avec filtres et recherche
     */
    public function index()
    {
        try {
            // Récupérer les paramètres de filtrage
            $filters = $this->getFilters();
            $search = $_GET['search'] ?? '';
            $page = max(1, intval($_GET['page'] ?? 1));
            $perPage = 10;

            // Construire la requête
            $query = "SELECT * FROM apps WHERE 1=1";
            $params = [];

            // Appliquer les filtres
            if (!empty($filters['type'])) {
                $query .= " AND type = ?";
                $params[] = $filters['type'];
            }

            if (!empty($filters['status'])) {
                $query .= " AND status = ?";
                $params[] = $filters['status'];
            }

            // Appliquer la recherche
            if (!empty($search)) {
                $query .= " AND (name LIKE ? OR description LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }

            // Pagination
            $total = $this->db->count($query, $params);
            $totalPages = ceil($total / $perPage);
            $offset = ($page - 1) * $perPage;
            $query .= " LIMIT ? OFFSET ?";
            $params[] = $perPage;
            $params[] = $offset;

            // Récupérer les applications
            $apps = $this->db->queryAll($query, $params);

            // Récupérer les statistiques pour chaque application
            foreach ($apps as &$app) {
                $app['stats'] = $this->statsCollector->getAppStats($app['id']);
            }

            return $this->json([
                'apps' => $apps,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_items' => $total
                ],
                'filters' => $filters
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la récupération des applications: ' . $e->getMessage());
            return $this->json(['error' => 'Erreur lors de la récupération des applications'], 500);
        }
    }

    /**
     * Création d'une nouvelle application
     */
    public function store()
    {
        try {
            // Vérifier les permissions
            if (!$this->hasPermission('create_app')) {
                return $this->json(['error' => 'Permission refusée'], 403);
            }

            // Valider les données
            $data = $this->validateAppData($_POST);

            // Insérer l'application
            $sql = "INSERT INTO apps (name, description, type, command, working_directory, env_vars, created_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $this->db->execute($sql, [
                $data['name'],
                $data['description'],
                $data['type'],
                $data['command'],
                $data['working_directory'],
                json_encode($data['env_vars']),
                $this->auth->getCurrentUser()->id
            ]);

            $appId = $this->db->lastInsertId();

            // Créer le répertoire de logs
            $this->createAppLogDirectory($appId);

            // Logger l'action
            $this->logger->info("Application créée: {$data['name']}", [
                'app_id' => $appId,
                'user_id' => $this->auth->getCurrentUser()->id
            ]);

            return $this->json([
                'success' => true,
                'app_id' => $appId
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la création de l\'application: ' . $e->getMessage());
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Détails d'une application
     */
    public function show($id)
    {
        try {
            // Récupérer l'application
            $app = $this->getApp($id);
            if (!$app) {
                return $this->json(['error' => 'Application non trouvée'], 404);
            }

            // Récupérer les statistiques
            $stats = $this->statsCollector->getAppStats($id);

            // Récupérer les derniers logs
            $logs = $this->getAppLogs($id, 10);

            return $this->json([
                'app' => $app,
                'stats' => $stats,
                'logs' => $logs
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Erreur lors de la récupération de l'application {$id}: " . $e->getMessage());
            return $this->json(['error' => 'Erreur lors de la récupération de l\'application'], 500);
        }
    }

    /**
     * Mise à jour d'une application
     */
    public function update($id)
    {
        try {
            // Vérifier les permissions
            if (!$this->hasPermission('update_app')) {
                return $this->json(['error' => 'Permission refusée'], 403);
            }

            // Vérifier si l'application existe
            $app = $this->getApp($id);
            if (!$app) {
                return $this->json(['error' => 'Application non trouvée'], 404);
            }

            // Valider les données
            $data = $this->validateAppData($_POST);

            // Mettre à jour l'application
            $sql = "UPDATE apps SET 
                    name = ?, 
                    description = ?, 
                    type = ?, 
                    command = ?, 
                    working_directory = ?, 
                    env_vars = ?,
                    updated_at = NOW()
                    WHERE id = ?";
            
            $this->db->execute($sql, [
                $data['name'],
                $data['description'],
                $data['type'],
                $data['command'],
                $data['working_directory'],
                json_encode($data['env_vars']),
                $id
            ]);

            // Logger l'action
            $this->logger->info("Application mise à jour: {$data['name']}", [
                'app_id' => $id,
                'user_id' => $this->auth->getCurrentUser()->id
            ]);

            return $this->json(['success' => true]);

        } catch (\Exception $e) {
            $this->logger->error("Erreur lors de la mise à jour de l'application {$id}: " . $e->getMessage());
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Suppression d'une application
     */
    public function destroy($id)
    {
        try {
            // Vérifier les permissions
            if (!$this->hasPermission('delete_app')) {
                return $this->json(['error' => 'Permission refusée'], 403);
            }

            // Vérifier si l'application existe
            $app = $this->getApp($id);
            if (!$app) {
                return $this->json(['error' => 'Application non trouvée'], 404);
            }

            // Arrêter l'application si elle est en cours d'exécution
            if ($app['status'] === 'running') {
                $this->processManager->stopProcess($id);
            }

            // Supprimer l'application
            $sql = "DELETE FROM apps WHERE id = ?";
            $this->db->execute($sql, [$id]);

            // Supprimer les logs
            $this->deleteAppLogs($id);

            // Logger l'action
            $this->logger->info("Application supprimée: {$app['name']}", [
                'app_id' => $id,
                'user_id' => $this->auth->getCurrentUser()->id
            ]);

            return $this->json(['success' => true]);

        } catch (\Exception $e) {
            $this->logger->error("Erreur lors de la suppression de l'application {$id}: " . $e->getMessage());
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Actions rapides (start/stop/restart)
     */
    public function action($id, $action)
    {
        try {
            // Vérifier les permissions
            if (!$this->hasPermission('manage_app')) {
                return $this->json(['error' => 'Permission refusée'], 403);
            }

            // Vérifier si l'application existe
            $app = $this->getApp($id);
            if (!$app) {
                return $this->json(['error' => 'Application non trouvée'], 404);
            }

            $result = false;
            $message = '';

            switch ($action) {
                case 'start':
                    if ($app['status'] !== 'running') {
                        $result = $this->processManager->startProcess($id);
                        $message = 'Application démarrée';
                    }
                    break;

                case 'stop':
                    if ($app['status'] === 'running') {
                        $result = $this->processManager->stopProcess($id);
                        $message = 'Application arrêtée';
                    }
                    break;

                case 'restart':
                    $this->processManager->stopProcess($id);
                    $result = $this->processManager->startProcess($id);
                    $message = 'Application redémarrée';
                    break;

                default:
                    return $this->json(['error' => 'Action non valide'], 400);
            }

            if ($result) {
                $this->logger->info($message, [
                    'app_id' => $id,
                    'user_id' => $this->auth->getCurrentUser()->id
                ]);
                return $this->json(['success' => true, 'message' => $message]);
            }

            return $this->json(['error' => 'Échec de l\'action'], 500);

        } catch (\Exception $e) {
            $this->logger->error("Erreur lors de l'action {$action} sur l'application {$id}: " . $e->getMessage());
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Récupère les logs d'une application
     */
    public function logs($id)
    {
        try {
            // Vérifier les permissions
            if (!$this->hasPermission('view_logs')) {
                return $this->json(['error' => 'Permission refusée'], 403);
            }

            $page = max(1, intval($_GET['page'] ?? 1));
            $perPage = 50;
            $logs = $this->getAppLogs($id, $perPage, ($page - 1) * $perPage);

            return $this->json([
                'logs' => $logs,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage
                ]
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Erreur lors de la récupération des logs de l'application {$id}: " . $e->getMessage());
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Méthodes privées utilitaires
     */
    private function validateAppData($data)
    {
        $rules = [
            'name' => 'required|min:3|max:100',
            'description' => 'max:500',
            'type' => 'required|in:game,utility,media,network,system',
            'command' => 'required',
            'working_directory' => 'required',
            'env_vars' => 'array'
        ];

        if (!$this->validator->validate($data, $rules)) {
            throw new \Exception($this->validator->getFirstError());
        }

        return $data;
    }

    private function getApp($id)
    {
        $sql = "SELECT * FROM apps WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    private function getFilters()
    {
        return [
            'type' => $_GET['type'] ?? null,
            'status' => $_GET['status'] ?? null
        ];
    }

    private function createAppLogDirectory($appId)
    {
        $logDir = "storage/logs/apps/{$appId}";
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    private function getAppLogs($appId, $limit = 10, $offset = 0)
    {
        $logFile = "storage/logs/apps/{$appId}/app.log";
        if (!file_exists($logFile)) {
            return [];
        }

        $logs = [];
        $handle = fopen($logFile, 'r');
        if ($handle) {
            $lines = [];
            while (($line = fgets($handle)) !== false) {
                $lines[] = $line;
                if (count($lines) > $limit + $offset) {
                    array_shift($lines);
                }
            }
            fclose($handle);

            $logs = array_slice($lines, $offset, $limit);
        }

        return array_map('trim', $logs);
    }

    private function deleteAppLogs($appId)
    {
        $logDir = "storage/logs/apps/{$appId}";
        if (is_dir($logDir)) {
            array_map('unlink', glob("$logDir/*.*"));
            rmdir($logDir);
        }
    }

    private function hasPermission($permission)
    {
        return $this->auth->hasPermission($permission);
    }
} 