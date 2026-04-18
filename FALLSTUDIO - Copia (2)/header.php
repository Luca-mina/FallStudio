<?php include_once 'includes/functions.php'; ?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Fall Studio | Streetwear & Abbigliamento'; ?></title>
    <link rel="icon" href="icon.jpeg" type="image/jpeg">
    <!-- Safari mask icon (SVG, supports tint color) -->
    <link rel="mask-icon" href="icon.svg" color="#000000">
    <!-- Apple touch icon for iOS home screen -->
    <link rel="apple-touch-icon" href="apple-touch-icon.png">
    <!-- Theme color for mobile browsers -->
    <meta name="theme-color" content="#000000">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Inter:wght@300;400&family=Tangerine:wght@400;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    
    <!-- Preload prioritario per logo + intro banner -->
    <link rel="preload" as="image" href="logo.png" fetchpriority="high">
    <link rel="preload" as="image" href="prima_desktop.png" fetchpriority="high">
    <link rel="preload" as="image" href="prima_telefono.png" fetchpriority="high" media="(max-width: 480px)">

    <!-- Soppressione errori script esterni (AlterVista) -->
    <script>
        // Sopprimi errori da script di terze parti iniettati (come s.js di AlterVista)
        window.addEventListener('error', function (e) {
            // Filtra errori da script non nostri
            if (e.filename && (
                e.filename.includes('/s.js') ||
                e.filename.includes('altervista') ||
                e.filename.includes('tb.altervista')
            )) {
                e.stopImmediatePropagation();
                e.preventDefault();
                return true; // Previeni il logging in console
            }
        }, true);
    </script>
    
    <?php if (isset($extraHead))
    echo $extraHead; ?>

</head>

<body>
    <div id="loading-screen" role="status" aria-live="polite" aria-label="Schermata di caricamento">
        <img src="loadingimage.png" alt="Caricamento" id="loading-image">
    </div>

    <!-- Header -->
    <header class="site-header">
        <div class="container header-container">
            <button class="menu-toggle" aria-label="Apri menu" aria-expanded="false" aria-controls="main-navigation">
                <i class="fas fa-bars" aria-hidden="true"></i>
            </button>

            <a href="index.php" class="logo">
                <img src="logo.png" alt="Fall Studio" class="site-logo">
            </a>

            <nav id="main-navigation" class="main-nav" aria-hidden="true" role="dialog" aria-label="Menu principale">
                <?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
                <ul>
                    <li><a href="index.php" <?php if ($current_page == 'index.php' && !isset($_GET['nuovi-arrivi']))
    echo 'class="active"'; ?>>Home</a></li>
                    <li><a href="index.php#nuovi-arrivi">Nuovi Arrivi</a></li>
                    <li><a href="collezione.php" <?php if ($current_page == 'collezione.php')
    echo 'class="active"'; ?>>Collezione</a></li>
                    <li><a href="accessori.php" <?php if ($current_page == 'accessori.php')
    echo 'class="active"'; ?>>Accessori</a></li>
                </ul>
            </nav>

            <div class="header-actions">
                <button class="icon-btn search-btn" aria-label="Cerca prodotti">
                    <i class="fas fa-search"></i>
                </button>
            </div>

            <!-- Search Bar -->
            <div class="search-bar">
                <input type="text" placeholder="Cerca prodotti...">
                <button class="search-close"><i class="fas fa-times"></i></button>
            </div>
        </div>
    </header>
