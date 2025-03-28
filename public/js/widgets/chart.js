// Gestionnaire de widgets de type chart
class ChartWidget {
    constructor(widgetId, config) {
        this.widgetId = widgetId;
        this.config = config;
        this.chart = null;
        this.data = {
            labels: [],
            datasets: []
        };
    }

    init() {
        const ctx = document.getElementById(`chart-${this.widgetId}`).getContext('2d');
        
        this.chart = new Chart(ctx, {
            type: 'line',
            data: this.data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 0 // Désactiver l'animation pour de meilleures performances
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: value => `${value}%`
                        }
                    }
                }
            }
        });

        this.updateData();
    }

    updateData() {
        if (!state.metrics) return;

        const now = new Date().toLocaleTimeString();
        this.data.labels.push(now);
        
        // Limiter le nombre de points affichés
        if (this.data.labels.length > 20) {
            this.data.labels.shift();
        }

        // Mettre à jour les datasets
        if (this.config.show_cpu) {
            this.updateDataset('CPU', state.metrics.cpu, '#FF6384');
        }
        if (this.config.show_memory) {
            this.updateDataset('Mémoire', state.metrics.memory, '#36A2EB');
        }
        if (this.config.show_disk) {
            this.updateDataset('Disque', state.metrics.disk, '#FFCE56');
        }

        this.chart.update('none'); // Mise à jour sans animation
    }

    updateDataset(label, value, color) {
        let dataset = this.data.datasets.find(ds => ds.label === label);
        
        if (!dataset) {
            dataset = {
                label: label,
                data: [],
                borderColor: color,
                tension: 0.4,
                fill: false
            };
            this.data.datasets.push(dataset);
        }

        dataset.data.push(value);
        
        // Limiter le nombre de points
        if (dataset.data.length > 20) {
            dataset.data.shift();
        }
    }
}

// Fonction de mise à jour du widget chart
function updateChartWidget(widget) {
    const chartWidget = new ChartWidget(widget.id, widget.config);
    chartWidget.init();
} 