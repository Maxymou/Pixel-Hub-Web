<?php
$page_title = 'Pixel Hub - Créer un projet';
$current_page = 'project_create';
require_once 'views/header.php';
?>

<main>
    <div class="create-container">
        <div class="create-header">
            <h1>Créer un nouveau projet</h1>
            <a href="/dashboard" class="button button-text">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 12H5M12 19l-7-7 7-7"></path>
                </svg>
                Retour au tableau de bord
            </a>
        </div>

        <form action="/project/create" method="POST" enctype="multipart/form-data" class="create-form">
            <div class="form-grid">
                <div class="form-section">
                    <div class="form-group">
                        <label for="name">Nom du projet</label>
                        <input type="text" id="name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="image">Image de couverture</label>
                        <div class="image-upload">
                            <div class="image-preview">
                                <img src="/images/placeholder.png" alt="Aperçu du projet" id="image-preview">
                            </div>
                            <input type="file" id="image" name="image" accept="image/png,image/gif,image/jpeg" required onchange="previewImage(this)">
                            <label for="image" class="button button-outline">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 19V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"></path>
                                </svg>
                                Choisir une image
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="visibility">Visibilité</label>
                        <select id="visibility" name="visibility" required>
                            <option value="public">Public</option>
                            <option value="private">Privé</option>
                        </select>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-group">
                        <label for="category">Catégorie</label>
                        <select id="category" name="category" required>
                            <option value="">Sélectionner une catégorie</option>
                            <option value="game">Jeu vidéo</option>
                            <option value="animation">Animation</option>
                            <option value="character">Personnage</option>
                            <option value="environment">Environnement</option>
                            <option value="ui">Interface utilisateur</option>
                            <option value="other">Autre</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="tags">Tags</label>
                        <input type="text" id="tags" name="tags" placeholder="Séparés par des virgules">
                        <small>Exemple: rpg, fantasy, medieval</small>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    Créer le projet
                </button>
            </div>
        </form>
    </div>
</main>

<style>
    .create-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }

    .create-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .create-header h1 {
        margin: 0;
        font-size: 2rem;
    }

    .create-form {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        padding: 2rem;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .form-section {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .form-group label {
        font-weight: 500;
        color: #333;
    }

    .form-group input[type="text"],
    .form-group select,
    .form-group textarea {
        padding: 0.75rem;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        font-size: 1rem;
    }

    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }

    .form-group small {
        color: #666;
        font-size: 0.875rem;
    }

    .image-upload {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .image-preview {
        width: 100%;
        height: 300px;
        border-radius: 4px;
        overflow: hidden;
        background-color: #f8f9fa;
    }

    .image-preview img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .image-upload input[type="file"] {
        display: none;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        padding-top: 2rem;
        border-top: 1px solid #dee2e6;
    }

    .button {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 1rem;
        text-decoration: none;
        transition: background-color 0.3s ease;
    }

    .button:hover {
        background-color: #0056b3;
    }

    .button-outline {
        background-color: transparent;
        border: 1px solid #007bff;
        color: #007bff;
    }

    .button-outline:hover {
        background-color: #007bff;
        color: white;
    }

    .button-text {
        background: none;
        color: #666;
        padding: 0.5rem 1rem;
    }

    .button-text:hover {
        background-color: #f8f9fa;
        color: #333;
    }

    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column;
        }

        .button {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<script>
    // Prévisualisation de l'image
    function previewImage(input) {
        const preview = document.getElementById('image-preview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

<?php require_once 'views/footer.php'; ?> 