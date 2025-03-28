class IconManager {
    constructor() {
        this.customIcons = new Map();
        this.defaultIcon = '/images/default-app-icon.svg';
        this.iconTypes = {
            game: '/images/app-icons/game.svg',
            utility: '/images/app-icons/utility.svg',
            media: '/images/app-icons/media.svg',
            network: '/images/app-icons/network.svg',
            system: '/images/app-icons/system.svg'
        };
    }

    /**
     * Importe une nouvelle icône personnalisée
     * @param {string} appId - L'identifiant de l'application
     * @param {File} file - Le fichier d'icône à importer
     * @returns {Promise<string>} L'URL de l'icône importée
     */
    async importCustomIcon(appId, file) {
        try {
            // Vérifier le type de fichier
            if (!file.type.startsWith('image/')) {
                throw new Error('Le fichier doit être une image');
            }

            // Vérifier la taille (max 1MB)
            if (file.size > 1024 * 1024) {
                throw new Error('L\'icône ne doit pas dépasser 1MB');
            }

            // Créer un FormData pour l'upload
            const formData = new FormData();
            formData.append('icon', file);
            formData.append('app_id', appId);

            // Envoyer l'icône au serveur
            const response = await fetch('/api/icons/upload', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error('Erreur lors de l\'upload de l\'icône');
            }

            const data = await response.json();
            const iconUrl = data.icon_url;

            // Mettre en cache l'icône
            this.customIcons.set(appId, iconUrl);

            return iconUrl;
        } catch (error) {
            console.error('Erreur lors de l\'importation de l\'icône:', error);
            throw error;
        }
    }

    /**
     * Récupère l'URL de l'icône pour une application
     * @param {Object} app - L'objet application
     * @returns {string} L'URL de l'icône
     */
    getIconUrl(app) {
        // Vérifier si une icône personnalisée existe
        if (this.customIcons.has(app.id)) {
            return this.customIcons.get(app.id);
        }

        // Vérifier le type d'application
        if (app.type && this.iconTypes[app.type.toLowerCase()]) {
            return this.iconTypes[app.type.toLowerCase()];
        }

        // Retourner l'icône par défaut
        return this.defaultIcon;
    }

    /**
     * Supprime une icône personnalisée
     * @param {string} appId - L'identifiant de l'application
     * @returns {Promise<void>}
     */
    async deleteCustomIcon(appId) {
        try {
            const response = await fetch(`/api/icons/${appId}`, {
                method: 'DELETE'
            });

            if (!response.ok) {
                throw new Error('Erreur lors de la suppression de l\'icône');
            }

            // Supprimer de la cache
            this.customIcons.delete(appId);
        } catch (error) {
            console.error('Erreur lors de la suppression de l\'icône:', error);
            throw error;
        }
    }

    /**
     * Charge les icônes personnalisées depuis le serveur
     * @returns {Promise<void>}
     */
    async loadCustomIcons() {
        try {
            const response = await fetch('/api/icons');
            if (!response.ok) {
                throw new Error('Erreur lors du chargement des icônes');
            }

            const data = await response.json();
            this.customIcons = new Map(Object.entries(data));
        } catch (error) {
            console.error('Erreur lors du chargement des icônes:', error);
        }
    }
}

// Créer une instance globale
const iconManager = new IconManager(); 