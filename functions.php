<?php
require_once 'HTTP/Request2.php';

function logActivity($message) {
    $logFile = __DIR__ . '/activity_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "$timestamp - $message\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

function sendSingleSMS($simId, $fromField, $messageText) {
    $request = new HTTP_Request2();
    $request->setUrl('https://api.onomondo.com/sms/' . $simId);
    $request->setMethod(HTTP_Request2::METHOD_POST);
    $request->setConfig(array(
        'follow_redirects' => TRUE,
        'timeout' => 30 // Increase timeout for potentially slow API responses
    ));
    $request->setHeader(array(
        'Authorization' => '<API KEY>',
        'Content-Type' => 'application/json'
    ));
    $request->setBody(json_encode([
        'from' => $fromField,
        'text' => $messageText
    ]));

    try {
        $response = $request->send();
        if ($response->getStatus() == 200) {
            $body = json_decode($response->getBody(), true);
            return ['success' => true, 'message' => $body['message']];
        } else {
            return ['success' => false, 'message' => 'Unexpected HTTP status: ' . $response->getStatus() . ' ' . $response->getReasonPhrase()];
        }
    } catch(HTTP_Request2_Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

function searchImei($imei) {
    $request = new HTTP_Request2();
    $request->setUrl('https://api.onomondo.com/sims/find?search=' . urlencode($imei));
    $request->setMethod(HTTP_Request2::METHOD_GET);
    $request->setConfig(array(
        'follow_redirects' => TRUE
    ));
    $request->setHeader(array(
        'Authorization' => '<API KEY>'
    ));

    try {
        $response = $request->send();
        if ($response->getStatus() == 200) {
            $data = json_decode($response->getBody(), true);
            if (!empty($data)) {
                return ['success' => true, 'data' => $data[0]];
            } else {
                return ['success' => false, 'message' => 'No SIM found for this IMEI'];
            }
        } else {
            return ['success' => false, 'message' => 'Unexpected HTTP status: ' . $response->getStatus() . ' ' . $response->getReasonPhrase()];
        }
    } catch(HTTP_Request2_Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

function retrieveAllSimCards() {
    $allSims = [];
    $nextPage = null;
    
    do {
        $request = new HTTP_Request2();
        $url = 'https://api.onomondo.com/sims?limit=100' . ($nextPage ? "&next_page=$nextPage" : "");
        $request->setUrl($url);
        $request->setMethod(HTTP_Request2::METHOD_GET);
        $request->setHeader(array(
            'Authorization' => '<API KEY>'
        ));

        try {
            $response = $request->send();
            if ($response->getStatus() === 200) {
                $data = json_decode($response->getBody(), true);
                $allSims = array_merge($allSims, $data['sims']);
                $nextPage = $data['pagination']['next_page'] ?? null;
            } else {
                break;
            }
        } catch(HTTP_Request2_Exception $e) {
            logActivity('Error retrieving SIM cards: ' . $e->getMessage());
            break;
        }
    } while ($nextPage);
    
    return $allSims;
}
