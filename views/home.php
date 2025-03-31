<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pixel Hub - Accueil</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header>
        <h1>Pixel Hub</h1>
        <p>Bienvenue sur votre plateforme de gestion de pixels</p>
    </header>

    <nav>
        <ul>
            <li><a href="/">Accueil</a></li>
            <li><a href="/about">À propos</a></li>
            <li><a href="/contact">Contact</a></li>
            <li><a href="/login">Connexion</a></li>
            <li><a href="/register">Inscription</a></li>
        </ul>
    </nav>

    <main>
        <section>
            <h2>Derniers projets</h2>
            <div class="projects-grid">
                <!-- Les projets seront affichés ici dynamiquement -->
            </div>
        </section>

        <section>
            <h2>Statistiques</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Projets actifs</h3>
                    <p>0</p>
                </div>
                <div class="stat-card">
                    <h3>Pixels créés</h3>
                    <p>0</p>
                </div>
                <div class="stat-card">
                    <h3>Utilisateurs</h3>
                    <p>0</p>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Pixel Hub. Tous droits réservés.</p>
    </footer>

    <script src="/js/main.js"></script>
</body>
</html> 