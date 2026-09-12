<?php
/**
 * Fetch single image URL for "Three"
 */

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
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => [
            'Accept-Version: v1'
        ]
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        return [
            'success' => false,
            'error' => "HTTP $httpCode",
            'message' => $error ?: 'Failed to fetch from Unsplash'
        ];
    }
    
    $data = json_decode($response, true);
    
    if (empty($data['results'])) {
        return [
            'success' => false,
            'error' => 'No results',
            'message' => "No images found for: $searchQuery"
        ];
    }
    
    $photo = $data['results'][0];
    $imageUrl = $photo['urls']['regular'];
    
    // Add sizing parameters
    if (strpos($imageUrl, '?') === false) {
        $imageUrl .= '?w=600&h=400&fit=crop';
    } else {
        $imageUrl .= '&w=600&h=400&fit=crop';
    }
    
    return [
        'success' => true,
        'url' => $imageUrl,
        'photo_id' => $photo['id'],
        'author' => $photo['user']['name'] ?? 'Unknown'
    ];
}

echo "Fetching image for 'Three'...\n";
$result = getUnsplashImageUrl('number 3');

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
