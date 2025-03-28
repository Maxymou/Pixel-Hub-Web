// Gestionnaire de widgets de type status
class StatusWidget {
    constructor(widgetId, config) {
        this.widgetId = widgetId;
        this.config = config;
        this.container = document.getElementById(`status-${widgetId}`);
    }

    update() {
        if (!state.metrics || !this.container) return;

        this.container.innerHTML = `
            ${this.config.show_uptime ? this.createUptimeSection() : ''}
            ${this.config.show_temperature ? this.createTemperatureSection() : ''}
            ${this.config.show_processes ? this.createProcessesSection() : ''}
        `;
    }

    createUptimeSection() {
        const uptime = this.formatUptime(state.metrics.uptime);
        return `
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-clock text-gray-500"></i>
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">Temps de fonctionnement</h4>
                        <p class="text-xs text-gray-500">${uptime}</p>
                    </div>
                </div>
            </div>
        `;
    }

    createTemperatureSection() {
        const temp = state.metrics.temperature;
        const tempClass = this.getTemperatureClass(temp);
        
        return `
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-thermometer-half text-gray-500"></i>
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">Température CPU</h4>
                        <p class="text-xs ${tempClass}">${temp.toFixed(1)}°C</p>
                    </div>
                </div>
            </div>
        `;
    }

    createProcessesSection() {
        const cpu = state.metrics.cpu;
        const memory = state.metrics.memory;
        
        return `
            <div class="space-y-2">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-microchip text-gray-500"></i>
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">CPU</h4>
                            <div class="w-32 bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: ${cpu}%"></div>
                            </div>
                            <p class="text-xs text-gray-500">${cpu.toFixed(1)}%</p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-memory text-gray-500"></i>
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">Mémoire</h4>
                            <div class="w-32 bg-gray-200 rounded-full h-2">
                                <div class="bg-green-600 h-2 rounded-full" style="width: ${memory}%"></div>
                            </div>
                            <p class="text-xs text-gray-500">${memory.toFixed(1)}%</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    formatUptime(seconds) {
        const days = Math.floor(seconds / 86400);
        const hours = Math.floor((seconds % 86400) / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);

        const parts = [];
        if (days > 0) parts.push(`${days}j`);
        if (hours > 0) parts.push(`${hours}h`);
        if (minutes > 0) parts.push(`${minutes}m`);

        return parts.join(' ') || '0m';
    }

    getTemperatureClass(temp) {
        if (temp >= 75) return 'text-red-500';
        if (temp >= 60) return 'text-yellow-500';
        return 'text-green-500';
    }
}

// Fonction de mise à jour du widget status
function updateStatusWidget(widget) {
    const statusWidget = new StatusWidget(widget.id, widget.config);
    statusWidget.update();
} 