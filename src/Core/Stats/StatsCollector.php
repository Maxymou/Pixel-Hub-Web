<?php

namespace App\Core\Stats;

use App\Core\Logging\Logger;

class StatsCollector
{
    private $logger;
    private $statsFile = 'storage/stats.json';
    private $stats = [];
    private $updateInterval = 60; // 1 minute

    public function __construct()
    {
        $this->logger = new Logger();
        $this->loadStats();
    }

    /**
     * Récupère les statistiques d'une application
     */
    public function getAppStats($appId)
    {
        $this->updateStats($appId);
        return $this->stats[$appId] ?? null;
    }

    /**
     * Met à jour les statistiques d'une application
     */
    private function updateStats($appId)
    {
        if (!$this->shouldUpdateStats($appId)) {
            return;
        }

        try {
            $stats = [
                'cpu_usage' => $this->getCPUUsage($appId),
                'memory_usage' => $this->getMemoryUsage($appId),
                'disk_usage' => $this->getDiskUsage($appId),
                'network_usage' => $this->getNetworkUsage($appId),
                'uptime' => $this->getUptime($appId),
                'last_update' => time()
            ];

            $this->stats[$appId] = $stats;
            $this->saveStats();

        } catch (\Exception $e) {
            $this->logger->error("Erreur lors de la mise à jour des statistiques pour l'application {$appId}: " . $e->getMessage());
        }
    }

    /**
     * Vérifie si les statistiques doivent être mises à jour
     */
    private function shouldUpdateStats($appId)
    {
        if (!isset($this->stats[$appId])) {
            return true;
        }

        $lastUpdate = $this->stats[$appId]['last_update'] ?? 0;
        return (time() - $lastUpdate) >= $this->updateInterval;
    }

    /**
     * Récupère l'utilisation CPU
     */
    private function getCPUUsage($appId)
    {
        $cmd = "ps aux | grep 'app_id={$appId}' | grep -v grep | awk '{print $3}'";
        exec($cmd, $output);
        return floatval($output[0] ?? 0);
    }

    /**
     * Récupère l'utilisation mémoire
     */
    private function getMemoryUsage($appId)
    {
        $cmd = "ps aux | grep 'app_id={$appId}' | grep -v grep | awk '{print $4}'";
        exec($cmd, $output);
        return floatval($output[0] ?? 0);
    }

    /**
     * Récupère l'utilisation disque
     */
    private function getDiskUsage($appId)
    {
        $app = $this->getAppInfo($appId);
        if (!$app) {
            return 0;
        }

        $cmd = "du -sh {$app['working_directory']} | awk '{print $1}'";
        exec($cmd, $output);
        return $output[0] ?? '0B';
    }

    /**
     * Récupère l'utilisation réseau
     */
    private function getNetworkUsage($appId)
    {
        $cmd = "netstat -i | grep -v '^Kernel' | grep -v '^Iface' | awk '{print $1,$3,$7}'";
        exec($cmd, $output);

        $networkStats = [];
        foreach ($output as $line) {
            list($interface, $received, $transmitted) = explode(' ', $line);
            $networkStats[$interface] = [
                'received' => $received,
                'transmitted' => $transmitted
            ];
        }

        return $networkStats;
    }

    /**
     * Récupère le temps de fonctionnement
     */
    private function getUptime($appId)
    {
        $sql = "SELECT started_at FROM apps WHERE id = ?";
        $result = $this->db->query($sql, [$appId]);
        
        if (!$result || !$result['started_at']) {
            return 0;
        }

        return time() - strtotime($result['started_at']);
    }

    /**
     * Charge les statistiques depuis le fichier
     */
    private function loadStats()
    {
        if (file_exists($this->statsFile)) {
            $this->stats = json_decode(file_get_contents($this->statsFile), true) ?? [];
        }
    }

    /**
     * Sauvegarde les statistiques dans le fichier
     */
    private function saveStats()
    {
        file_put_contents($this->statsFile, json_encode($this->stats));
    }

    /**
     * Récupère les informations d'une application
     */
    private function getAppInfo($appId)
    {
        $sql = "SELECT * FROM apps WHERE id = ?";
        return $this->db->query($sql, [$appId]);
    }

    /**
     * Nettoie les anciennes statistiques
     */
    public function cleanupOldStats()
    {
        $maxAge = 7 * 24 * 3600; // 7 jours
        $now = time();

        foreach ($this->stats as $appId => $appStats) {
            if (($now - $appStats['last_update']) > $maxAge) {
                unset($this->stats[$appId]);
            }
        }

        $this->saveStats();
    }

    /**
     * Récupère les statistiques globales
     */
    public function getGlobalStats()
    {
        $stats = [
            'total_apps' => count($this->stats),
            'running_apps' => 0,
            'total_cpu_usage' => 0,
            'total_memory_usage' => 0,
            'total_disk_usage' => '0B'
        ];

        foreach ($this->stats as $appStats) {
            if ($appStats['uptime'] > 0) {
                $stats['running_apps']++;
            }
            $stats['total_cpu_usage'] += $appStats['cpu_usage'];
            $stats['total_memory_usage'] += $appStats['memory_usage'];
        }

        return $stats;
    }
} 