<?php
/**
 * Newsletter List Endpoint (Admin)
 * GET /api/newsletter/list
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../lib/Database.php';

try {
    $db = Database::getInstance();
    $subscribers = $db->getSubscribers();

    echo json_encode($subscribers);


}
catch (Exception $e) {
    error_log('Newsletter list error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Errore DB']);
}
