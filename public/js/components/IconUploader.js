class IconUploader {
    constructor(appId, onSuccess, onError) {
        this.appId = appId;
        this.onSuccess = onSuccess;
        this.onError = onError;
        this.createUploader();
    }

    createUploader() {
        const container = document.createElement('div');
        container.className = 'icon-uploader';
        container.innerHTML = `
            <div class="relative">
                <input type="file" 
                       accept="image/*" 
                       class="hidden" 
                       id="icon-upload-${this.appId}">
                <label for="icon-upload-${this.appId}" 
                       class="cursor-pointer inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-upload mr-2"></i>
                    Changer l'icône
                </label>
            </div>
        `;

        const input = container.querySelector(`#icon-upload-${this.appId}`);
        input.addEventListener('change', this.handleFileSelect.bind(this));

        return container;
    }

    async handleFileSelect(event) {
        const file = event.target.files[0];
        if (!file) return;

        try {
            const iconUrl = await iconManager.importCustomIcon(this.appId, file);
            if (this.onSuccess) {
                this.onSuccess(iconUrl);
            }
        } catch (error) {
            if (this.onError) {
                this.onError(error.message);
            }
        }
    }
}

// Exemple d'utilisation :
/*
const uploader = new IconUploader(
    'app-123',
    (iconUrl) => {
        // Mise à jour de l'interface après succès
        console.log('Icône importée avec succès:', iconUrl);
    },
    (error) => {
        // Gestion des erreurs
        console.error('Erreur lors de l\'importation:', error);
    }
);

// Ajouter l'uploader à l'interface
document.querySelector('.app-settings').appendChild(uploader.createUploader());
*/ 