<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pixel Hub - Erreur <?php echo $error_code; ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header>
        <h1>Pixel Hub</h1>
        <p>Erreur <?php echo $error_code; ?></p>
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
        <section class="error-section">
            <h2>Oups ! Une erreur est survenue</h2>
            
            <div class="error-content">
                <?php if ($error_code === 404): ?>
                    <h3>Page non trouvée</h3>
                    <p>La page que vous recherchez n'existe pas ou a été déplacée.</p>
                    <p>Vérifiez l'URL ou retournez à la page d'accueil.</p>
                <?php elseif ($error_code === 403): ?>
                    <h3>Accès refusé</h3>
                    <p>Vous n'avez pas les permissions nécessaires pour accéder à cette page.</p>
                    <p>Connectez-vous ou vérifiez vos droits d'accès.</p>
                <?php elseif ($error_code === 500): ?>
                    <h3>Erreur serveur</h3>
                    <p>Une erreur est survenue sur notre serveur.</p>
                    <p>Notre équipe technique a été notifiée et travaille à résoudre le problème.</p>
                <?php else: ?>
                    <h3>Erreur <?php echo $error_code; ?></h3>
                    <p><?php echo $error_message ?? 'Une erreur inattendue est survenue.'; ?></p>
                <?php endif; ?>

                <div class="error-actions">
                    <a href="/" class="button">Retour à l'accueil</a>
                    <a href="/contact" class="button">Nous contacter</a>
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