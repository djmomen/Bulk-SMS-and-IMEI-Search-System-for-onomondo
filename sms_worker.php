<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');
error_reporting(E_ALL);

require_once 'functions.php';

ini_set('memory_limit', '1G');
set_time_limit(0);

$jobId = $argv[1] ?? null;

if (!$jobId) {
    logActivity("Error: No job ID provided to worker");
    exit(1);
}

$jobFile = __DIR__ . "/jobs/{$jobId}.json";

if (!file_exists($jobFile)) {
    logActivity("Error: Job file not found for job ID: {$jobId}");
    exit(1);
}

$jobData = json_decode(file_get_contents($jobFile), true);

logActivity("Starting SMS sending job: {$jobId}");

$jobData['status'] = 'processing';
file_put_contents($jobFile, json_encode($jobData));

foreach ($jobData['simNumbers'] as $index => $simNumber) {
    $jobData = json_decode(file_get_contents($jobFile), true);
    if ($jobData['status'] === 'stopped') {
        break;
    }

    $result = sendSingleSMS($simNumber, $jobData['fromField'], $jobData['messageText']);
    
    if ($result['success']) {
        $jobData['successCount']++;
    } else {
        $jobData['failCount']++;
    }
    
    $jobData['processedCount']++;
    $jobData['lastProcessedSim'] = $simNumber;
    
    file_put_contents($jobFile, json_encode($jobData));
    
    logActivity("SMS to {$simNumber}: " . ($result['success'] ? 'Sent successfully' : 'Failed - ' . $result['message']));
    
    usleep(100000); // 0.1 second delay
}

$jobData['status'] = $jobData['status'] === 'stopped' ? 'stopped' : 'completed';
file_put_contents($jobFile, json_encode($jobData));

logActivity("SMS sending job {$jobData['status']}: {$jobId}");
