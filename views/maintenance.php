<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pixel Hub - Maintenance</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header>
        <h1>Pixel Hub</h1>
        <p>Maintenance en cours</p>
    </header>

    <main>
        <section class="maintenance-section">
            <div class="maintenance-content">
                <h2>Site en maintenance</h2>
                
                <div class="maintenance-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
                    </svg>
                </div>

                <p>Nous effectuons actuellement des travaux de maintenance sur notre site pour améliorer votre expérience.</p>
                
                <div class="maintenance-details">
                    <h3>Informations</h3>
                    <ul>
                        <li>Durée estimée : <?php echo $maintenance_duration ?? '1-2 heures'; ?></li>
                        <li>Raison : <?php echo $maintenance_reason ?? 'Amélioration des performances'; ?></li>
                        <li>Heure de début : <?php echo $maintenance_start ?? date('H:i'); ?></li>
                    </ul>
                </div>

                <div class="maintenance-actions">
                    <p>Nous vous remercions de votre patience.</p>
                    <p>Vous pouvez nous suivre sur nos réseaux sociaux pour être informé de la fin de la maintenance :</p>
                    <div class="social-links">
                        <a href="https://twitter.com/pixelhub" target="_blank" rel="noopener">Twitter</a>
                        <a href="https://facebook.com/pixelhub" target="_blank" rel="noopener">Facebook</a>
                        <a href="https://instagram.com/pixelhub" target="_blank" rel="noopener">Instagram</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Pixel Hub. Tous droits réservés.</p>
    </footer>

    <style>
        .maintenance-section {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            text-align: center;
        }

        .maintenance-content {
            max-width: 600px;
            margin: 0 auto;
        }

        .maintenance-icon {
            margin: 2rem 0;
        }

        .maintenance-details {
            margin: 2rem 0;
            text-align: left;
        }

        .maintenance-details ul {
            list-style: none;
            padding: 0;
        }

        .maintenance-details li {
            margin: 0.5rem 0;
        }

        .social-links {
            margin-top: 1rem;
        }

        .social-links a {
            display: inline-block;
            margin: 0 0.5rem;
            padding: 0.5rem 1rem;
            background-color: #f0f0f0;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
        }

        .social-links a:hover {
            background-color: #e0e0e0;
        }
    </style>
</body>
</html> 