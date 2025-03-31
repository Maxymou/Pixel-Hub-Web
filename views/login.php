<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pixel Hub - Connexion</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header>
        <h1>Pixel Hub</h1>
        <p>Connexion</p>
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
            <h2>Connexion</h2>
            <form action="/login" method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="remember"> Se souvenir de moi
                    </label>
                </div>

                <button type="submit">Se connecter</button>
            </form>

            <p class="form-footer">
                Pas encore de compte ? <a href="/register">Inscrivez-vous</a>
            </p>
        </section>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Pixel Hub. Tous droits réservés.</p>
    </footer>

    <script src="/js/main.js"></script>
</body>
</html> 