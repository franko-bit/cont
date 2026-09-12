<?php
/**
 * Direct Proxy Test
 */

// Make a direct request to the proxy
$url = 'http://localhost/language-platform/api/unsplash-proxy.php?action=fetch&q=contract';

echo "<h1>Direct Proxy Test</h1>";
echo "<p><strong>URL:</strong> " . htmlspecialchars($url) . "</p>";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>HTTP Status:</strong> " . $httpCode . "</p>";
echo "<p><strong>Response:</strong></p>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

$data = json_decode($response, true);
if (is_array($data)) {
    echo "<p><strong>Parsed:</strong></p>";
    echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "</pre>";
}
?>
