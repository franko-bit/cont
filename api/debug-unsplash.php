<?php
/**
 * Quick API Debug Test
 */

const UNSPLASH_ACCESS_KEY = 'nI0_dkU_xrUmf6BNwnhgfXtfD7GUDrZ7byHymVmhKEg';

$query = 'contract';

$searchUrl = 'https://api.unsplash.com/search/photos?' . http_build_query([
    'query' => $query,
    'per_page' => 1,
    'client_id' => UNSPLASH_ACCESS_KEY
]);

echo "<h1>API Debug</h1>";
echo "<p><strong>URL:</strong> " . htmlspecialchars($searchUrl) . "</p>";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $searchUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HTTPHEADER => [
        'Accept-Version: v1'
    ]
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "<p><strong>HTTP Status:</strong> " . $httpCode . "</p>";
if ($curlError) {
    echo "<p><strong>Curl Error:</strong> " . htmlspecialchars($curlError) . "</p>";
}

echo "<p><strong>Response:</strong></p>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

$data = json_decode($response, true);
if (is_array($data)) {
    echo "<p><strong>Parsed JSON:</strong></p>";
    echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
}
?>
