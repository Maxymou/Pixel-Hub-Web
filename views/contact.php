<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pixel Hub - Contact</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header>
        <h1>Pixel Hub</h1>
        <p>Contactez-nous</p>
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
            <h2>Formulaire de contact</h2>
            <form action="/contact" method="POST">
                <div class="form-group">
                    <label for="name">Nom</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="subject">Sujet</label>
                    <input type="text" id="subject" name="subject" required>
                </div>

                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" rows="5" required></textarea>
                </div>

                <button type="submit">Envoyer</button>
            </form>
        </section>

        <section>
            <h2>Informations de contact</h2>
            <div class="contact-info">
                <p><strong>Email :</strong> contact@pixel-hub.com</p>
                <p><strong>Adresse :</strong> 123 Rue des Pixels, 75000 Paris</p>
                <p><strong>Téléphone :</strong> +33 1 23 45 67 89</p>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Pixel Hub. Tous droits réservés.</p>
    </footer>

    <script src="/js/main.js"></script>
</body>
</html> 