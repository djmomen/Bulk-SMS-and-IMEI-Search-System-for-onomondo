<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');
error_reporting(E_ALL);

header('Content-Type: application/json');

$jobId = $_GET['jobId'] ?? null;

if (!$jobId) {
    echo json_encode(['success' => false, 'message' => 'No job ID provided']);
    exit;
}

$jobFile = __DIR__ . "/jobs/{$jobId}.json";

if (!file_exists($jobFile)) {
    echo json_encode(['success' => false, 'message' => 'Job not found']);
    exit;
}

$jobData = json_decode(file_get_contents($jobFile), true);

echo json_encode($jobData);
