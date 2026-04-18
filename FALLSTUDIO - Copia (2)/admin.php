<?php
/**
 * Admin Dashboard - Fall Studio
 * Visualizza iscritti newsletter e messaggi di contatto
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/lib/Database.php';

session_start();

$adminPassword = env('ADMIN_PASSWORD', 'admin123');

// Gestione Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Gestione Login
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === $adminPassword) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $error = 'Password errata';
    }
}

$isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

$subscribers = [];
$messages = [];
$topSearches = [];
$topPages = [];
$topProducts = [];
$engagementStats = [];
$formMetrics = [];
$topClicks = [];
$sessionStats = [];

if ($isLoggedIn) {
    try {
        $db = Database::getInstance();
        $subscribers = $db->getSubscribers();
        $messages = $db->getContactMessages();
        
        try {
            $topSearches = $db->getTopSearches(10);
            $topPages = $db->getTopPages(10);
            $topProducts = $db->getTopProducts(10);
            $engagementStats = $db->getEngagementStats();
            $formMetrics = $db->getFormMetrics();
            $topClicks = $db->getTopClickElements(15);
            $sessionStats = $db->getSessionStats(30);
        } catch (Exception $e) {
            // Tables may not exist yet
        }
    } catch (Exception $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Fall Studio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #000;
            --secondary: #666;
            --accent: #ff3b30;
            --bg: #f8f9fa;
            --card: #fff;
        }
        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg);
            margin: 0;
            padding: 20px;
            color: var(--primary);
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        h1, h2 {
            font-family: 'Montserrat', sans-serif;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .login-box {
            background: var(--card);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            max-width: 400px;
            margin: 100px auto;
            text-align: center;
        }
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 20px 0;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
        }
        button {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: opacity 0.3s;
        }
        button:hover { opacity: 0.8; }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }
        .logout-btn { color: var(--accent); text-decoration: none; font-weight: 600; }

        .card {
            background: var(--card);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        th { background: #fafafa; font-size: 0.9rem; color: var(--secondary); }
        .date { color: var(--secondary); font-size: 0.85rem; }
        .error { color: var(--accent); margin-bottom: 15px; }

        @media (max-width: 768px) {
            .dashboard-header { flex-direction: column; text-align: center; gap: 20px; }
            th:nth-child(3), td:nth-child(3) { display: none; }
        }
    </style>
</head>
<body>

<div class="container">
    <?php if (!$isLoggedIn): ?>
        <div class="login-box">
            <h1>Admin Login</h1>
            <?php if ($error): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
            <form method="POST">
                <input type="password" name="password" placeholder="Inserisci password" required autofocus>
                <button type="submit">Entra</button>
            </form>
            <p style="margin-top: 20px; font-size: 0.8rem; color: #999;">Fall Studio Admin Access Only</p>
        </div>
    <?php else: ?>
        <div class="dashboard-header">
            <h1>Dashboard</h1>
            <a href="?logout=1" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Sloggiati</a>
        </div>

        <div class="card">
            <h2><i class="fas fa-envelope"></i> Iscritti Newsletter (<?php echo count($subscribers); ?>)</h2>
            <?php if (empty($subscribers)): ?>
                <p>Nessun iscritto trovato.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Data Iscrizione</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subscribers as $s): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($s['email']); ?></strong></td>
                            <td class="date"><?php echo date('d/m/Y H:i', strtotime($s['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2><i class="fas fa-comment"></i> Messaggi di Contatto (<?php echo count($messages); ?>)</h2>
            <?php if (empty($messages)): ?>
                <p>Nessun messaggio ricevuto.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Messaggio</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $m): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($m['name']); ?></td>
                            <td><?php echo htmlspecialchars($m['email']); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($m['message'])); ?></td>
                            <td class="date"><?php echo date('d/m/Y H:i', strtotime($m['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2><i class="fas fa-chart-line"></i> Analytics Tools</h2>
            <p style="color: #666; margin-bottom: 20px;">Accedi direttamente ai tuoi dashboard analytics:</p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                <!-- Google Analytics -->
                <div style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
                        <i class="fas fa-chart-bar" style="font-size: 1.8rem; color: #EA4335;"></i>
                        <h3 style="margin: 0; font-size: 1rem;">Google Analytics</h3>
                    </div>
                    <p style="margin: 10px 0; font-size: 0.85rem; color: #999;">GTM-MNXKJNG5</p>
                    <p style="margin: 10px 0; font-size: 0.85rem;">Visualizza traffico, dispositivi, sorgenti, conversioni e eventi personalizzati.</p>
                    <a href="https://tagmanager.google.com" target="_blank" style="display: inline-block; background: #EA4335; color: white; padding: 10px 16px; border-radius: 4px; text-decoration: none; font-size: 0.9rem; margin-top: 10px; transition: opacity 0.3s;">
                        <i class="fas fa-external-link-alt"></i> Apri Dashboard
                    </a>
                </div>

                <!-- Microsoft Clarity -->
                <div style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
                        <i class="fas fa-eye" style="font-size: 1.8rem; color: #0078D4;"></i>
                        <h3 style="margin: 0; font-size: 1rem;">Microsoft Clarity</h3>
                    </div>
                    <p style="margin: 10px 0; font-size: 0.85rem; color: #999;">viy89qi2lp</p>
                    <p style="margin: 10px 0; font-size: 0.85rem;">Heatmaps in tempo reale, registrazioni sessioni, analisi funnel.</p>
                    <a href="https://clarity.microsoft.com" target="_blank" style="display: inline-block; background: #0078D4; color: white; padding: 10px 16px; border-radius: 4px; text-decoration: none; font-size: 0.9rem; margin-top: 10px; transition: opacity 0.3s;">
                        <i class="fas fa-external-link-alt"></i> Apri Dashboard
                    </a>
                </div>

                <!-- Hotjar -->
                <div style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
                        <i class="fas fa-play-circle" style="font-size: 1.8rem; color: #FF5A23;"></i>
                        <h3 style="margin: 0; font-size: 1rem;">Hotjar</h3>
                    </div>
                    <p style="margin: 10px 0; font-size: 0.85rem; color: #999;">d64823176e617</p>
                    <p style="margin: 10px 0; font-size: 0.85rem;">Session playback, heatmaps, polls, feedback, conversions.</p>
                    <a href="https://www.hotjar.com" target="_blank" style="display: inline-block; background: #FF5A23; color: white; padding: 10px 16px; border-radius: 4px; text-decoration: none; font-size: 0.9rem; margin-top: 10px; transition: opacity 0.3s;">
                        <i class="fas fa-external-link-alt"></i> Apri Dashboard
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <h2><i class="fas fa-mouse"></i> Behavior Events (Ultimi 30 giorni)</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 15px; margin-bottom: 30px;">
                <?php
                // Calcola statistiche dai sessionStats
                $deviceMobile = 0;
                $deviceDesktop = 0;
                $menuClicks = 0;
                $searchCount = 0;
                $productViews = 0;
                $addToCart = 0;
                
                foreach ($sessionStats as $stat) {
                    $deviceMobile += $stat['mobile_count'] ?? 0;
                    $deviceDesktop += $stat['desktop_count'] ?? 0;
                }
                
                $searchCount = count($topSearches);
                $productViews = array_sum(array_column($topProducts, 'view_count'));
                $addToCart = array_sum(array_column($topProducts, 'cart_count'));
                
                $menuClicks = array_sum(array_filter(array_column($topClicks, 'click_count'), function($item) {
                    return strpos($item, 'menu') !== false;
                }));
                ?>
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px; border-radius: 8px; text-align: center;">
                    <p style="margin: 0; font-size: 0.8rem; opacity: 0.9;">Mobile</p>
                    <p style="margin: 5px 0 0 0; font-size: 1.6rem; font-weight: 700;"><?php echo number_format($deviceMobile); ?></p>
                </div>
                <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 15px; border-radius: 8px; text-align: center;">
                    <p style="margin: 0; font-size: 0.8rem; opacity: 0.9;">Desktop</p>
                    <p style="margin: 5px 0 0 0; font-size: 1.6rem; font-weight: 700;"><?php echo number_format($deviceDesktop); ?></p>
                </div>
                <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 15px; border-radius: 8px; text-align: center;">
                    <p style="margin: 0; font-size: 0.8rem; opacity: 0.9;">Ricerche</p>
                    <p style="margin: 5px 0 0 0; font-size: 1.6rem; font-weight: 700;"><?php echo number_format($searchCount); ?></p>
                </div>
                <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; padding: 15px; border-radius: 8px; text-align: center;">
                    <p style="margin: 0; font-size: 0.8rem; opacity: 0.9;">Visualizzazioni Prodotti</p>
                    <p style="margin: 5px 0 0 0; font-size: 1.6rem; font-weight: 700;"><?php echo number_format($productViews); ?></p>
                </div>
                <div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; padding: 15px; border-radius: 8px; text-align: center;">
                    <p style="margin: 0; font-size: 0.8rem; opacity: 0.9;">Aggiunte Carrello</p>
                    <p style="margin: 5px 0 0 0; font-size: 1.6rem; font-weight: 700;"><?php echo number_format($addToCart); ?></p>
                </div>
                <div style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: white; padding: 15px; border-radius: 8px; text-align: center;">
                    <p style="margin: 0; font-size: 0.8rem; opacity: 0.9;">Conversion Rate</p>
                    <p style="margin: 5px 0 0 0; font-size: 1.6rem; font-weight: 700;"><?php echo $productViews > 0 ? round(($addToCart / $productViews) * 100, 1) : 0; ?>%</p>
                </div>
            </div>
        </div>
            
            <h3>KPI Principali</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 30px;">
                <?php
                $totalSessions = array_sum(array_column($sessionStats, 'total_sessions'));
                $totalEvents = array_sum(array_column($sessionStats, 'total_events'));
                $totalPages = count(array_unique(array_column($topPages, 'page_path')));
                ?>
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px;">
                    <p style="margin: 0; font-size: 0.9rem; opacity: 0.9;">Sessioni Totali</p>
                    <p style="margin: 5px 0 0 0; font-size: 2rem; font-weight: 700;"><?php echo number_format($totalSessions); ?></p>
                </div>
                <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; border-radius: 8px;">
                    <p style="margin: 0; font-size: 0.9rem; opacity: 0.9;">Eventi Totali</p>
                    <p style="margin: 5px 0 0 0; font-size: 2rem; font-weight: 700;"><?php echo number_format($totalEvents); ?></p>
                </div>
                <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 20px; border-radius: 8px;">
                    <p style="margin: 0; font-size: 0.9rem; opacity: 0.9;">Pagine Visitate</p>
                    <p style="margin: 5px 0 0 0; font-size: 2rem; font-weight: 700;"><?php echo $totalPages; ?></p>
                </div>
                <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; padding: 20px; border-radius: 8px;">
                    <p style="margin: 0; font-size: 0.9rem; opacity: 0.9;">Ricerche Uniche</p>
                    <p style="margin: 5px 0 0 0; font-size: 2rem; font-weight: 700;"><?php echo count($topSearches); ?></p>
                </div>
            </div>

            <h3>Top Search Queries</h3>
            <?php if (empty($topSearches)): ?>
                <p style="color: #999;">Nessuna ricerca registrata.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Query Ricerca</th>
                            <th>Numero Ricerche</th>
                            <th>Risultati Medi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topSearches as $search): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($search['search_query']); ?></strong></td>
                            <td><?php echo $search['search_count']; ?></td>
                            <td><?php echo round($search['avg_results']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <h3 style="margin-top: 30px;">Top Pages</h3>
            <?php if (empty($topPages)): ?>
                <p style="color: #999;">Nessun dato disponibile.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Pagina</th>
                            <th>Visualizzazioni</th>
                            <th>Tempo Medio (s)</th>
                            <th>Scroll Medio %</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topPages as $page): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($page['page_title'] ?: $page['page_path']); ?></strong></td>
                            <td><?php echo number_format($page['view_count']); ?></td>
                            <td><?php echo round($page['avg_time_spent']); ?></td>
                            <td><?php echo round($page['avg_scroll']); ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <h3 style="margin-top: 30px;">Top Products</h3>
            <?php if (empty($topProducts)): ?>
                <p style="color: #999;">Nessun dato disponibile.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Prodotto</th>
                            <th>Visualizzazioni</th>
                            <th>Aggiunte Carrello</th>
                            <th>Conversion %</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topProducts as $product): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($product['product_name']); ?></strong></td>
                            <td><?php echo number_format($product['view_count']); ?></td>
                            <td><?php echo $product['cart_count']; ?></td>
                            <td><?php echo round(($product['cart_count'] / $product['view_count']) * 100, 1); ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <h3 style="margin-top: 30px;">User Engagement (Scroll Depth)</h3>
            <?php if (empty($engagementStats)): ?>
                <p style="color: #999;">Nessun dato disponibile.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Range Scroll</th>
                            <th>Numero Visitatori</th>
                            <th>Percentuale</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalVisitors = array_sum(array_column($engagementStats, 'visitor_count'));
                        foreach ($engagementStats as $stat): 
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($stat['scroll_range']); ?></strong></td>
                            <td><?php echo $stat['visitor_count']; ?></td>
                            <td>
                                <div style="background: #eee; border-radius: 3px; overflow: hidden;">
                                    <div style="background: #667eea; width: <?php echo round(($stat['visitor_count'] / $totalVisitors) * 100); ?>%; padding: 5px; color: white; font-size: 0.85rem;">
                                        <?php echo round(($stat['visitor_count'] / $totalVisitors) * 100, 1); ?>%
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <h3 style="margin-top: 30px;">Form Performance</h3>
            <?php if (empty($formMetrics)): ?>
                <p style="color: #999;">Nessun dato disponibile.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Form</th>
                            <th>Stato</th>
                            <th>Numero</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($formMetrics as $metric): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($metric['form_type']); ?></strong></td>
                            <td><?php echo htmlspecialchars($metric['status']); ?></td>
                            <td><?php echo $metric['count']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <h3 style="margin-top: 30px;">Most Clicked Elements</h3>
            <?php if (empty($topClicks)): ?>
                <p style="color: #999;">Nessun dato disponibile.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Elemento</th>
                            <th>Tipo</th>
                            <th>Pagina</th>
                            <th>Click</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topClicks as $click): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars(substr($click['element_text'], 0, 40)); ?></strong></td>
                            <td><span style="background: #f0f0f0; padding: 3px 8px; border-radius: 3px; font-size: 0.85rem;"><?php echo htmlspecialchars($click['element_type']); ?></span></td>
                            <td><?php echo htmlspecialchars($click['page_path']); ?></td>
                            <td><strong><?php echo $click['click_count']; ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
