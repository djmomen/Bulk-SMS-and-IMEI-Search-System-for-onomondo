<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');
error_reporting(E_ALL);

require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $imeiNumbers = array_filter(array_map('trim', explode("\n", $_POST['imei_numbers'])));
    $searchResults = [];
    
    foreach ($imeiNumbers as $imei) {
        $result = searchImei($imei);
        $searchResults[$imei] = $result;
    }
    
    $html = '<h4>IMEI Search Results</h4>';
    $html .= '<ul class="list-group">';
    foreach ($searchResults as $imei => $result) {
        $badgeClass = $result['success'] ? 'bg-success' : 'bg-danger';
        $html .= "<li class='list-group-item d-flex justify-content-between align-items-center'>";
        $html .= "IMEI: $imei ";
        if ($result['success']) {
            $html .= "<span class='badge {$badgeClass} rounded-pill sim-id'>{$result['data']['id']}</span>";
            $html .= "<small>ICCID: {$result['data']['iccid']}</small>";
        } else {
            $html .= "<span class='badge {$badgeClass} rounded-pill sim-id'>{$result['message']}</span>";
        }
        $html .= "</li>";
    }
    $html .= '</ul>';
    $html .= '<button id="copyImeiResults" class="btn btn-secondary mt-2"><i class="fas fa-copy"></i> Copy SIM IDs</button>';
    $html .= '<small class="d-block mt-2">' . count($searchResults) . ' results found</small>';
    
    logActivity("IMEI search completed: " . count($searchResults) . " results found");
    echo $html;
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
