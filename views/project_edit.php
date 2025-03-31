<?php
$page_title = 'Pixel Hub - Modifier le projet';
$current_page = 'project_edit';
require_once 'views/header.php';
?>

<main>
    <div class="edit-container">
        <div class="edit-header">
            <h1>Modifier le projet</h1>
            <a href="/project/<?php echo $project['id']; ?>" class="button button-text">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 12H5M12 19l-7-7 7-7"></path>
                </svg>
                Retour au projet
            </a>
        </div>

        <form action="/project/<?php echo $project['id']; ?>/edit" method="POST" enctype="multipart/form-data" class="edit-form">
            <div class="form-grid">
                <div class="form-section">
                    <div class="form-group">
                        <label for="name">Nom du projet</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($project['name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($project['description']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="image">Image de couverture</label>
                        <div class="image-upload">
                            <div class="image-preview">
                                <img src="<?php echo htmlspecialchars($project['image_url']); ?>" alt="Aperçu du projet" id="image-preview">
                            </div>
                            <input type="file" id="image" name="image" accept="image/png,image/gif,image/jpeg" onchange="previewImage(this)">
                            <label for="image" class="button button-outline">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 19V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"></path>
                                </svg>
                                Changer l'image
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="visibility">Visibilité</label>
                        <select id="visibility" name="visibility" required>
                            <option value="public" <?php echo $project['visibility'] === 'public' ? 'selected' : ''; ?>>Public</option>
                            <option value="private" <?php echo $project['visibility'] === 'private' ? 'selected' : ''; ?>>Privé</option>
                        </select>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-group">
                        <label for="category">Catégorie</label>
                        <select id="category" name="category" required>
                            <option value="">Sélectionner une catégorie</option>
                            <option value="game" <?php echo $project['category'] === 'game' ? 'selected' : ''; ?>>Jeu vidéo</option>
                            <option value="animation" <?php echo $project['category'] === 'animation' ? 'selected' : ''; ?>>Animation</option>
                            <option value="character" <?php echo $project['category'] === 'character' ? 'selected' : ''; ?>>Personnage</option>
                            <option value="environment" <?php echo $project['category'] === 'environment' ? 'selected' : ''; ?>>Environnement</option>
                            <option value="ui" <?php echo $project['category'] === 'ui' ? 'selected' : ''; ?>>Interface utilisateur</option>
                            <option value="other" <?php echo $project['category'] === 'other' ? 'selected' : ''; ?>>Autre</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="tags">Tags</label>
                        <input type="text" id="tags" name="tags" value="<?php echo htmlspecialchars($project['tags']); ?>" placeholder="Séparés par des virgules">
                        <small>Exemple: rpg, fantasy, medieval</small>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="button button-danger" onclick="confirmDelete()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                    Supprimer le projet
                </button>
                <button type="submit" class="button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</main>

<style>
    .edit-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }

    .edit-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .edit-header h1 {
        margin: 0;
        font-size: 2rem;
    }

    .edit-form {
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

    .button-danger {
        background-color: #dc3545;
    }

    .button-danger:hover {
        background-color: #c82333;
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

    // Confirmation de suppression
    function confirmDelete() {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce projet ? Cette action est irréversible.')) {
            window.location.href = '/project/<?php echo $project['id']; ?>/delete';
        }
    }
</script>

<?php require_once 'views/footer.php'; ?> 