<?php
/**
 * Contact Messages List Endpoint (Admin)
 * GET /api/contact/list
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../lib/Database.php';

try {
    $db = Database::getInstance();
    $messages = $db->getContactMessages();

    echo json_encode($messages);


}
catch (Exception $e) {
    error_log('Contact list error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Errore DB']);
}
