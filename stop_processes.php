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

    $jobId = $postData['jobId'] ?? null;

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
    $jobData['status'] = 'stopped';
    file_put_contents($jobFile, json_encode($jobData));

    // Terminate the worker process
    $cmd = "pkill -f 'php.*sms_worker.php.*{$jobId}'";
    exec($cmd);

    logActivity("Job {$jobId} stopped and terminated");

    echo json_encode(['success' => true, 'message' => 'Job stopped and terminated successfully']);
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
