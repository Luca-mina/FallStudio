<?php
/**
 * Database handler per SQLite
 */

class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        $dbPath = __DIR__ . '/../newsletter.db';

        try {
            $this->pdo = new PDO('sqlite:' . $dbPath);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->createTables();
        }
        catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function createTables()
    {
        // Tabella subscribers
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS subscribers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT NOT NULL UNIQUE,
            created_at TEXT NOT NULL
        )");

        // Tabella contact_messages
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS contact_messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            message TEXT NOT NULL,
            created_at TEXT NOT NULL,
            ip_address TEXT
        )");

        // Tabella user_analytics (sessioni base)
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS user_analytics (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            session_id TEXT NOT NULL UNIQUE,
            device_type TEXT,
            screen_size TEXT,
            language TEXT,
            timezone TEXT,
            created_at TEXT NOT NULL,
            last_activity TEXT,
            duration INTEGER DEFAULT 0
        )");

        // Tabella page_views (pagine visitate)
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS page_views (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            session_id TEXT NOT NULL,
            page_path TEXT NOT NULL,
            page_title TEXT,
            entry_time TEXT NOT NULL,
            exit_time TEXT,
            time_spent INTEGER DEFAULT 0,
            scroll_depth REAL DEFAULT 0,
            created_at TEXT NOT NULL
        )");

        // Tabella user_searches (ricerche effettuate)
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS user_searches (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            session_id TEXT NOT NULL,
            search_query TEXT NOT NULL,
            results_count INTEGER DEFAULT 0,
            filters_applied TEXT,
            created_at TEXT NOT NULL
        )");

        // Tabella product_interactions (interazioni con prodotti)
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS product_interactions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            session_id TEXT NOT NULL,
            product_id TEXT,
            product_name TEXT,
            interaction_type TEXT,
            time_viewed INTEGER DEFAULT 0,
            click_position TEXT,
            page_path TEXT,
            created_at TEXT NOT NULL
        )");

        // Tabella click_events (click tracking)
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS click_events (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            session_id TEXT NOT NULL,
            element_type TEXT,
            element_text TEXT,
            element_id TEXT,
            page_path TEXT,
            x_position INTEGER,
            y_position INTEGER,
            created_at TEXT NOT NULL
        )");

        // Tabella form_interactions (interazioni con form)
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS form_interactions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            session_id TEXT NOT NULL,
            form_type TEXT,
            form_field TEXT,
            status TEXT,
            created_at TEXT NOT NULL
        )");

        // Crea indici per performance
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_session_analytics ON user_analytics(session_id)");
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_session_pages ON page_views(session_id)");
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_session_searches ON user_searches(session_id)");
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_session_products ON product_interactions(session_id)");
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_session_clicks ON click_events(session_id)");
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_session_forms ON form_interactions(session_id)");
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_created_analytics ON user_analytics(created_at)");
    }

    public function getPDO()
    {
        return $this->pdo;
    }

    /**
     * Inserisce email newsletter (ignora duplicati)
     */
    public function insertSubscriber($email)
    {
        $stmt = $this->pdo->prepare(
            "INSERT OR IGNORE INTO subscribers (email, created_at) VALUES (?, ?)"
        );
        $stmt->execute([$email, date('c')]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Controlla se email è già iscritta
     */
    public function isSubscribed($email)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM subscribers WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Inserisce messaggio di contatto
     */
    public function insertContactMessage($name, $email, $message, $ipAddress = null)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO contact_messages (name, email, message, created_at, ip_address) 
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$name, $email, $message, date('c'), $ipAddress]);
        return $this->pdo->lastInsertId();
    }

    /**
     * Ottiene lista subscribers
     */
    public function getSubscribers()
    {
        $stmt = $this->pdo->query(
            "SELECT id, email, created_at FROM subscribers ORDER BY created_at DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ottiene lista messaggi
     */
    public function getContactMessages()
    {
        $stmt = $this->pdo->query(
            "SELECT id, name, email, message, created_at FROM contact_messages ORDER BY created_at DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ===== NUOVI METODI PER TRACKING USER BEHAVIOR =====

    /**
     * Crea o aggiorna una sessione utente
     */
    public function trackSession($sessionId, $deviceType, $screenSize, $language, $timezone)
    {
        $stmt = $this->pdo->prepare("
            INSERT OR REPLACE INTO user_analytics 
            (session_id, device_type, screen_size, language, timezone, created_at, last_activity)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $sessionId,
            $deviceType,
            $screenSize,
            $language,
            $timezone,
            date('c'),
            date('c')
        ]);
    }

    /**
     * Traccia una pagina visitata
     */
    public function trackPageView($sessionId, $pagePath, $pageTitle, $entryTime)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO page_views 
            (session_id, page_path, page_title, entry_time, created_at)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$sessionId, $pagePath, $pageTitle, $entryTime, date('c')]);
    }

    /**
     * Aggiorna il tempo speso su una pagina
     */
    public function updatePageViewDuration($sessionId, $pagePath, $timeSpent, $scrollDepth)
    {
        $stmt = $this->pdo->prepare("
            UPDATE page_views 
            SET time_spent = ?, scroll_depth = ?, exit_time = ?
            WHERE session_id = ? AND page_path = ?
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([$timeSpent, $scrollDepth, date('c'), $sessionId, $pagePath]);
    }

    /**
     * Traccia una ricerca di prodotto
     */
    public function trackSearch($sessionId, $searchQuery, $resultsCount, $filters = null)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO user_searches 
            (session_id, search_query, results_count, filters_applied, created_at)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $sessionId,
            $searchQuery,
            $resultsCount,
            $filters ? json_encode($filters) : null,
            date('c')
        ]);
    }

    /**
     * Traccia un'interazione con un prodotto
     */
    public function trackProductInteraction($sessionId, $productId, $productName, $interactionType, $pagePath)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO product_interactions 
            (session_id, product_id, product_name, interaction_type, page_path, created_at)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $sessionId,
            $productId,
            $productName,
            $interactionType,
            $pagePath,
            date('c')
        ]);
    }

    /**
     * Traccia un click in pagina
     */
    public function trackClickEvent($sessionId, $elementType, $elementText, $elementId, $pagePath, $x, $y)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO click_events 
            (session_id, element_type, element_text, element_id, page_path, x_position, y_position, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $sessionId,
            $elementType,
            $elementText,
            $elementId,
            $pagePath,
            $x,
            $y,
            date('c')
        ]);
    }

    /**
     * Traccia un'interazione con un form
     */
    public function trackFormInteraction($sessionId, $formType, $formField, $status)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO form_interactions 
            (session_id, form_type, form_field, status, created_at)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $sessionId,
            $formType,
            $formField,
            $status,
            date('c')
        ]);
    }

    /**
     * Ottiene statistiche di sessione
     */
    public function getSessionStats($limit = 30)
    {
        $stmt = $this->pdo->query("
            SELECT 
                COUNT(DISTINCT session_id) as total_sessions,
                COUNT(*) as total_events,
                AVG(duration) as avg_duration,
                DATE(created_at) as date
            FROM user_analytics
            GROUP BY DATE(created_at)
            ORDER BY date DESC
            LIMIT ?
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ottiene le ricerche più frequenti
     */
    public function getTopSearches($limit = 10)
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                search_query,
                COUNT(*) as search_count,
                AVG(results_count) as avg_results
            FROM user_searches
            GROUP BY search_query
            ORDER BY search_count DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ottiene le pagine più visitate
     */
    public function getTopPages($limit = 10)
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                page_path,
                page_title,
                COUNT(*) as view_count,
                AVG(time_spent) as avg_time_spent,
                AVG(scroll_depth) as avg_scroll
            FROM page_views
            GROUP BY page_path
            ORDER BY view_count DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ottiene i prodotti più visualizzati
     */
    public function getTopProducts($limit = 10)
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                product_id,
                product_name,
                COUNT(*) as view_count,
                SUM(CASE WHEN interaction_type = 'add_to_cart' THEN 1 ELSE 0 END) as cart_count
            FROM product_interactions
            GROUP BY product_id
            ORDER BY view_count DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ottiene statistiche di engagement
     */
    public function getEngagementStats()
    {
        $stmt = $this->pdo->query("
            SELECT 
                CASE 
                    WHEN scroll_depth < 25 THEN '0-25%'
                    WHEN scroll_depth < 50 THEN '25-50%'
                    WHEN scroll_depth < 75 THEN '50-75%'
                    ELSE '75-100%'
                END as scroll_range,
                COUNT(*) as visitor_count
            FROM page_views
            GROUP BY scroll_range
            ORDER BY scroll_range
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ottiene metriche form
     */
    public function getFormMetrics($formType = null)
    {
        if ($formType) {
            $stmt = $this->pdo->prepare("
                SELECT 
                    form_type,
                    status,
                    COUNT(*) as count
                FROM form_interactions
                WHERE form_type = ?
                GROUP BY form_type, status
            ");
            $stmt->execute([$formType]);
        } else {
            $stmt = $this->pdo->query("
                SELECT 
                    form_type,
                    status,
                    COUNT(*) as count
                FROM form_interactions
                GROUP BY form_type, status
            ");
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ottiene statistiche clic
     */
    public function getTopClickElements($limit = 15)
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                element_type,
                element_text,
                page_path,
                COUNT(*) as click_count
            FROM click_events
            GROUP BY element_type, element_text, page_path
            ORDER BY click_count DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ottiene dettagli di una sessione
     */
    public function getSessionDetails($sessionId)
    {
        // Info sessione
        $stmt = $this->pdo->prepare("SELECT * FROM user_analytics WHERE session_id = ?");
        $stmt->execute([$sessionId]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$session) {
            return null;
        }

        // Pagine visitate
        $stmt = $this->pdo->prepare("
            SELECT * FROM page_views 
            WHERE session_id = ? 
            ORDER BY created_at ASC
        ");
        $stmt->execute([$sessionId]);
        $pages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Interazioni prodotti
        $stmt = $this->pdo->prepare("
            SELECT * FROM product_interactions 
            WHERE session_id = ? 
            ORDER BY created_at ASC
        ");
        $stmt->execute([$sessionId]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Click
        $stmt = $this->pdo->prepare("
            SELECT * FROM click_events 
            WHERE session_id = ? 
            ORDER BY created_at ASC
        ");
        $stmt->execute([$sessionId]);
        $clicks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Ricerche
        $stmt = $this->pdo->prepare("
            SELECT * FROM user_searches 
            WHERE session_id = ? 
            ORDER BY created_at ASC
        ");
        $stmt->execute([$sessionId]);
        $searches = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'session' => $session,
            'pages' => $pages,
            'products' => $products,
            'clicks' => $clicks,
            'searches' => $searches
        ];
    }
}
