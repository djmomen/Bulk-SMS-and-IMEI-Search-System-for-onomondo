<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');
error_reporting(E_ALL);

require_once 'functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $postData = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        logActivity("Error decoding JSON: " . json_last_error_msg() . ". Raw input: " . $input);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        exit;
    }

    $simNumbers = isset($postData['sim_numbers']) ? array_filter(array_map('trim', explode("\n", $postData['sim_numbers']))) : [];
    $fromField = isset($postData['from_field']) ? trim($postData['from_field']) : '';
    $messageText = $postData['message_text'] ?? '';

    if (empty($simNumbers)) {
        echo json_encode(['success' => false, 'message' => 'No valid SIM numbers provided']);
        exit;
    }

    $jobId = uniqid();
    $jobData = [
        'id' => $jobId,
        'simNumbers' => $simNumbers,
        'fromField' => $fromField,
        'messageText' => $messageText,
        'totalCount' => count($simNumbers),
        'processedCount' => 0,
        'successCount' => 0,
        'failCount' => 0,
        'status' => 'queued',
        'lastProcessedSim' => null
    ];

    $jobFile = __DIR__ . "/jobs/{$jobId}.json";
    file_put_contents($jobFile, json_encode($jobData));

    $cmd = "php " . __DIR__ . "/sms_worker.php {$jobId} > /dev/null 2>&1 &";
    exec($cmd);

    echo json_encode([
        'success' => true,
        'message' => 'SMS sending job queued',
        'jobId' => $jobId
    ]);
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
