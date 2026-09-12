<?php
const UNSPLASH_API_URL = 'https://api.unsplash.com';
const UNSPLASH_ACCESS_KEY = 'nI0_dkU_xrUmf6BNwnhgfXtfD7GUDrZ7byHymVmhKEg';

function getUnsplashImageUrl($searchQuery) {
    $searchUrl = UNSPLASH_API_URL . '/search/photos?' . http_build_query([
        'query' => $searchQuery,
        'per_page' => 1,
        'orientation' => 'landscape',
        'client_id' => UNSPLASH_ACCESS_KEY
    ]);
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $searchUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPHEADER => ['Accept-Version: v1']
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) return null;
    
    $data = json_decode($response, true);
    if (empty($data['results'])) return null;
    
    $photo = $data['results'][0];
    $url = $photo['urls']['regular'];
    
    if (strpos($url, '?') === false) {
        $url .= '?w=600&h=400&fit=crop';
    } else {
        $url .= '&w=600&h=400&fit=crop';
    }
    
    return $url;
}

echo "Retrying failed queries...\n\n";
$retries = ['Speak' => 'speaking', 'Repeat' => 'repeating words', 'Quickly' => 'quick fast'];

foreach ($retries as $answer => $query) {
    echo "Trying: $answer ($query)...\n";
    $url = getUnsplashImageUrl($query);
    if ($url) {
        echo "✓ Success: " . substr($url, 0, 80) . "...\n\n";
    } else {
        echo "✗ Failed\n\n";
    }
    sleep(2);
}
?>
