<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Pixel Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/dashboard.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- En-tête -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-semibold text-gray-900">Tableau de bord</h1>
                    <div class="flex items-center space-x-4">
                        <button id="refreshBtn" class="action-button">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <button id="settingsBtn" class="action-button">
                            <i class="fas fa-cog"></i>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Contenu principal -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Grille de widgets -->
            <div id="widgetGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Les widgets seront injectés ici dynamiquement -->
            </div>
        </main>

        <!-- Modal des paramètres -->
        <div id="settingsModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full settings-modal">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white settings-content">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Configuration des widgets</h3>
                    <div id="widgetSettings" class="config-form">
                        <!-- Les paramètres des widgets seront injectés ici -->
                    </div>
                    <div class="mt-4 flex justify-end space-x-3">
                        <button id="cancelSettings" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                            Annuler
                        </button>
                        <button id="saveSettings" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Enregistrer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts des widgets -->
    <script src="/js/widgets/chart.js"></script>
    <script src="/js/widgets/list.js"></script>
    <script src="/js/widgets/alert.js"></script>
    <script src="/js/widgets/status.js"></script>

    <script>
        // Configuration globale
        const config = {
            refreshInterval: 30000, // 30 secondes
            apiBaseUrl: '/api/dashboard'
        };

        // État de l'application
        let state = {
            widgets: [],
            metrics: null,
            alerts: [],
            recentApps: []
        };

        // Initialisation
        document.addEventListener('DOMContentLoaded', () => {
            initializeWidgets();
            setupEventListeners();
            startAutoRefresh();
        });

        // Fonctions d'initialisation
        async function initializeWidgets() {
            try {
                const response = await fetch(`${config.apiBaseUrl}/widgets`);
                state.widgets = await response.json();
                renderWidgets();
            } catch (error) {
                console.error('Erreur lors du chargement des widgets:', error);
            }
        }

        function setupEventListeners() {
            // Bouton de rafraîchissement
            document.getElementById('refreshBtn').addEventListener('click', refreshAllData);

            // Bouton des paramètres
            document.getElementById('settingsBtn').addEventListener('click', showSettings);
            document.getElementById('cancelSettings').addEventListener('click', hideSettings);
            document.getElementById('saveSettings').addEventListener('click', saveSettings);

            // Configuration du drag & drop
            new Sortable(document.getElementById('widgetGrid'), {
                animation: 150,
                onEnd: updateWidgetPositions
            });
        }

        // Fonctions de rafraîchissement
        function startAutoRefresh() {
            setInterval(refreshAllData, config.refreshInterval);
        }

        async function refreshAllData() {
            try {
                await Promise.all([
                    refreshMetrics(),
                    refreshAlerts(),
                    refreshRecentApps()
                ]);
                updateWidgets();
            } catch (error) {
                console.error('Erreur lors du rafraîchissement:', error);
            }
        }

        async function refreshMetrics() {
            const response = await fetch(`${config.apiBaseUrl}/metrics`);
            state.metrics = await response.json();
        }

        async function refreshAlerts() {
            const response = await fetch(`${config.apiBaseUrl}/alerts`);
            state.alerts = await response.json();
        }

        async function refreshRecentApps() {
            const response = await fetch(`${config.apiBaseUrl}/recent-apps`);
            state.recentApps = await response.json();
        }

        // Fonctions de rendu
        function renderWidgets() {
            const grid = document.getElementById('widgetGrid');
            grid.innerHTML = '';

            state.widgets.forEach(widget => {
                const widgetElement = createWidgetElement(widget);
                grid.appendChild(widgetElement);
            });
        }

        function createWidgetElement(widget) {
            const div = document.createElement('div');
            div.className = 'bg-white rounded-lg shadow p-6';
            div.dataset.widgetId = widget.id;

            switch (widget.type) {
                case 'chart':
                    div.innerHTML = createChartWidget(widget);
                    break;
                case 'list':
                    div.innerHTML = createListWidget(widget);
                    break;
                case 'alert':
                    div.innerHTML = createAlertWidget(widget);
                    break;
                case 'status':
                    div.innerHTML = createStatusWidget(widget);
                    break;
            }

            return div;
        }

        // Fonctions de gestion des widgets
        function createChartWidget(widget) {
            return `
                <h3 class="text-lg font-medium text-gray-900 mb-4">${widget.name}</h3>
                <canvas id="chart-${widget.id}"></canvas>
            `;
        }

        function createListWidget(widget) {
            return `
                <h3 class="text-lg font-medium text-gray-900 mb-4">${widget.name}</h3>
                <div id="list-${widget.id}" class="space-y-2">
                    <!-- Le contenu sera mis à jour dynamiquement -->
                </div>
            `;
        }

        function createAlertWidget(widget) {
            return `
                <h3 class="text-lg font-medium text-gray-900 mb-4">${widget.name}</h3>
                <div id="alerts-${widget.id}" class="space-y-2">
                    <!-- Les alertes seront injectées ici -->
                </div>
            `;
        }

        function createStatusWidget(widget) {
            return `
                <h3 class="text-lg font-medium text-gray-900 mb-4">${widget.name}</h3>
                <div id="status-${widget.id}" class="space-y-2">
                    <!-- Le statut sera mis à jour dynamiquement -->
                </div>
            `;
        }

        // Fonctions de mise à jour
        function updateWidgets() {
            state.widgets.forEach(widget => {
                switch (widget.type) {
                    case 'chart':
                        updateChartWidget(widget);
                        break;
                    case 'list':
                        updateListWidget(widget);
                        break;
                    case 'alert':
                        updateAlertWidget(widget);
                        break;
                    case 'status':
                        updateStatusWidget(widget);
                        break;
                }
            });
        }

        // Fonctions de gestion des paramètres
        function showSettings() {
            document.getElementById('settingsModal').classList.remove('hidden');
            renderSettings();
        }

        function hideSettings() {
            document.getElementById('settingsModal').classList.add('hidden');
        }

        async function saveSettings() {
            try {
                const positions = Array.from(document.getElementById('widgetGrid').children)
                    .map((el, index) => ({
                        id: parseInt(el.dataset.widgetId),
                        position: index,
                        config: getWidgetConfigs()
                    }));

                await fetch(`${config.apiBaseUrl}/widgets`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ widgets: positions })
                });

                hideSettings();
                await initializeWidgets();
            } catch (error) {
                console.error('Erreur lors de la sauvegarde des paramètres:', error);
            }
        }

        // Fonctions utilitaires
        function updateWidgetPositions() {
            const positions = Array.from(document.getElementById('widgetGrid').children)
                .map((el, index) => ({
                    id: parseInt(el.dataset.widgetId),
                    position: index
                }));

            // Mettre à jour l'état local
            state.widgets.sort((a, b) => {
                const posA = positions.find(p => p.id === a.id)?.position ?? 0;
                const posB = positions.find(p => p.id === b.id)?.position ?? 0;
                return posA - posB;
            });
        }

        function getWidgetConfigs() {
            // Récupérer les configurations des widgets depuis le formulaire
            return state.widgets.map(widget => ({
                id: widget.id,
                config: widget.config
            }));
        }
    </script>
</body>
</html> 