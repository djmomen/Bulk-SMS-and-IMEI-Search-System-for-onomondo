<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');
error_reporting(E_ALL);

require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $simCards = retrieveAllSimCards();
    
    $html = '<ul class="list-group">';
    foreach ($simCards as $simCard) {
        $html .= "<li class='list-group-item d-flex justify-content-between align-items-center sim-number'>";
        $html .= "{$simCard['id']} <span class='badge bg-info rounded-pill'>{$simCard['imei']}</span></li>";
    }
    $html .= '</ul>';
    
    logActivity(count($simCards) . " SIM cards retrieved");
    echo json_encode([
        'count' => count($simCards),
        'html' => $html
    ]);
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
