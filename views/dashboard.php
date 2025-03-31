<?php
$page_title = 'Pixel Hub - Tableau de bord';
$current_page = 'dashboard';
require_once 'views/header.php';
?>

<main>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Tableau de bord</h1>
            <div class="dashboard-actions">
                <a href="/project/create" class="button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Nouveau projet
                </a>
                <a href="/pixel/create" class="button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Nouveau pixel
                </a>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <div class="card-header">
                    <h2>Projets récents</h2>
                    <a href="/projects" class="button button-text">Voir tout</a>
                </div>
                <div class="card-content">
                    <?php if (empty($recent_projects)): ?>
                        <div class="empty-state">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 19V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"></path>
                            </svg>
                            <p>Aucun projet récent</p>
                        </div>
                    <?php else: ?>
                        <div class="project-list">
                            <?php foreach ($recent_projects as $project): ?>
                                <a href="/project/<?php echo $project['id']; ?>" class="project-item">
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
            </div>

            <div class="dashboard-card">
                <div class="card-header">
                    <h2>Pixels récents</h2>
                    <a href="/pixels" class="button button-text">Voir tout</a>
                </div>
                <div class="card-content">
                    <?php if (empty($recent_pixels)): ?>
                        <div class="empty-state">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                            </svg>
                            <p>Aucun pixel récent</p>
                        </div>
                    <?php else: ?>
                        <div class="pixel-list">
                            <?php foreach ($recent_pixels as $pixel): ?>
                                <a href="/pixel/<?php echo $pixel['id']; ?>" class="pixel-item">
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
            </div>

            <div class="dashboard-card">
                <div class="card-header">
                    <h2>Statistiques</h2>
                </div>
                <div class="card-content">
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 19V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"></path>
                                </svg>
                            </div>
                            <div class="stat-info">
                                <span class="stat-value"><?php echo $stats['total_projects']; ?></span>
                                <span class="stat-label">Projets</span>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="7" height="7"></rect>
                                    <rect x="14" y="3" width="7" height="7"></rect>
                                    <rect x="14" y="14" width="7" height="7"></rect>
                                    <rect x="3" y="14" width="7" height="7"></rect>
                                </svg>
                            </div>
                            <div class="stat-info">
                                <span class="stat-value"><?php echo $stats['total_pixels']; ?></span>
                                <span class="stat-label">Pixels</span>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                </svg>
                            </div>
                            <div class="stat-info">
                                <span class="stat-value"><?php echo $stats['total_favorites']; ?></span>
                                <span class="stat-label">Favoris</span>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </div>
                            <div class="stat-info">
                                <span class="stat-value"><?php echo $stats['total_views']; ?></span>
                                <span class="stat-label">Vues</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dashboard-card">
                <div class="card-header">
                    <h2>Activité récente</h2>
                </div>
                <div class="card-content">
                    <?php if (empty($recent_activity)): ?>
                        <div class="empty-state">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            <p>Aucune activité récente</p>
                        </div>
                    <?php else: ?>
                        <div class="activity-list">
                            <?php foreach ($recent_activity as $activity): ?>
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <?php if ($activity['type'] === 'project'): ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21 19V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"></path>
                                            </svg>
                                        <?php elseif ($activity['type'] === 'pixel'): ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="3" y="3" width="7" height="7"></rect>
                                                <rect x="14" y="3" width="7" height="7"></rect>
                                                <rect x="14" y="14" width="7" height="7"></rect>
                                                <rect x="3" y="14" width="7" height="7"></rect>
                                            </svg>
                                        <?php endif; ?>
                                    </div>
                                    <div class="activity-content">
                                        <p><?php echo htmlspecialchars($activity['message']); ?></p>
                                        <span class="activity-date">
                                            <?php echo date('d/m/Y H:i', strtotime($activity['created_at'])); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
    .dashboard-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }

    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .dashboard-header h1 {
        margin: 0;
        font-size: 2rem;
    }

    .dashboard-actions {
        display: flex;
        gap: 1rem;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 2rem;
    }

    .dashboard-card {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
        border-bottom: 1px solid #dee2e6;
    }

    .card-header h2 {
        margin: 0;
        font-size: 1.25rem;
    }

    .card-content {
        padding: 1.5rem;
    }

    .project-list,
    .pixel-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .project-item,
    .pixel-item {
        display: flex;
        gap: 1rem;
        padding: 1rem;
        border-radius: 4px;
        text-decoration: none;
        color: inherit;
        transition: background-color 0.3s ease;
    }

    .project-item:hover,
    .pixel-item:hover {
        background-color: #f8f9fa;
    }

    .project-image,
    .pixel-image {
        width: 80px;
        height: 80px;
        border-radius: 4px;
        overflow: hidden;
        background-color: #f8f9fa;
    }

    .project-image img,
    .pixel-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .project-info,
    .pixel-info {
        flex: 1;
    }

    .project-info h3,
    .pixel-info h3 {
        margin: 0 0 0.5rem;
        font-size: 1rem;
    }

    .project-info p,
    .pixel-info p {
        margin: 0 0 0.5rem;
        font-size: 0.875rem;
        color: #666;
    }

    .project-meta,
    .pixel-meta {
        display: flex;
        gap: 1rem;
        font-size: 0.75rem;
        color: #666;
    }

    .project-meta span,
    .pixel-meta span {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background-color: #f8f9fa;
        border-radius: 4px;
    }

    .stat-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #e9ecef;
        border-radius: 50%;
        color: #495057;
    }

    .stat-info {
        display: flex;
        flex-direction: column;
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

    .activity-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .activity-item {
        display: flex;
        gap: 1rem;
        padding: 1rem;
        background-color: #f8f9fa;
        border-radius: 4px;
    }

    .activity-icon {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #e9ecef;
        border-radius: 50%;
        color: #495057;
    }

    .activity-content {
        flex: 1;
    }

    .activity-content p {
        margin: 0 0 0.25rem;
        font-size: 0.875rem;
        color: #212529;
    }

    .activity-date {
        font-size: 0.75rem;
        color: #666;
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
        .dashboard-grid {
            grid-template-columns: 1fr;
        }

        .dashboard-header {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }

        .dashboard-actions {
            width: 100%;
            justify-content: center;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php require_once 'views/footer.php'; ?> 