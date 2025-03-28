class UpdateManager {
    constructor() {
        this.initializeElements();
        this.initializeEventListeners();
        this.loadData();
    }

    initializeElements() {
        // Boutons
        this.checkUpdatesBtn = document.getElementById('checkUpdatesBtn');

        // Sections
        this.availableUpdates = document.getElementById('availableUpdates');
        this.availableUpdatesList = document.getElementById('availableUpdatesList');
        this.downloadedUpdatesList = document.getElementById('downloadedUpdatesList');

        // Modals
        this.downloadModal = document.getElementById('downloadModal');
        this.scheduleModal = document.getElementById('scheduleModal');
        this.confirmModal = document.getElementById('confirmModal');

        // Éléments de téléchargement
        this.downloadProgress = document.getElementById('downloadProgress');
        this.downloadBar = document.getElementById('downloadBar');
        this.downloadStatus = document.getElementById('downloadStatus');

        // Formulaires
        this.scheduleForm = document.getElementById('scheduleForm');
        this.scheduleVersion = document.getElementById('scheduleVersion');
    }

    initializeEventListeners() {
        // Vérification des mises à jour
        this.checkUpdatesBtn.addEventListener('click', () => this.checkForUpdates());

        // Gestion des modals
        document.querySelectorAll('.cancel-modal').forEach(button => {
            button.addEventListener('click', () => this.hideAllModals());
        });

        // Gestion du formulaire de planification
        this.scheduleForm.addEventListener('submit', (e) => this.handleScheduleSubmit(e));

        // Gestion de la confirmation
        document.getElementById('confirmAction').addEventListener('click', () => this.executeConfirmedAction());
    }

    async loadData() {
        try {
            const response = await fetch('/api/updates');
            const result = await response.json();

            if (result.success) {
                this.renderDownloadedUpdates(result.data);
            } else {
                this.showError('Erreur lors du chargement des mises à jour');
            }
        } catch (error) {
            this.showError('Erreur lors du chargement des mises à jour');
        }
    }

    async checkForUpdates() {
        try {
            this.checkUpdatesBtn.disabled = true;
            this.checkUpdatesBtn.textContent = 'Vérification...';

            const response = await fetch('/api/updates/check');
            const result = await response.json();

            if (result.success) {
                if (result.data.available) {
                    this.availableUpdates.classList.remove('hidden');
                    this.renderAvailableUpdates(result.data);
                    this.showSuccess('Nouvelles mises à jour disponibles');
                } else {
                    this.showInfo('Votre système est à jour');
                }
            } else {
                this.showError(result.message);
            }
        } catch (error) {
            this.showError('Erreur lors de la vérification des mises à jour');
        } finally {
            this.checkUpdatesBtn.disabled = false;
            this.checkUpdatesBtn.textContent = 'Vérifier les Mises à Jour';
        }
    }

    async downloadUpdate(version) {
        try {
            this.showModal(this.downloadModal);
            this.downloadStatus.textContent = 'Démarrage du téléchargement...';
            this.downloadProgress.textContent = '0%';
            this.downloadBar.style.width = '0%';

            const response = await fetch('/api/updates/download', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ version })
            });

            const result = await response.json();

            if (result.success) {
                this.downloadStatus.textContent = 'Téléchargement terminé';
                this.downloadProgress.textContent = '100%';
                this.downloadBar.style.width = '100%';
                this.hideModal(this.downloadModal);
                this.loadData();
                this.showSuccess('Mise à jour téléchargée avec succès');
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            this.showError('Erreur lors du téléchargement de la mise à jour');
            this.hideModal(this.downloadModal);
        }
    }

    async installUpdate(version) {
        if (await this.confirmAction('Êtes-vous sûr de vouloir installer cette mise à jour ?')) {
            try {
                const response = await fetch('/api/updates/install', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ version })
                });

                const result = await response.json();

                if (result.success) {
                    this.loadData();
                    this.showSuccess('Mise à jour installée avec succès');
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                this.showError('Erreur lors de l\'installation de la mise à jour');
            }
        }
    }

    async rollbackUpdate(version) {
        if (await this.confirmAction('Êtes-vous sûr de vouloir annuler cette mise à jour ?')) {
            try {
                const response = await fetch('/api/updates/rollback', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ version })
                });

                const result = await response.json();

                if (result.success) {
                    this.loadData();
                    this.showSuccess('Mise à jour annulée avec succès');
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                this.showError('Erreur lors de l\'annulation de la mise à jour');
            }
        }
    }

    async scheduleUpdate(version) {
        this.scheduleVersion.value = version;
        this.showModal(this.scheduleModal);
    }

    async handleScheduleSubmit(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = {
            version: formData.get('version'),
            scheduled_time: formData.get('scheduled_time')
        };

        try {
            const response = await fetch('/api/updates/schedule', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                this.hideModal(this.scheduleModal);
                this.loadData();
                this.showSuccess('Installation de la mise à jour planifiée avec succès');
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            this.showError('Erreur lors de la planification de la mise à jour');
        }
    }

    renderAvailableUpdates(data) {
        this.availableUpdatesList.innerHTML = `
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${data.latest_version}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${this.formatDate(data.info.date)}</td>
                <td class="px-6 py-4 text-sm text-gray-900">${data.info.description}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <button class="text-blue-600 hover:text-blue-900" onclick="updateManager.downloadUpdate('${data.latest_version}')">
                        Télécharger
                    </button>
                </td>
            </tr>
        `;
    }

    renderDownloadedUpdates(updates) {
        this.downloadedUpdatesList.innerHTML = Object.entries(updates).map(([version, update]) => `
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${version}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${this.getStatusClass(update.status)}">
                        ${this.getStatusText(update.status)}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${this.formatDate(update.downloaded_at)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${this.getActionButtons(version, update.status)}
                </td>
            </tr>
        `).join('');
    }

    getStatusClass(status) {
        const classes = {
            downloaded: 'bg-yellow-100 text-yellow-800',
            installed: 'bg-green-100 text-green-800',
            scheduled: 'bg-blue-100 text-blue-800',
            rolled_back: 'bg-red-100 text-red-800'
        };
        return classes[status] || 'bg-gray-100 text-gray-800';
    }

    getStatusText(status) {
        const texts = {
            downloaded: 'Téléchargée',
            installed: 'Installée',
            scheduled: 'Planifiée',
            rolled_back: 'Annulée'
        };
        return texts[status] || status;
    }

    getActionButtons(version, status) {
        switch (status) {
            case 'downloaded':
                return `
                    <button class="text-green-600 hover:text-green-900 mr-3" onclick="updateManager.installUpdate('${version}')">
                        Installer
                    </button>
                    <button class="text-blue-600 hover:text-blue-900" onclick="updateManager.scheduleUpdate('${version}')">
                        Planifier
                    </button>
                `;
            case 'installed':
                return `
                    <button class="text-red-600 hover:text-red-900" onclick="updateManager.rollbackUpdate('${version}')">
                        Annuler
                    </button>
                `;
            case 'scheduled':
                return `
                    <button class="text-red-600 hover:text-red-900" onclick="updateManager.rollbackUpdate('${version}')">
                        Annuler
                    </button>
                `;
            default:
                return '';
        }
    }

    async confirmAction(message) {
        return new Promise(resolve => {
            document.getElementById('confirmMessage').textContent = message;
            this.showModal(this.confirmModal);

            const handleConfirm = () => {
                this.hideModal(this.confirmModal);
                document.getElementById('confirmAction').removeEventListener('click', handleConfirm);
                resolve(true);
            };

            const handleCancel = () => {
                this.hideModal(this.confirmModal);
                document.querySelector('.cancel-modal').removeEventListener('click', handleCancel);
                resolve(false);
            };

            document.getElementById('confirmAction').addEventListener('click', handleConfirm);
            document.querySelector('.cancel-modal').addEventListener('click', handleCancel);
        });
    }

    showModal(modal) {
        modal.classList.remove('hidden');
    }

    hideModal(modal) {
        modal.classList.add('hidden');
    }

    hideAllModals() {
        this.downloadModal.classList.add('hidden');
        this.scheduleModal.classList.add('hidden');
        this.confirmModal.classList.add('hidden');
    }

    formatDate(dateString) {
        return new Date(dateString).toLocaleString('fr-FR');
    }

    showSuccess(message) {
        // TODO: Implémenter l'affichage des messages de succès
        console.log('Success:', message);
    }

    showError(message) {
        // TODO: Implémenter l'affichage des messages d'erreur
        console.error('Error:', message);
    }

    showInfo(message) {
        // TODO: Implémenter l'affichage des messages d'information
        console.log('Info:', message);
    }
}

// Initialisation
const updateManager = new UpdateManager(); 