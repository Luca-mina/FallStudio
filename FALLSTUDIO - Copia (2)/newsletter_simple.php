<?php
// Ultra-simple newsletter handler using only JSON files (no database)
// More reliable on AlterVista shared hosting

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
    
    // Store in JSON file (no database needed!)
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
            log_error("Email already exists: $email");
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
    
    // Save to file
    if (@file_put_contents($subscribers_file, json_encode($subscribers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
        log_error("Success: $email registered");
        http_response_code(201);
        echo json_encode(['message' => 'Email registrata con successo! Controlla la tua inbox.']);
    } else {
        log_error("Failed to write file");
        http_response_code(500);
        echo json_encode(['error' => 'Errore di sistema']);
    }

} catch (Exception $e) {
    log_error("Exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Errore di sistema']);
}
?>
