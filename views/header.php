<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Pixel Hub'; ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">
                <a href="/">
                    <h1>Pixel Hub</h1>
                </a>
            </div>

            <nav class="main-nav">
                <ul>
                    <li><a href="/" <?php echo $current_page === 'home' ? 'class="active"' : ''; ?>>Accueil</a></li>
                    <li><a href="/about" <?php echo $current_page === 'about' ? 'class="active"' : ''; ?>>À propos</a></li>
                    <li><a href="/contact" <?php echo $current_page === 'contact' ? 'class="active"' : ''; ?>>Contact</a></li>
                    <?php if (isset($_SESSION['user'])): ?>
                        <li><a href="/dashboard" <?php echo $current_page === 'dashboard' ? 'class="active"' : ''; ?>>Tableau de bord</a></li>
                        <li><a href="/profile" <?php echo $current_page === 'profile' ? 'class="active"' : ''; ?>>Profil</a></li>
                        <li><a href="/logout">Déconnexion</a></li>
                    <?php else: ?>
                        <li><a href="/login" <?php echo $current_page === 'login' ? 'class="active"' : ''; ?>>Connexion</a></li>
                        <li><a href="/register" <?php echo $current_page === 'register' ? 'class="active"' : ''; ?>>Inscription</a></li>
                    <?php endif; ?>
                </ul>
            </nav>

            <div class="header-actions">
                <button class="mobile-menu-toggle" aria-label="Menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </header>

    <style>
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo a {
            text-decoration: none;
            color: inherit;
        }

        .logo h1 {
            margin: 0;
            font-size: 1.5rem;
        }

        .main-nav ul {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .main-nav li {
            margin: 0 1rem;
        }

        .main-nav a {
            text-decoration: none;
            color: inherit;
            padding: 0.5rem;
            transition: color 0.3s ease;
        }

        .main-nav a:hover,
        .main-nav a.active {
            color: #007bff;
        }

        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
        }

        .mobile-menu-toggle span {
            display: block;
            width: 25px;
            height: 3px;
            background-color: #333;
            margin: 5px 0;
            transition: 0.3s;
        }

        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: block;
            }

            .main-nav {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background-color: white;
                padding: 1rem;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }

            .main-nav.active {
                display: block;
            }

            .main-nav ul {
                flex-direction: column;
            }

            .main-nav li {
                margin: 0.5rem 0;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
            const mainNav = document.querySelector('.main-nav');

            mobileMenuToggle.addEventListener('click', function() {
                mainNav.classList.toggle('active');
            });
        });
    </script>
</body>
</html> 