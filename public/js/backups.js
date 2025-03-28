class BackupManager {
    constructor() {
        this.initializeElements();
        this.initializeEventListeners();
        this.loadData();
    }

    initializeElements() {
        // Boutons
        this.createBackupBtn = document.getElementById('createBackupBtn');
        this.createScheduleBtn = document.getElementById('createScheduleBtn');

        // Modals
        this.createBackupModal = document.getElementById('createBackupModal');
        this.createScheduleModal = document.getElementById('createScheduleModal');
        this.confirmModal = document.getElementById('confirmModal');

        // Forms
        this.createBackupForm = document.getElementById('createBackupForm');
        this.createScheduleForm = document.getElementById('createScheduleForm');

        // Containers
        this.backupsList = document.getElementById('backupsList');
        this.schedulesList = document.getElementById('schedulesList');
        this.targetsContainer = document.getElementById('targetsContainer');
        this.scheduleTargetsContainer = document.getElementById('scheduleTargetsContainer');

        // Onglets
        this.tabButtons = document.querySelectorAll('.tab-btn');
        this.tabContents = document.querySelectorAll('.tab-content');
    }

    initializeEventListeners() {
        // Gestion des onglets
        this.tabButtons.forEach(button => {
            button.addEventListener('click', () => this.switchTab(button.dataset.tab));
        });

        // Gestion des modals
        this.createBackupBtn.addEventListener('click', () => this.showModal(this.createBackupModal));
        this.createScheduleBtn.addEventListener('click', () => this.showModal(this.createScheduleModal));
        document.querySelectorAll('.cancel-modal').forEach(button => {
            button.addEventListener('click', () => this.hideAllModals());
        });

        // Gestion des formulaires
        this.createBackupForm.addEventListener('submit', (e) => this.handleBackupSubmit(e));
        this.createScheduleForm.addEventListener('submit', (e) => this.handleScheduleSubmit(e));

        // Gestion du type de sauvegarde
        this.createBackupForm.querySelector('select[name="type"]').addEventListener('change', (e) => {
            this.targetsContainer.classList.toggle('hidden', e.target.value === 'full');
        });

        this.createScheduleForm.querySelector('select[name="type"]').addEventListener('change', (e) => {
            this.scheduleTargetsContainer.classList.toggle('hidden', e.target.value === 'full');
        });

        // Gestion de la confirmation
        document.getElementById('confirmAction').addEventListener('click', () => this.executeConfirmedAction());
    }

    async loadData() {
        try {
            const response = await fetch('/api/backups');
            const data = await response.json();

            if (data.success) {
                this.renderBackups(data.data.backups);
                this.renderSchedules(data.data.schedules);
            } else {
                this.showError('Erreur lors du chargement des données');
            }
        } catch (error) {
            this.showError('Erreur lors du chargement des données');
        }
    }

    renderBackups(backups) {
        this.backupsList.innerHTML = backups.map(backup => `
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${backup.id}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${backup.type === 'full' ? 'Complète' : 'Partielle'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${this.formatSize(backup.size)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${this.formatDate(backup.created_at)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${backup.integrity ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                        ${backup.integrity ? 'OK' : 'Corrompue'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <button class="text-blue-600 hover:text-blue-900 mr-3" onclick="backupManager.showBackupDetails('${backup.id}')">
                        Détails
                    </button>
                    <button class="text-green-600 hover:text-green-900 mr-3" onclick="backupManager.restoreBackup('${backup.id}')">
                        Restaurer
                    </button>
                    <button class="text-red-600 hover:text-red-900" onclick="backupManager.deleteBackup('${backup.id}')">
                        Supprimer
                    </button>
                </td>
            </tr>
        `).join('');
    }

    renderSchedules(schedules) {
        this.schedulesList.innerHTML = schedules.map(schedule => `
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${schedule.name}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${schedule.type === 'full' ? 'Complète' : 'Partielle'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${this.formatFrequency(schedule.frequency)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${this.formatDate(schedule.next_run)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${schedule.enabled ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                        ${schedule.enabled ? 'Active' : 'Inactive'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <button class="text-blue-600 hover:text-blue-900 mr-3" onclick="backupManager.toggleSchedule('${schedule.id}')">
                        ${schedule.enabled ? 'Désactiver' : 'Activer'}
                    </button>
                    <button class="text-red-600 hover:text-red-900" onclick="backupManager.deleteSchedule('${schedule.id}')">
                        Supprimer
                    </button>
                </td>
            </tr>
        `).join('');
    }

    async handleBackupSubmit(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = {
            type: formData.get('type'),
            targets: formData.getAll('targets[]'),
            description: formData.get('description'),
            compression: formData.get('compression') === 'on'
        };

        try {
            const response = await fetch('/api/backups', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                this.hideAllModals();
                this.loadData();
                this.showSuccess('Sauvegarde créée avec succès');
            } else {
                this.showError(result.message);
            }
        } catch (error) {
            this.showError('Erreur lors de la création de la sauvegarde');
        }
    }

    async handleScheduleSubmit(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = {
            name: formData.get('name'),
            type: formData.get('type'),
            targets: formData.getAll('targets[]'),
            frequency: formData.get('frequency'),
            time: formData.get('time'),
            retention: parseInt(formData.get('retention'))
        };

        try {
            const response = await fetch('/api/backups/schedules', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                this.hideAllModals();
                this.loadData();
                this.showSuccess('Planification créée avec succès');
            } else {
                this.showError(result.message);
            }
        } catch (error) {
            this.showError('Erreur lors de la création de la planification');
        }
    }

    async showBackupDetails(id) {
        try {
            const response = await fetch(`/api/backups/${id}`);
            const result = await response.json();

            if (result.success) {
                // Afficher les détails dans un modal
                // TODO: Implémenter l'affichage des détails
            } else {
                this.showError(result.message);
            }
        } catch (error) {
            this.showError('Erreur lors de la récupération des détails');
        }
    }

    async restoreBackup(id) {
        if (await this.confirmAction('Êtes-vous sûr de vouloir restaurer cette sauvegarde ?')) {
            try {
                const response = await fetch(`/api/backups/${id}/restore`, {
                    method: 'POST'
                });

                const result = await response.json();

                if (result.success) {
                    this.showSuccess('Sauvegarde restaurée avec succès');
                } else {
                    this.showError(result.message);
                }
            } catch (error) {
                this.showError('Erreur lors de la restauration de la sauvegarde');
            }
        }
    }

    async deleteBackup(id) {
        if (await this.confirmAction('Êtes-vous sûr de vouloir supprimer cette sauvegarde ?')) {
            try {
                const response = await fetch(`/api/backups/${id}`, {
                    method: 'DELETE'
                });

                const result = await response.json();

                if (result.success) {
                    this.loadData();
                    this.showSuccess('Sauvegarde supprimée avec succès');
                } else {
                    this.showError(result.message);
                }
            } catch (error) {
                this.showError('Erreur lors de la suppression de la sauvegarde');
            }
        }
    }

    async toggleSchedule(id) {
        try {
            const response = await fetch(`/api/backups/schedules/${id}/toggle`, {
                method: 'PATCH'
            });

            const result = await response.json();

            if (result.success) {
                this.loadData();
                this.showSuccess('État de la planification modifié avec succès');
            } else {
                this.showError(result.message);
            }
        } catch (error) {
            this.showError('Erreur lors de la modification de la planification');
        }
    }

    async deleteSchedule(id) {
        if (await this.confirmAction('Êtes-vous sûr de vouloir supprimer cette planification ?')) {
            try {
                const response = await fetch(`/api/backups/schedules/${id}`, {
                    method: 'DELETE'
                });

                const result = await response.json();

                if (result.success) {
                    this.loadData();
                    this.showSuccess('Planification supprimée avec succès');
                } else {
                    this.showError(result.message);
                }
            } catch (error) {
                this.showError('Erreur lors de la suppression de la planification');
            }
        }
    }

    // Méthodes utilitaires
    switchTab(tabName) {
        this.tabButtons.forEach(button => {
            button.classList.toggle('active', button.dataset.tab === tabName);
        });

        this.tabContents.forEach(content => {
            content.classList.toggle('active', content.id === `${tabName}Tab`);
            content.classList.toggle('hidden', content.id !== `${tabName}Tab`);
        });
    }

    showModal(modal) {
        modal.classList.remove('hidden');
    }

    hideAllModals() {
        this.createBackupModal.classList.add('hidden');
        this.createScheduleModal.classList.add('hidden');
        this.confirmModal.classList.add('hidden');
    }

    async confirmAction(message) {
        return new Promise(resolve => {
            document.getElementById('confirmMessage').textContent = message;
            this.showModal(this.confirmModal);

            const handleConfirm = () => {
                this.hideAllModals();
                document.getElementById('confirmAction').removeEventListener('click', handleConfirm);
                resolve(true);
            };

            const handleCancel = () => {
                this.hideAllModals();
                document.querySelector('.cancel-modal').removeEventListener('click', handleCancel);
                resolve(false);
            };

            document.getElementById('confirmAction').addEventListener('click', handleConfirm);
            document.querySelector('.cancel-modal').addEventListener('click', handleCancel);
        });
    }

    formatSize(bytes) {
        const units = ['B', 'KB', 'MB', 'GB', 'TB'];
        let size = bytes;
        let unitIndex = 0;

        while (size >= 1024 && unitIndex < units.length - 1) {
            size /= 1024;
            unitIndex++;
        }

        return `${size.toFixed(2)} ${units[unitIndex]}`;
    }

    formatDate(dateString) {
        return new Date(dateString).toLocaleString('fr-FR');
    }

    formatFrequency(frequency) {
        const frequencies = {
            daily: 'Quotidienne',
            weekly: 'Hebdomadaire',
            monthly: 'Mensuelle',
            custom: 'Personnalisée'
        };
        return frequencies[frequency] || frequency;
    }

    showSuccess(message) {
        // TODO: Implémenter l'affichage des messages de succès
        console.log('Success:', message);
    }

    showError(message) {
        // TODO: Implémenter l'affichage des messages d'erreur
        console.error('Error:', message);
    }
}

// Initialisation
const backupManager = new BackupManager(); 