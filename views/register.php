<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pixel Hub - Inscription</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header>
        <h1>Pixel Hub</h1>
        <p>Inscription</p>
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
            <h2>Inscription</h2>
            <form action="/register" method="POST">
                <div class="form-group">
                    <label for="name">Nom</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirmer le mot de passe</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="terms" required>
                        J'accepte les <a href="/terms">conditions d'utilisation</a>
                    </label>
                </div>

                <button type="submit">S'inscrire</button>
            </form>

            <p class="form-footer">
                Déjà un compte ? <a href="/login">Connectez-vous</a>
            </p>
        </section>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Pixel Hub. Tous droits réservés.</p>
    </footer>

    <script src="/js/main.js"></script>
</body>
</html> 