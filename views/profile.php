<?php
$page_title = 'Pixel Hub - Profil';
$current_page = 'profile';
require_once 'views/header.php';
?>

<main>
    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-cover">
                <img src="<?php echo htmlspecialchars($user['cover_url']); ?>" alt="Couverture du profil">
            </div>
            <div class="profile-info">
                <div class="profile-avatar">
                    <img src="<?php echo htmlspecialchars($user['avatar_url']); ?>" alt="<?php echo htmlspecialchars($user['name']); ?>">
                    <?php if ($is_own_profile): ?>
                        <label for="avatar" class="avatar-upload">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 19V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"></path>
                            </svg>
                        </label>
                        <input type="file" id="avatar" name="avatar" accept="image/png,image/gif,image/jpeg" onchange="previewAvatar(this)">
                    <?php endif; ?>
                </div>
                <div class="profile-details">
                    <h1><?php echo htmlspecialchars($user['name']); ?></h1>
                    <p class="username">@<?php echo htmlspecialchars($user['username']); ?></p>
                    <p class="bio"><?php echo nl2br(htmlspecialchars($user['bio'])); ?></p>
                    <div class="profile-stats">
                        <div class="stat">
                            <span class="stat-value"><?php echo $stats['total_projects']; ?></span>
                            <span class="stat-label">Projets</span>
                        </div>
                        <div class="stat">
                            <span class="stat-value"><?php echo $stats['total_pixels']; ?></span>
                            <span class="stat-label">Pixels</span>
                        </div>
                        <div class="stat">
                            <span class="stat-value"><?php echo $stats['total_followers']; ?></span>
                            <span class="stat-label">Abonnés</span>
                        </div>
                        <div class="stat">
                            <span class="stat-value"><?php echo $stats['total_following']; ?></span>
                            <span class="stat-label">Abonnements</span>
                        </div>
                    </div>
                    <?php if (!$is_own_profile): ?>
                        <button class="button <?php echo $is_following ? 'button-outline' : ''; ?>" onclick="toggleFollow(<?php echo $user['id']; ?>)">
                            <?php echo $is_following ? 'Se désabonner' : 'S\'abonner'; ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="profile-content">
            <div class="profile-tabs">
                <button class="tab-button active" data-tab="projects">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 19V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"></path>
                    </svg>
                    Projets
                </button>
                <button class="tab-button" data-tab="pixels">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    Pixels
                </button>
                <button class="tab-button" data-tab="favorites">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                    </svg>
                    Favoris
                </button>
                <?php if ($is_own_profile): ?>
                    <button class="tab-button" data-tab="settings">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                        </svg>
                        Paramètres
                    </button>
                <?php endif; ?>
            </div>

            <div class="tab-content">
                <div class="tab-pane active" id="projects">
                    <?php if (empty($projects)): ?>
                        <div class="empty-state">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 19V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"></path>
                            </svg>
                            <p>Aucun projet</p>
                        </div>
                    <?php else: ?>
                        <div class="project-grid">
                            <?php foreach ($projects as $project): ?>
                                <a href="/project/<?php echo $project['id']; ?>" class="project-card">
                                    <div class="project-image">
                                        <img src="<?php echo htmlspecialchars($project['image_url']); ?>" alt="<?php echo htmlspecialchars($project['name']); ?>">
                                    </div>
                                    <div class="project-info">
                                        <h3><?php echo htmlspecialchars($project['name']); ?></h3>
                                        <p><?php echo htmlspecialchars($project['description']); ?></p>
                                        <div class="project-meta">
                                            <span class="pixel-count">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="3" y="3" width="7" height="7"></rect>
                                                    <rect x="14" y="3" width="7" height="7"></rect>
                                                    <rect x="14" y="14" width="7" height="7"></rect>
                                                    <rect x="3" y="14" width="7" height="7"></rect>
                                                </svg>
                                                <?php echo $project['pixel_count']; ?> pixels
                                            </span>
                                            <span class="date">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                                </svg>
                                                <?php echo date('d/m/Y', strtotime($project['created_at'])); ?>
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="tab-pane" id="pixels">
                    <?php if (empty($pixels)): ?>
                        <div class="empty-state">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                            </svg>
                            <p>Aucun pixel</p>
                        </div>
                    <?php else: ?>
                        <div class="pixel-grid">
                            <?php foreach ($pixels as $pixel): ?>
                                <a href="/pixel/<?php echo $pixel['id']; ?>" class="pixel-card">
                                    <div class="pixel-image">
                                        <img src="<?php echo htmlspecialchars($pixel['image_url']); ?>" alt="<?php echo htmlspecialchars($pixel['name']); ?>">
                                    </div>
                                    <div class="pixel-info">
                                        <h3><?php echo htmlspecialchars($pixel['name']); ?></h3>
                                        <p><?php echo htmlspecialchars($pixel['description']); ?></p>
                                        <div class="pixel-meta">
                                            <span class="dimensions">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                                    <line x1="3" y1="9" x2="21" y2="9"></line>
                                                    <line x1="9" y1="21" x2="9" y2="9"></line>
                                                </svg>
                                                <?php echo $pixel['width']; ?>x<?php echo $pixel['height']; ?>
                                            </span>
                                            <span class="date">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                                </svg>
                                                <?php echo date('d/m/Y', strtotime($pixel['created_at'])); ?>
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="tab-pane" id="favorites">
                    <?php if (empty($favorites)): ?>
                        <div class="empty-state">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                            </svg>
                            <p>Aucun favori</p>
                        </div>
                    <?php else: ?>
                        <div class="favorite-grid">
                            <?php foreach ($favorites as $favorite): ?>
                                <a href="/<?php echo $favorite['type']; ?>/<?php echo $favorite['id']; ?>" class="favorite-card">
                                    <div class="favorite-image">
                                        <img src="<?php echo htmlspecialchars($favorite['image_url']); ?>" alt="<?php echo htmlspecialchars($favorite['name']); ?>">
                                    </div>
                                    <div class="favorite-info">
                                        <h3><?php echo htmlspecialchars($favorite['name']); ?></h3>
                                        <p><?php echo htmlspecialchars($favorite['description']); ?></p>
                                        <div class="favorite-meta">
                                            <span class="type">
                                                <?php if ($favorite['type'] === 'project'): ?>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M21 19V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"></path>
                                                    </svg>
                                                    Projet
                                                <?php else: ?>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <rect x="3" y="3" width="7" height="7"></rect>
                                                        <rect x="14" y="3" width="7" height="7"></rect>
                                                        <rect x="14" y="14" width="7" height="7"></rect>
                                                        <rect x="3" y="14" width="7" height="7"></rect>
                                                    </svg>
                                                    Pixel
                                                <?php endif; ?>
                                            </span>
                                            <span class="date">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                                </svg>
                                                <?php echo date('d/m/Y', strtotime($favorite['created_at'])); ?>
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($is_own_profile): ?>
                    <div class="tab-pane" id="settings">
                        <form action="/profile/update" method="POST" enctype="multipart/form-data" class="settings-form">
                            <div class="form-group">
                                <label for="name">Nom</label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="username">Nom d'utilisateur</label>
                                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="bio">Bio</label>
                                <textarea id="bio" name="bio" rows="4"><?php echo htmlspecialchars($user['bio']); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="password">Nouveau mot de passe</label>
                                <input type="password" id="password" name="password">
                                <small>Laissez vide pour garder le mot de passe actuel</small>
                            </div>

                            <div class="form-group">
                                <label for="cover">Image de couverture</label>
                                <div class="image-upload">
                                    <div class="image-preview">
                                        <img src="<?php echo htmlspecialchars($user['cover_url']); ?>" alt="Couverture du profil" id="cover-preview">
                                    </div>
                                    <input type="file" id="cover" name="cover" accept="image/png,image/gif,image/jpeg" onchange="previewCover(this)">
                                    <label for="cover" class="button button-outline">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 19V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"></path>
                                        </svg>
                                        Changer la couverture
                                    </label>
                                </div>
                            </div>

                            <div class="form-actions">
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
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<style>
    .profile-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }

    .profile-header {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .profile-cover {
        width: 100%;
        height: 200px;
        overflow: hidden;
        background-color: #f8f9fa;
    }

    .profile-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-info {
        display: flex;
        gap: 2rem;
        padding: 2rem;
        position: relative;
    }

    .profile-avatar {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        overflow: hidden;
        border: 4px solid white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        position: relative;
        margin-top: -75px;
    }

    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .avatar-upload {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background-color: rgba(0,0,0,0.5);
        color: white;
        padding: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .profile-avatar:hover .avatar-upload {
        opacity: 1;
    }

    .profile-avatar input[type="file"] {
        display: none;
    }

    .profile-details {
        flex: 1;
    }

    .profile-details h1 {
        margin: 0 0 0.5rem;
        font-size: 2rem;
    }

    .username {
        color: #666;
        margin: 0 0 1rem;
    }

    .bio {
        margin: 0 0 1.5rem;
        white-space: pre-line;
    }

    .profile-stats {
        display: flex;
        gap: 2rem;
        margin-bottom: 1.5rem;
    }

    .stat {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .stat-value {
        font-size: 1.25rem;
        font-weight: 500;
        color: #212529;
    }

    .stat-label {
        font-size: 0.875rem;
        color: #666;
    }

    .profile-content {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .profile-tabs {
        display: flex;
        border-bottom: 1px solid #dee2e6;
    }

    .tab-button {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 1rem 1.5rem;
        background: none;
        border: none;
        color: #666;
        cursor: pointer;
        font-size: 1rem;
        transition: color 0.3s ease;
    }

    .tab-button:hover {
        color: #212529;
    }

    .tab-button.active {
        color: #007bff;
        border-bottom: 2px solid #007bff;
    }

    .tab-content {
        padding: 2rem;
    }

    .tab-pane {
        display: none;
    }

    .tab-pane.active {
        display: block;
    }

    .project-grid,
    .pixel-grid,
    .favorite-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .project-card,
    .pixel-card,
    .favorite-card {
        background-color: #f8f9fa;
        border-radius: 8px;
        overflow: hidden;
        text-decoration: none;
        color: inherit;
        transition: transform 0.3s ease;
    }

    .project-card:hover,
    .pixel-card:hover,
    .favorite-card:hover {
        transform: translateY(-4px);
    }

    .project-image,
    .pixel-image,
    .favorite-image {
        width: 100%;
        height: 200px;
        overflow: hidden;
        background-color: #e9ecef;
    }

    .project-image img,
    .pixel-image img,
    .favorite-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .project-info,
    .pixel-info,
    .favorite-info {
        padding: 1rem;
    }

    .project-info h3,
    .pixel-info h3,
    .favorite-info h3 {
        margin: 0 0 0.5rem;
        font-size: 1.125rem;
    }

    .project-info p,
    .pixel-info p,
    .favorite-info p {
        margin: 0 0 0.5rem;
        font-size: 0.875rem;
        color: #666;
    }

    .project-meta,
    .pixel-meta,
    .favorite-meta {
        display: flex;
        gap: 1rem;
        font-size: 0.75rem;
        color: #666;
    }

    .project-meta span,
    .pixel-meta span,
    .favorite-meta span {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .settings-form {
        max-width: 600px;
        margin: 0 auto;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #333;
    }

    .form-group input[type="text"],
    .form-group input[type="email"],
    .form-group input[type="password"],
    .form-group textarea {
        width: 100%;
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
        display: block;
        margin-top: 0.25rem;
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
        height: 200px;
        border-radius: 4px;
        overflow: hidden;
        background-color: #f8f9fa;
    }

    .image-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
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

    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        text-align: center;
        color: #666;
    }

    .empty-state svg {
        margin-bottom: 1rem;
        color: #adb5bd;
    }

    @media (max-width: 768px) {
        .profile-info {
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 1rem;
        }

        .profile-avatar {
            margin-top: -50px;
        }

        .profile-stats {
            justify-content: center;
        }

        .profile-tabs {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .tab-button {
            white-space: nowrap;
        }

        .project-grid,
        .pixel-grid,
        .favorite-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    // Gestion des onglets
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabPanes = document.querySelectorAll('.tab-pane');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tabId = button.dataset.tab;

            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanes.forEach(pane => pane.classList.remove('active'));

            button.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        });
    });

    // Prévisualisation de l'avatar
    function previewAvatar(input) {
        const preview = document.querySelector('.profile-avatar img');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Prévisualisation de la couverture
    function previewCover(input) {
        const preview = document.getElementById('cover-preview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Gestion de l'abonnement
    function toggleFollow(userId) {
        fetch(`/user/${userId}/follow`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const button = document.querySelector('.button');
                if (data.following) {
                    button.classList.add('button-outline');
                    button.textContent = 'Se désabonner';
                } else {
                    button.classList.remove('button-outline');
                    button.textContent = 'S\'abonner';
                }
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
        });
    }
</script>

<?php require_once 'views/footer.php'; ?> 