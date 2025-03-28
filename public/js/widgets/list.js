// Gestionnaire de widgets de type list
class ListWidget {
    constructor(widgetId, config) {
        this.widgetId = widgetId;
        this.config = config;
        this.container = document.getElementById(`list-${widgetId}`);
    }

    update() {
        if (!state.recentApps || !this.container) return;

        this.container.innerHTML = state.recentApps
            .map(app => this.createAppItem(app))
            .join('');
    }

    createAppItem(app) {
        const date = new Date(app.last_accessed).toLocaleString();
        const icon = this.config.show_icon ? this.getAppIcon(app) : '';
        
        return `
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors list-item">
                <div class="flex items-center space-x-3">
                    ${icon}
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">${app.name}</h4>
                        <p class="text-xs text-gray-500">${app.description || ''}</p>
                    </div>
                </div>
                ${this.config.show_date ? `
                    <span class="text-xs text-gray-500">${date}</span>
                ` : ''}
            </div>
        `;
    }

    getAppIcon(app) {
        const iconUrl = iconManager.getIconUrl(app);

        return `
            <div class="widget-icon">
                <img src="${iconUrl}" 
                     alt="${app.name}" 
                     class="w-8 h-8 rounded-lg object-cover"
                     onerror="this.src='/images/default-app-icon.svg'">
            </div>
        `;
    }
}

// Fonction de mise à jour du widget list
function updateListWidget(widget) {
    const listWidget = new ListWidget(widget.id, widget.config);
    listWidget.update();
} 