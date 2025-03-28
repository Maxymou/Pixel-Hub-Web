// Gestionnaire de widgets de type alert
class AlertWidget {
    constructor(widgetId, config) {
        this.widgetId = widgetId;
        this.config = config;
        this.container = document.getElementById(`alerts-${widgetId}`);
        this.previousAlerts = new Set();
    }

    update() {
        if (!state.alerts || !this.container) return;

        // Filtrer les alertes selon la configuration
        const filteredAlerts = state.alerts.filter(alert => {
            if (alert.level === 'warning' && !this.config.show_warnings) return false;
            if (alert.level === 'error' && !this.config.show_errors) return false;
            return true;
        });

        // Vérifier s'il y a de nouvelles alertes
        const newAlerts = filteredAlerts.filter(alert => !this.previousAlerts.has(alert.timestamp));
        
        if (newAlerts.length > 0) {
            this.showNotification(newAlerts);
        }

        // Mettre à jour l'affichage
        this.container.innerHTML = filteredAlerts
            .map(alert => this.createAlertItem(alert))
            .join('');

        // Mettre à jour l'historique des alertes
        this.previousAlerts = new Set(filteredAlerts.map(alert => alert.timestamp));
    }

    createAlertItem(alert) {
        const icon = this.getAlertIcon(alert.level);
        const color = this.getAlertColor(alert.level);
        const time = new Date(alert.timestamp * 1000).toLocaleTimeString();

        return `
            <div class="flex items-start p-3 bg-${color}-50 rounded-lg border border-${color}-200 alert-item">
                <div class="flex-shrink-0">
                    ${icon}
                </div>
                <div class="ml-3">
                    <p class="text-sm text-${color}-800">${alert.message}</p>
                    <p class="text-xs text-${color}-600 mt-1">${time}</p>
                </div>
            </div>
        `;
    }

    getAlertIcon(level) {
        switch (level) {
            case 'error':
                return '<i class="fas fa-exclamation-circle text-red-500"></i>';
            case 'warning':
                return '<i class="fas fa-exclamation-triangle text-yellow-500"></i>';
            default:
                return '<i class="fas fa-info-circle text-blue-500"></i>';
        }
    }

    getAlertColor(level) {
        switch (level) {
            case 'error':
                return 'red';
            case 'warning':
                return 'yellow';
            default:
                return 'blue';
        }
    }

    showNotification(alerts) {
        if (!('Notification' in window)) return;

        // Demander la permission si nécessaire
        if (Notification.permission !== 'granted') {
            Notification.requestPermission();
        }

        // Afficher les notifications
        alerts.forEach(alert => {
            if (Notification.permission === 'granted') {
                new Notification('Pixel Hub - Alerte système', {
                    body: alert.message,
                    icon: '/images/logo.svg'
                });
            }
        });
    }
}

// Fonction de mise à jour du widget alert
function updateAlertWidget(widget) {
    const alertWidget = new AlertWidget(widget.id, widget.config);
    alertWidget.update();
} 