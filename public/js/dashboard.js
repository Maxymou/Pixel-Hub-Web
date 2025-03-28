// Gestionnaire du tableau de bord
class DashboardManager {
    constructor() {
        this.statsInterval = null;
        this.appsInterval = null;
        this.notificationsInterval = null;
        this.updatesInterval = null;
        this.backupsInterval = null;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.startStatsUpdates();
        this.loadRecentApps();
        this.loadNotifications();
        this.checkUpdates();
        this.loadBackups();
    }

    setupEventListeners() {
        // Gestionnaire pour le bouton d'ajout d'application
        document.querySelector('[data-widget-id="recent-apps"] .btn-outline-primary')?.addEventListener('click', () => {
            const modal = new bootstrap.Modal(document.getElementById('addAppModal'));
            modal.show();
        });

        // Gestionnaire pour le formulaire d'ajout d'application
        document.getElementById('addAppForm')?.addEventListener('submit', this.handleAddApp.bind(this));

        // Gestionnaires pour les boutons d'actualisation
        document.querySelectorAll('.widget .btn-outline-secondary').forEach(button => {
            button.addEventListener('click', (e) => {
                const widgetId = e.target.closest('.widget').dataset.widgetId;
                this.refreshWidget(widgetId);
            });
        });
    }

    startStatsUpdates() {
        this.updateStats();
        this.statsInterval = setInterval(() => this.updateStats(), 5000);
    }

    async updateStats() {
        try {
            const response = await fetch('/api/stats');
            const stats = await response.json();

            // Mise à jour des widgets de statistiques
            this.updateStatsWidget('stats-cpu', stats.cpu);
            this.updateStatsWidget('stats-memory', stats.memory);
            this.updateStatsWidget('stats-disk', stats.disk);
            this.updateStatsWidget('stats-network', stats.network);

            // Mise à jour des timestamps
            document.querySelectorAll('.last-update').forEach(el => {
                el.textContent = 'quelques secondes';
            });
        } catch (error) {
            console.error('Erreur lors de la mise à jour des statistiques:', error);
        }
    }

    updateStatsWidget(widgetId, value) {
        const widget = document.querySelector(`[data-widget-id="${widgetId}"]`);
        if (!widget) return;

        const progressBar = widget.querySelector('.progress-bar');
        if (progressBar) {
            progressBar.style.width = `${value}%`;
            progressBar.textContent = `${value}%`;
            progressBar.className = `progress-bar ${this.getProgressBarClass(value)}`;
        }
    }

    getProgressBarClass(value) {
        if (value >= 90) return 'bg-danger';
        if (value >= 70) return 'bg-warning';
        return 'bg-success';
    }

    async loadRecentApps() {
        try {
            const response = await fetch('/api/apps/recent');
            const apps = await response.json();
            this.renderRecentApps(apps);
        } catch (error) {
            console.error('Erreur lors du chargement des applications récentes:', error);
        }
    }

    renderRecentApps(apps) {
        const tbody = document.querySelector('[data-widget-id="recent-apps"] tbody');
        if (!tbody) return;

        tbody.innerHTML = apps.map(app => `
            <tr>
                <td>
                    <img src="${app.icon}" alt="${app.name}" class="me-2" style="width: 24px; height: 24px;">
                    ${app.name}
                </td>
                <td>${this.getAppTypeLabel(app.type)}</td>
                <td>
                    <span class="badge ${this.getStatusBadgeClass(app.status)}">
                        ${this.getStatusLabel(app.status)}
                    </span>
                </td>
                <td>${this.formatDate(app.lastUsed)}</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Démarrer">
                            <i class="fas fa-play"></i>
                        </button>
                        <button class="btn btn-outline-danger" data-bs-toggle="tooltip" title="Arrêter">
                            <i class="fas fa-stop"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');

        // Réinitialiser les tooltips
        const tooltipTriggerList = [].slice.call(tbody.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    getAppTypeLabel(type) {
        const types = {
            game: 'Jeu',
            utility: 'Utilitaire',
            media: 'Média',
            network: 'Réseau',
            system: 'Système'
        };
        return types[type] || type;
    }

    getStatusBadgeClass(status) {
        const classes = {
            running: 'bg-success',
            stopped: 'bg-danger',
            error: 'bg-warning'
        };
        return classes[status] || 'bg-secondary';
    }

    getStatusLabel(status) {
        const labels = {
            running: 'En cours',
            stopped: 'Arrêté',
            error: 'Erreur'
        };
        return labels[status] || status;
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return new Intl.DateTimeFormat('fr-FR', {
            dateStyle: 'short',
            timeStyle: 'short'
        }).format(date);
    }

    async handleAddApp(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        
        try {
            const response = await fetch('/api/apps', {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('addAppModal'));
                modal.hide();
                form.reset();
                this.loadRecentApps();
                notify('Application ajoutée avec succès', 'success');
            } else {
                throw new Error('Erreur lors de l\'ajout de l\'application');
            }
        } catch (error) {
            console.error('Erreur:', error);
            notify('Erreur lors de l\'ajout de l\'application', 'error');
        }
    }

    async loadNotifications() {
        try {
            const response = await fetch('/api/notifications');
            const notifications = await response.json();
            this.renderNotifications(notifications);
        } catch (error) {
            console.error('Erreur lors du chargement des notifications:', error);
        }
    }

    renderNotifications(notifications) {
        const container = document.querySelector('[data-widget-id="notifications"] .list-group');
        if (!container) return;

        container.innerHTML = notifications.map(notification => `
            <div class="list-group-item ${notification.read ? '' : 'list-group-item-primary'}">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">${notification.title}</h6>
                    <small>${this.formatDate(notification.created_at)}</small>
                </div>
                <p class="mb-1">${notification.message}</p>
            </div>
        `).join('');
    }

    async checkUpdates() {
        try {
            const response = await fetch('/api/updates/check');
            const updates = await response.json();
            this.renderUpdates(updates);
        } catch (error) {
            console.error('Erreur lors de la vérification des mises à jour:', error);
        }
    }

    renderUpdates(updates) {
        const container = document.querySelector('[data-widget-id="updates"] .list-group');
        if (!container) return;

        if (updates.length === 0) {
            container.innerHTML = '<div class="list-group-item">Aucune mise à jour disponible</div>';
            return;
        }

        container.innerHTML = updates.map(update => `
            <div class="list-group-item">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">${update.name}</h6>
                    <small>${update.version}</small>
                </div>
                <p class="mb-1">${update.description}</p>
                <button class="btn btn-sm btn-primary" onclick="dashboardManager.installUpdate('${update.id}')">
                    Installer
                </button>
            </div>
        `).join('');
    }

    async loadBackups() {
        try {
            const response = await fetch('/api/backups');
            const backups = await response.json();
            this.renderBackups(backups);
        } catch (error) {
            console.error('Erreur lors du chargement des sauvegardes:', error);
        }
    }

    renderBackups(backups) {
        const container = document.querySelector('[data-widget-id="backups"] .list-group');
        if (!container) return;

        if (backups.length === 0) {
            container.innerHTML = '<div class="list-group-item">Aucune sauvegarde disponible</div>';
            return;
        }

        container.innerHTML = backups.map(backup => `
            <div class="list-group-item">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">Sauvegarde du ${this.formatDate(backup.created_at)}</h6>
                    <small>${this.formatSize(backup.size)}</small>
                </div>
                <p class="mb-1">${backup.description}</p>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="dashboardManager.restoreBackup('${backup.id}')">
                        Restaurer
                    </button>
                    <button class="btn btn-outline-danger" onclick="dashboardManager.deleteBackup('${backup.id}')">
                        Supprimer
                    </button>
                </div>
            </div>
        `).join('');
    }

    formatSize(bytes) {
        const units = ['B', 'KB', 'MB', 'GB', 'TB'];
        let size = bytes;
        let unitIndex = 0;

        while (size >= 1024 && unitIndex < units.length - 1) {
            size /= 1024;
            unitIndex++;
        }

        return `${size.toFixed(1)} ${units[unitIndex]}`;
    }

    async refreshWidget(widgetId) {
        switch (widgetId) {
            case 'stats-cpu':
            case 'stats-memory':
            case 'stats-disk':
            case 'stats-network':
                await this.updateStats();
                break;
            case 'recent-apps':
                await this.loadRecentApps();
                break;
            case 'notifications':
                await this.loadNotifications();
                break;
            case 'updates':
                await this.checkUpdates();
                break;
            case 'backups':
                await this.loadBackups();
                break;
        }
    }

    async installUpdate(updateId) {
        try {
            const loading = showLoading();
            const response = await fetch(`/api/updates/install/${updateId}`, {
                method: 'POST'
            });

            if (response.ok) {
                notify('Mise à jour installée avec succès', 'success');
                this.checkUpdates();
            } else {
                throw new Error('Erreur lors de l\'installation de la mise à jour');
            }
        } catch (error) {
            console.error('Erreur:', error);
            notify('Erreur lors de l\'installation de la mise à jour', 'error');
        } finally {
            hideLoading(loading);
        }
    }

    async restoreBackup(backupId) {
        if (!confirm('Êtes-vous sûr de vouloir restaurer cette sauvegarde ?')) {
            return;
        }

        try {
            const loading = showLoading();
            const response = await fetch(`/api/backups/restore/${backupId}`, {
                method: 'POST'
            });

            if (response.ok) {
                notify('Sauvegarde restaurée avec succès', 'success');
                this.loadBackups();
            } else {
                throw new Error('Erreur lors de la restauration de la sauvegarde');
            }
        } catch (error) {
            console.error('Erreur:', error);
            notify('Erreur lors de la restauration de la sauvegarde', 'error');
        } finally {
            hideLoading(loading);
        }
    }

    async deleteBackup(backupId) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer cette sauvegarde ?')) {
            return;
        }

        try {
            const loading = showLoading();
            const response = await fetch(`/api/backups/${backupId}`, {
                method: 'DELETE'
            });

            if (response.ok) {
                notify('Sauvegarde supprimée avec succès', 'success');
                this.loadBackups();
            } else {
                throw new Error('Erreur lors de la suppression de la sauvegarde');
            }
        } catch (error) {
            console.error('Erreur:', error);
            notify('Erreur lors de la suppression de la sauvegarde', 'error');
        } finally {
            hideLoading(loading);
        }
    }
}

// Initialisation
let dashboardManager;

document.addEventListener('DOMContentLoaded', () => {
    dashboardManager = new DashboardManager();
}); 