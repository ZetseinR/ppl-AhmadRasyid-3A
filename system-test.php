<?php
/**
 * System Test Suite (White Box Testing)
 * Aplikasi: REST Client PHP untuk NewsAPI.org
 * Nama File: system-test.php
 * 
 * Target Endpoint: https://newsapi.org/v2/top-headlines
 * URL: https://newsapi.org/v2/top-headlines?country=us&category=technology&apiKey=9cdbd3138a004a8e88bc7e8c984a2cc9
 */

// ============================================================================
// FUNGSI REST CLIENT INTERNAL (Target Pengujian White Box)
// ============================================================================
function callNewsAPI(?string $apiKey, string $country = 'us', string $category = 'technology'): array {
    $baseUrl = "https://newsapi.org/v2/top-headlines";
    
    $params = [
        'country' => $country,
        'category' => $category
    ];
    
    if ($apiKey !== null) {
        $params['apiKey'] = $apiKey;
    }

    $queryString = http_build_query($params);
    $url = $baseUrl . '?' . $queryString;

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => 'PHP-NewsAPI-Client/1.0',
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
        CURLOPT_TIMEOUT => 10
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        throw new Exception("cURL Error: " . $curlError);
    }

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Failed to parse JSON response: " . json_last_error_msg());
    }

    return [
        'http_code' => $httpCode,
        'data' => $data
    ];
}

// ============================================================================
// SUITE PENGUJIEN WHITE BOX DENGAN TRY-CATCH VALIDATOR
// ============================================================================

$testResults = [];

echo "===========================================================================\n";
echo "       WHITE BOX AUTOMATED TEST SUITE - REST CLIENT PHP (NewsAPI.org)      \n";
echo "===========================================================================\n\n";

// ----------------------------------------------------------------------------
// TEST CASE 1: Validasi Response Structure & Parsing Payload JSON
// ----------------------------------------------------------------------------
try {
    $apiKey = "9cdbd3138a004a8e88bc7e8c984a2cc9";
    $result = callNewsAPI($apiKey, 'us', 'technology');

    // Assertion / White Box Validator
    if ($result['http_code'] !== 200) {
        throw new Exception("Expected HTTP 200, got: " . $result['http_code']);
    }

    if (!isset($result['data']['status']) || $result['data']['status'] !== 'ok') {
        throw new Exception("JSON status payload is not 'ok'");
    }

    if (!isset($result['data']['articles']) || !is_array($result['data']['articles'])) {
        throw new Exception("Payload does not contain valid 'articles' array.");
    }

    $testResults[] = [
        'id' => 'TC-WB-001',
        'name' => 'Validasi HTTP 200 & Structure Payload JSON (Positive Path)',
        'status' => 'PASS',
        'message' => 'Response HTTP 200 OK, JSON valid, dan struktur array articles valid.'
    ];
} catch (Exception $e) {
    $testResults[] = [
        'id' => 'TC-WB-001',
        'name' => 'Validasi HTTP 200 & Structure Payload JSON (Positive Path)',
        'status' => 'FAIL',
        'message' => $e->getMessage()
    ];
}

// ----------------------------------------------------------------------------
// TEST CASE 2: Validasi Code Branching saat API Key Missing (Null)
// ----------------------------------------------------------------------------
try {
    $result = callNewsAPI(null, 'us', 'technology');

    // Assertion / White Box Validator
    if ($result['http_code'] !== 401) {
        throw new Exception("Expected HTTP 401 Unauthorized, got: " . $result['http_code']);
    }

    if (!isset($result['data']['code']) || $result['data']['code'] !== 'apiKeyMissing') {
        throw new Exception("Expected error code 'apiKeyMissing', got: " . ($result['data']['code'] ?? 'N/A'));
    }

    $testResults[] = [
        'id' => 'TC-WB-002',
        'name' => 'Validasi Branching Error HTTP 401 saat API Key Missing',
        'status' => 'PASS',
        'message' => 'Status HTTP 401 & error code apiKeyMissing berhasil terverifikasi.'
    ];
} catch (Exception $e) {
    $testResults[] = [
        'id' => 'TC-WB-002',
        'name' => 'Validasi Branching Error HTTP 401 saat API Key Missing',
        'status' => 'FAIL',
        'message' => $e->getMessage()
    ];
}

// ----------------------------------------------------------------------------
// TEST CASE 3: Validasi Handling Logic Exception saat API Key Invalid
// ----------------------------------------------------------------------------
try {
    $invalidApiKey = "9cdbd3138a004a8e88bc7e8c984a2cc9_INVALID";
    $result = callNewsAPI($invalidApiKey, 'us', 'technology');

    // Assertion / White Box Validator
    if ($result['http_code'] !== 401) {
        throw new Exception("Expected HTTP 401 Unauthorized, got: " . $result['http_code']);
    }

    if (!isset($result['data']['code']) || $result['data']['code'] !== 'apiKeyInvalid') {
        throw new Exception("Expected error code 'apiKeyInvalid', got: " . ($result['data']['code'] ?? 'N/A'));
    }

    $testResults[] = [
        'id' => 'TC-WB-003',
        'name' => 'Validasi Logic Exception saat API Key Invalid',
        'status' => 'PASS',
        'message' => 'Status HTTP 401 & error code apiKeyInvalid berhasil terverifikasi.'
    ];
} catch (Exception $e) {
    $testResults[] = [
        'id' => 'TC-WB-003',
        'name' => 'Validasi Logic Exception saat API Key Invalid',
        'status' => 'FAIL',
        'message' => $e->getMessage()
    ];
}

// ============================================================================
// TAMPILKAN HASIL PENGUJIEN
// ============================================================================
echo str_pad("Test ID", 12) . " | " . str_pad("Skenario Pengujian", 45) . " | " . str_pad("Status", 8) . " | Log / Message\n";
echo str_repeat("-", 100) . "\n";

foreach ($testResults as $res) {
    echo str_pad($res['id'], 12) . " | " . str_pad($res['name'], 45) . " | " . str_pad($res['status'], 8) . " | " . $res['message'] . "\n";
}