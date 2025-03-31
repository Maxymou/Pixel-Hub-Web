<?php
$page_title = 'Pixel Hub - ' . $project['name'];
$current_page = 'project';
require_once 'views/header.php';
?>

<main>
    <div class="project-container">
        <div class="project-header">
            <div class="project-image">
                <img src="<?php echo $project['image']; ?>" alt="<?php echo $project['name']; ?>">
                <div class="project-actions">
                    <?php if ($is_owner): ?>
                        <a href="/project/<?php echo $project['id']; ?>/edit" class="button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                            Modifier
                        </a>
                        <button class="button button-danger" id="delete-project">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                            Supprimer
                        </button>
                    <?php else: ?>
                        <button class="button <?php echo $is_favorite ? 'button-outline' : ''; ?>" id="favorite-project">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="<?php echo $is_favorite ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                            </svg>
                            <?php echo $is_favorite ? 'Favori' : 'Ajouter aux favoris'; ?>
                        </button>
                        <button class="button" id="share-project">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path>
                                <polyline points="16 6 12 2 8 6"></polyline>
                                <line x1="12" y1="2" x2="12" y2="15"></line>
                            </svg>
                            Partager
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="project-info">
                <div class="project-author">
                    <img src="<?php echo $project['author']['avatar'] ?? '/images/default-avatar.png'; ?>" alt="Avatar de <?php echo $project['author']['name']; ?>">
                    <div class="author-info">
                        <a href="/profile/<?php echo $project['author']['username']; ?>" class="author-name"><?php echo $project['author']['name']; ?></a>
                        <span class="author-username">@<?php echo $project['author']['username']; ?></span>
                    </div>
                </div>
                <h1><?php echo $project['name']; ?></h1>
                <p class="project-description"><?php echo $project['description']; ?></p>
                <div class="project-meta">
                    <div class="meta-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                            <polyline points="21 15 16 10 5 21"></polyline>
                        </svg>
                        <span><?php echo $project['pixel_count']; ?> pixels</span>
                    </div>
                    <div class="meta-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <span>Créé le <?php echo date('d/m/Y', strtotime($project['created_at'])); ?></span>
                    </div>
                    <?php if ($project['category']): ?>
                        <div class="meta-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h5a2 2 0 0 1 2 2z"></path>
                            </svg>
                            <span><?php echo $project['category']; ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($project['tags'])): ?>
                    <div class="project-tags">
                        <?php foreach ($project['tags'] as $tag): ?>
                            <a href="/tag/<?php echo urlencode($tag); ?>" class="tag">#<?php echo $tag; ?></a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="project-content">
            <div class="project-pixels">
                <h2>Pixels du projet</h2>
                <?php if (empty($project['pixels'])): ?>
                    <p class="empty-state">Aucun pixel dans ce projet</p>
                <?php else: ?>
                    <div class="pixels-grid">
                        <?php foreach ($project['pixels'] as $pixel): ?>
                            <div class="pixel-card">
                                <img src="<?php echo $pixel['image']; ?>" alt="<?php echo $pixel['name']; ?>">
                                <div class="pixel-info">
                                    <h3><?php echo $pixel['name']; ?></h3>
                                    <p><?php echo $pixel['description']; ?></p>
                                    <div class="pixel-meta">
                                        <span><?php echo $pixel['width']; ?>x<?php echo $pixel['height']; ?></span>
                                        <span><?php echo $pixel['created_at']; ?></span>
                                    </div>
                                </div>
                                <a href="/pixel/<?php echo $pixel['id']; ?>" class="pixel-link"></a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="project-comments">
                <h2>Commentaires</h2>
                <?php if (empty($project['comments'])): ?>
                    <p class="empty-state">Aucun commentaire</p>
                <?php else: ?>
                    <div class="comments-list">
                        <?php foreach ($project['comments'] as $comment): ?>
                            <div class="comment-item">
                                <div class="comment-author">
                                    <img src="<?php echo $comment['author']['avatar'] ?? '/images/default-avatar.png'; ?>" alt="Avatar de <?php echo $comment['author']['name']; ?>">
                                    <div class="author-info">
                                        <a href="/profile/<?php echo $comment['author']['username']; ?>" class="author-name"><?php echo $comment['author']['name']; ?></a>
                                        <span class="comment-date"><?php echo $comment['created_at']; ?></span>
                                    </div>
                                </div>
                                <div class="comment-content">
                                    <p><?php echo $comment['content']; ?></p>
                                    <?php if ($is_owner || $comment['author']['id'] === $current_user['id']): ?>
                                        <div class="comment-actions">
                                            <button class="button button-text edit-comment" data-comment-id="<?php echo $comment['id']; ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                </svg>
                                                Modifier
                                            </button>
                                            <button class="button button-text button-danger delete-comment" data-comment-id="<?php echo $comment['id']; ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                </svg>
                                                Supprimer
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($is_logged_in): ?>
                    <form class="comment-form" action="/project/<?php echo $project['id']; ?>/comment" method="POST">
                        <div class="form-group">
                            <textarea name="content" placeholder="Ajouter un commentaire..." required></textarea>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="button">Commenter</button>
                        </div>
                    </form>
                <?php else: ?>
                    <p class="login-prompt">Connectez-vous pour laisser un commentaire</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<style>
    .project-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1rem;
    }

    .project-header {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .project-image {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .project-image img {
        width: 100%;
        height: 400px;
        object-fit: cover;
    }

    .project-actions {
        position: absolute;
        top: 1rem;
        right: 1rem;
        display: flex;
        gap: 0.5rem;
    }

    .project-info {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .project-author {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .project-author img {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        object-fit: cover;
    }

    .author-info {
        display: flex;
        flex-direction: column;
    }

    .author-name {
        font-weight: 500;
        color: #333;
        text-decoration: none;
    }

    .author-username {
        font-size: 0.875rem;
        color: #666;
    }

    .project-info h1 {
        margin: 0;
        font-size: 2rem;
    }

    .project-description {
        margin: 0;
        color: #666;
        line-height: 1.6;
    }

    .project-meta {
        display: flex;
        gap: 1.5rem;
        color: #666;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .project-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .tag {
        padding: 0.25rem 0.75rem;
        background-color: #f8f9fa;
        border-radius: 20px;
        color: #007bff;
        text-decoration: none;
        font-size: 0.875rem;
    }

    .tag:hover {
        background-color: #e9ecef;
    }

    .project-content {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
    }

    .project-pixels h2,
    .project-comments h2 {
        margin: 0 0 1.5rem;
        font-size: 1.5rem;
    }

    .pixels-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .pixel-card {
        position: relative;
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        overflow: hidden;
        transition: transform 0.2s ease;
    }

    .pixel-card:hover {
        transform: translateY(-2px);
    }

    .pixel-card img {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }

    .pixel-info {
        padding: 1rem;
    }

    .pixel-info h3 {
        margin: 0 0 0.5rem;
        font-size: 1.25rem;
    }

    .pixel-info p {
        margin: 0 0 0.5rem;
        color: #666;
    }

    .pixel-meta {
        display: flex;
        justify-content: space-between;
        font-size: 0.875rem;
        color: #999;
    }

    .pixel-link {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
    }

    .comments-list {
        display: grid;
        gap: 1.5rem;
    }

    .comment-item {
        display: flex;
        gap: 1rem;
        padding: 1rem;
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .comment-author img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }

    .comment-content {
        flex: 1;
    }

    .comment-content p {
        margin: 0 0 0.5rem;
        color: #333;
    }

    .comment-date {
        font-size: 0.875rem;
        color: #666;
    }

    .comment-actions {
        display: flex;
        gap: 0.5rem;
    }

    .comment-form {
        margin-top: 2rem;
    }

    .form-group textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        resize: vertical;
        min-height: 100px;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 1rem;
    }

    .login-prompt {
        text-align: center;
        padding: 2rem;
        background-color: #f8f9fa;
        border-radius: 8px;
        color: #666;
    }

    .button {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.875rem;
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

    .button-danger {
        background-color: #dc3545;
    }

    .button-danger:hover {
        background-color: #c82333;
    }

    .button-text {
        background: none;
        color: #666;
        padding: 0.25rem 0.5rem;
    }

    .button-text:hover {
        background-color: #f8f9fa;
        color: #333;
    }

    .empty-state {
        text-align: center;
        padding: 2rem;
        color: #666;
    }

    @media (max-width: 1024px) {
        .project-header {
            grid-template-columns: 1fr;
        }

        .project-content {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .project-image img {
            height: 300px;
        }

        .project-actions {
            flex-direction: column;
        }

        .project-meta {
            flex-wrap: wrap;
        }

        .pixels-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    // Gestion de la suppression du projet
    const deleteButton = document.getElementById('delete-project');
    if (deleteButton) {
        deleteButton.addEventListener('click', async () => {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce projet ? Cette action est irréversible.')) {
                try {
                    const response = await fetch('/project/<?php echo $project['id']; ?>/delete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    });

                    if (response.ok) {
                        window.location.href = '/dashboard';
                    }
                } catch (error) {
                    console.error('Erreur lors de la suppression:', error);
                }
            }
        });
    }

    // Gestion des favoris
    const favoriteButton = document.getElementById('favorite-project');
    if (favoriteButton) {
        favoriteButton.addEventListener('click', async () => {
            const isFavorite = favoriteButton.classList.contains('button-outline');
            const action = isFavorite ? 'unfavorite' : 'favorite';
            
            try {
                const response = await fetch('/project/<?php echo $project['id']; ?>/' + action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });

                if (response.ok) {
                    favoriteButton.classList.toggle('button-outline');
                    favoriteButton.innerHTML = isFavorite ? 
                        '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>Ajouter aux favoris' :
                        '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>Favori';
                }
            } catch (error) {
                console.error('Erreur lors de l\'ajout aux favoris:', error);
            }
        });
    }

    // Gestion du partage
    const shareButton = document.getElementById('share-project');
    if (shareButton) {
        shareButton.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(window.location.href);
                alert('Lien copié dans le presse-papiers !');
            } catch (error) {
                console.error('Erreur lors de la copie du lien:', error);
            }
        });
    }

    // Gestion des commentaires
    document.querySelectorAll('.edit-comment').forEach(button => {
        button.addEventListener('click', function() {
            const commentId = this.getAttribute('data-comment-id');
            const commentItem = this.closest('.comment-item');
            const commentContent = commentItem.querySelector('.comment-content p');
            const currentContent = commentContent.textContent;

            const form = document.createElement('form');
            form.className = 'comment-form';
            form.innerHTML = `
                <div class="form-group">
                    <textarea name="content" required>${currentContent}</textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="button button-text cancel-edit">Annuler</button>
                    <button type="submit" class="button">Enregistrer</button>
                </div>
            `;

            commentContent.replaceWith(form);

            form.querySelector('.cancel-edit').addEventListener('click', () => {
                form.replaceWith(commentContent);
            });

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const content = form.querySelector('textarea').value;

                try {
                    const response = await fetch(`/comment/${commentId}/edit`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ content })
                    });

                    if (response.ok) {
                        commentContent.textContent = content;
                        form.replaceWith(commentContent);
                    }
                } catch (error) {
                    console.error('Erreur lors de la modification du commentaire:', error);
                }
            });
        });
    });

    document.querySelectorAll('.delete-comment').forEach(button => {
        button.addEventListener('click', async function() {
            const commentId = this.getAttribute('data-comment-id');
            
            if (confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?')) {
                try {
                    const response = await fetch(`/comment/${commentId}/delete`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    });

                    if (response.ok) {
                        this.closest('.comment-item').remove();
                    }
                } catch (error) {
                    console.error('Erreur lors de la suppression du commentaire:', error);
                }
            }
        });
    });
</script>

<?php require_once 'views/footer.php'; ?> 