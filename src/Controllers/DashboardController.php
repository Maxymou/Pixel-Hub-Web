<?php

namespace App\Controllers;

use App\Core\Application;
use App\Core\Database;
use App\Core\Cache\CacheManager;
use App\Core\Validation\Validator;
use App\Core\Controller;
use App\Core\Stats\StatsCollector;
use App\Core\Process\ProcessManager;
use App\Core\Backup\BackupManager;
use App\Core\Update\UpdateManager;

class DashboardController extends Controller
{
    private Database $db;
    private CacheManager $cache;
    private const CACHE_TTL = 300; // 5 minutes
    private const MAX_WIDGETS = 10;
    private const ALERT_THRESHOLDS = [
        'cpu' => 80, // 80% d'utilisation CPU
        'memory' => 85, // 85% d'utilisation mémoire
        'disk' => 90, // 90% d'utilisation disque
        'temperature' => 75 // 75°C
    ];
    private $statsCollector;
    private $processManager;
    private $backupManager;
    private $updateManager;

    public function __construct()
    {
        parent::__construct();
        $this->db = Application::getInstance()->getDatabase();
        $this->cache = Application::getInstance()->getCache();
        $this->statsCollector = new StatsCollector();
        $this->processManager = new ProcessManager();
        $this->backupManager = new BackupManager();
        $this->updateManager = new UpdateManager();
    }

    public function index()
    {
        return $this->view('dashboard/index');
    }

    /**
     * Récupère les métriques système en temps réel
     */
    public function getSystemMetrics(): array
    {
        $cacheKey = 'system_metrics_' . $_REQUEST['user']['id'];
        
        // Vérifier le cache
        if ($metrics = $this->cache->get($cacheKey)) {
            return $metrics;
        }

        // Récupérer les métriques système
        $metrics = [
            'cpu' => $this->getCPUUsage(),
            'memory' => $this->getMemoryUsage(),
            'disk' => $this->getDiskUsage(),
            'temperature' => $this->getCPUTemperature(),
            'uptime' => $this->getSystemUptime(),
            'timestamp' => time()
        ];

        // Vérifier les alertes
        $metrics['alerts'] = $this->checkSystemAlerts($metrics);

        // Mettre en cache
        $this->cache->set($cacheKey, $metrics, self::CACHE_TTL);

        return $metrics;
    }

    /**
     * Récupère la liste des widgets de l'utilisateur
     */
    public function getUserWidgets(): array
    {
        $userId = $_REQUEST['user']['id'];
        $cacheKey = 'user_widgets_' . $userId;

        if ($widgets = $this->cache->get($cacheKey)) {
            return $widgets;
        }

        $widgets = $this->db->query(
            "SELECT * FROM user_widgets WHERE user_id = ? ORDER BY position ASC",
            [$userId]
        )->fetchAll();

        $this->cache->set($cacheKey, $widgets, self::CACHE_TTL);

        return $widgets;
    }

    /**
     * Met à jour la configuration des widgets
     */
    public function updateWidgetConfig(): array
    {
        $userId = $_REQUEST['user']['id'];
        $data = json_decode(file_get_contents('php://input'), true);

        // Valider les données
        $validator = new Validator($data);
        if (!$validator->validate([
            'widgets' => ['required', 'array', 'max:' . self::MAX_WIDGETS],
            'widgets.*.id' => ['required', 'numeric'],
            'widgets.*.position' => ['required', 'numeric', 'min:0'],
            'widgets.*.config' => ['required', 'array']
        ])) {
            throw new \InvalidArgumentException($validator->getFirstError());
        }

        // Supprimer les anciennes configurations
        $this->db->query(
            "DELETE FROM user_widgets WHERE user_id = ?",
            [$userId]
        );

        // Insérer les nouvelles configurations
        foreach ($data['widgets'] as $widget) {
            $this->db->query(
                "INSERT INTO user_widgets (user_id, widget_id, position, config) VALUES (?, ?, ?, ?)",
                [$userId, $widget['id'], $widget['position'], json_encode($widget['config'])]
            );
        }

        // Invalider le cache
        $this->cache->delete('user_widgets_' . $userId);

        return ['success' => true];
    }

    /**
     * Récupère la liste des applications récemment modifiées
     */
    public function getRecentApps(): array
    {
        $userId = $_REQUEST['user']['id'];
        $cacheKey = 'recent_apps_' . $userId;

        if ($apps = $this->cache->get($cacheKey)) {
            return $apps;
        }

        $apps = $this->db->query(
            "SELECT a.*, ua.last_accessed 
             FROM apps a 
             JOIN user_apps ua ON a.id = ua.app_id 
             WHERE ua.user_id = ? 
             ORDER BY ua.last_accessed DESC 
             LIMIT 5",
            [$userId]
        )->fetchAll();

        $this->cache->set($cacheKey, $apps, self::CACHE_TTL);

        return $apps;
    }

    /**
     * Récupère les alertes système
     */
    public function getSystemAlerts(): array
    {
        $userId = $_REQUEST['user']['id'];
        $cacheKey = 'system_alerts_' . $userId;

        if ($alerts = $this->cache->get($cacheKey)) {
            return $alerts;
        }

        $metrics = $this->getSystemMetrics();
        $alerts = $this->checkSystemAlerts($metrics);

        $this->cache->set($cacheKey, $alerts, 60); // 1 minute de cache pour les alertes

        return $alerts;
    }

    /**
     * Vérifie les seuils d'alerte système
     */
    private function checkSystemAlerts(array $metrics): array
    {
        $alerts = [];

        foreach (self::ALERT_THRESHOLDS as $metric => $threshold) {
            if (isset($metrics[$metric]) && $metrics[$metric] > $threshold) {
                $alerts[] = [
                    'type' => $metric,
                    'level' => 'warning',
                    'message' => "Utilisation $metric élevée: {$metrics[$metric]}%",
                    'timestamp' => time()
                ];
            }
        }

        return $alerts;
    }

    /**
     * Récupère l'utilisation CPU
     */
    private function getCPUUsage(): float
    {
        $load = sys_getloadavg();
        return $load[0] * 100;
    }

    /**
     * Récupère l'utilisation mémoire
     */
    private function getMemoryUsage(): float
    {
        $memory = file_get_contents('/proc/meminfo');
        preg_match('/MemTotal:\s+(\d+)/', $memory, $total);
        preg_match('/MemAvailable:\s+(\d+)/', $memory, $available);
        
        if (empty($total[1]) || empty($available[1])) {
            return 0;
        }

        return (($total[1] - $available[1]) / $total[1]) * 100;
    }

    /**
     * Récupère l'utilisation disque
     */
    private function getDiskUsage(): float
    {
        $total = disk_total_space('/');
        $free = disk_free_space('/');
        
        if ($total === false || $free === false) {
            return 0;
        }

        return (($total - $free) / $total) * 100;
    }

    /**
     * Récupère la température CPU
     */
    private function getCPUTemperature(): float
    {
        $temp = file_get_contents('/sys/class/thermal/thermal_zone0/temp');
        return $temp ? (float)$temp / 1000 : 0;
    }

    /**
     * Récupère le temps de fonctionnement du système
     */
    private function getSystemUptime(): int
    {
        $uptime = file_get_contents('/proc/uptime');
        return $uptime ? (int)explode(' ', $uptime)[0] : 0;
    }

    public function getStats()
    {
        try {
            $stats = $this->statsCollector->getStats();
            return $this->json($stats);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getNotifications()
    {
        try {
            $notifications = $this->getNotificationsFromDatabase();
            return $this->json($notifications);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function checkUpdates()
    {
        try {
            $updates = $this->updateManager->checkForUpdates();
            return $this->json($updates);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function installUpdate($id)
    {
        try {
            $this->updateManager->installUpdate($id);
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getBackups()
    {
        try {
            $backups = $this->backupManager->listBackups();
            return $this->json($backups);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function restoreBackup($id)
    {
        try {
            $this->backupManager->restoreBackup($id);
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteBackup($id)
    {
        try {
            $this->backupManager->deleteBackup($id);
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    private function getNotificationsFromDatabase()
    {
        // TODO: Implémenter la récupération des notifications depuis la base de données
        return [
            [
                'id' => 1,
                'title' => 'Mise à jour disponible',
                'message' => 'Une nouvelle version de l\'application est disponible.',
                'created_at' => date('Y-m-d H:i:s'),
                'read' => false
            ],
            [
                'id' => 2,
                'title' => 'Sauvegarde réussie',
                'message' => 'La sauvegarde automatique a été effectuée avec succès.',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour')),
                'read' => true
            ]
        ];
    }
} 