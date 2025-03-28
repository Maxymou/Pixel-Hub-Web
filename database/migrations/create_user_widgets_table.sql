CREATE TABLE IF NOT EXISTS user_widgets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    widget_id INT UNSIGNED NOT NULL,
    position INT UNSIGNED NOT NULL DEFAULT 0,
    config JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (widget_id) REFERENCES widgets(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_widget_position (user_id, position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS widgets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    type VARCHAR(20) NOT NULL,
    default_config JSON NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_widget_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insérer les widgets par défaut
INSERT INTO widgets (name, description, type, default_config) VALUES
('system_metrics', 'Métriques système en temps réel', 'chart', '{"refresh_interval": 30, "show_cpu": true, "show_memory": true, "show_disk": true}'),
('recent_apps', 'Applications récemment modifiées', 'list', '{"limit": 5, "show_icon": true, "show_date": true}'),
('system_alerts', 'Alertes système', 'alert', '{"show_warnings": true, "show_errors": true, "auto_refresh": true}'),
('system_status', 'État du système', 'status', '{"show_uptime": true, "show_temperature": true, "show_processes": true}'); 