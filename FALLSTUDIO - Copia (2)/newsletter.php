<?php
// Newsletter subscription handler - saves to database
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

$log_file = __DIR__ . '/newsletter_errors.log';

function log_error($msg) {
    global $log_file;
    @file_put_contents($log_file, date('Y-m-d H:i:s') . " - $msg\n", FILE_APPEND);
}

try {
    // Get email from POST
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    
    if (!$data || !isset($data['email'])) {
        http_response_code(400);
        die(json_encode(['error' => 'Email mancante']));
    }
    
    $email = trim(strtolower($data['email']));
    log_error("Received email: $email");
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        die(json_encode(['error' => 'Email non valida']));
    }
    
    // Rate limiting
    $ip = $_SERVER['REMOTE_ADDR'];
    $rate_file = __DIR__ . '/rate_' . md5($ip) . '.txt';
    
    if (file_exists($rate_file)) {
        $data_rl = @json_decode(file_get_contents($rate_file), true);
        if ($data_rl && (time() - $data_rl['time'] < 60) && $data_rl['count'] >= 5) {
            http_response_code(429);
            die(json_encode(['error' => 'Troppi tentativi']));
        }
        $count = (time() - $data_rl['time'] < 60) ? $data_rl['count'] + 1 : 1;
    } else {
        $count = 1;
    }
    @file_put_contents($rate_file, json_encode(['time' => time(), 'count' => $count]));
    
    // Try to connect to database
    $mysqli = null;
    $db_configs = [
        ['localhost', 'my_fallstudio', 'fallstudio', ''],
        ['localhost', 'my_fallstudio', 'fallstudio', 'fallstudio'],
        ['127.0.0.1', 'my_fallstudio', 'fallstudio', '']
    ];
    
    foreach ($db_configs as $config) {
        list($host, $db, $user, $pass) = $config;
        @$conn = new mysqli($host, $user, $pass, $db);
        
        if (!$conn->connect_errno) {
            $mysqli = $conn;
            log_error("DB connected: $host / $db / $user");
            break;
        }
    }
    
    if (!$mysqli) {
        log_error("DB connection failed - using file fallback");
        // Fall back to file storage
        $subscribers_file = __DIR__ . '/subscribers.json';
        $subscribers = [];
        
        if (file_exists($subscribers_file)) {
            $content = @file_get_contents($subscribers_file);
            if ($content) {
                $subscribers = @json_decode($content, true) ?: [];
            }
        }
        
        // Check if already subscribed
        foreach ($subscribers as $sub) {
            if (isset($sub['email']) && strtolower($sub['email']) === $email) {
                log_error("Email already exists (file): $email");
                http_response_code(200);
                die(json_encode(['message' => 'Email già registrata']));
            }
        }
        
        // Add new subscriber
        $subscribers[] = [
            'email' => $email,
            'date' => date('Y-m-d H:i:s'),
            'ip' => $ip
        ];
        
        if (@file_put_contents($subscribers_file, json_encode($subscribers, JSON_PRETTY_PRINT))) {
            log_error("Success (file): $email");
            http_response_code(201);
            echo json_encode(['message' => 'Email registrata con successo! Controlla la tua inbox.']);
        } else {
            log_error("Failed to write file");
            http_response_code(500);
            echo json_encode(['error' => 'Errore di sistema']);
        }
        exit;
    }
    
    // Database connected - use it
    $mysqli->set_charset("utf8mb4");
    
    // Check if email already exists
    $check_stmt = $mysqli->prepare('SELECT id FROM subscribers WHERE LOWER(email) = ?');
    if ($check_stmt) {
        $check_stmt->bind_param('s', $email);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            log_error("Email already exists (DB): $email");
            http_response_code(200);
            echo json_encode(['message' => 'Email già registrata']);
            $check_stmt->close();
            $mysqli->close();
            exit;
        }
        $check_stmt->close();
    }
    
    // Insert new email (provide created_at because table requires it)
    $insert_stmt = $mysqli->prepare('INSERT INTO subscribers (email, created_at) VALUES (?, NOW())');
    if (!$insert_stmt) {
        log_error("Prepare failed: " . $mysqli->error);
        http_response_code(500);
        echo json_encode(['error' => 'Errore di sistema']);
        $mysqli->close();
        exit;
    }
    
    $insert_stmt->bind_param('s', $email);
    
    if (!$insert_stmt->execute()) {
        $errno = $insert_stmt->errno;
        log_error("Execute failed - errno: $errno - " . $insert_stmt->error);
        
        // Duplicate key error
        if ($errno === 1062 || $errno === 23000) {
            http_response_code(200);
            echo json_encode(['message' => 'Email già registrata']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Errore durante la registrazione']);
        }
        $insert_stmt->close();
        $mysqli->close();
        exit;
    }
    
    log_error("Success (DB): $email");
    http_response_code(201);
    echo json_encode(['message' => 'Email registrata con successo! Controlla la tua inbox.']);
    
    $insert_stmt->close();
    $mysqli->close();

} catch (Exception $e) {
    log_error("Exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Errore di sistema']);
}
?>
