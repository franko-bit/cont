<?php
/**
 * Unsplash API Proxy
 * Server-side proxy to fetch images from Unsplash using the API
 * Bypasses CORS issues and handles authentication
 */

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: max-age=3600'); // Cache for 1 hour

// Unsplash API Configuration
const UNSPLASH_API_URL = 'https://api.unsplash.com';
const UNSPLASH_ACCESS_KEY = 'nI0_dkU_xrUmf6BNwnhgfXtfD7GUDrZ7byHymVmhKEg';

/**
 * Get image from Unsplash API
 * @param string $query Search query
 * @param int $width Desired width
 * @param int $height Desired height
 * @return array|false Image data or false on error
 */
function getUnsplashImage($query, $width = 400, $height = 300) {
    // Validate input
    if (empty($query)) {
        return ['error' => 'Search query is required'];
    }
    
    // Build API URL
    $searchUrl = UNSPLASH_API_URL . '/search/photos?' . http_build_query([
        'query' => $query,
        'per_page' => 1,
        'orientation' => 'landscape',
        'client_id' => UNSPLASH_ACCESS_KEY
    ]);
    
    // Fetch from API
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $searchUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => [
            'Accept-Version: v1'
        ]
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Handle errors
    if ($httpCode !== 200) {
        return [
            'error' => 'API Error',
            'status' => $httpCode,
            'message' => 'Failed to fetch image from Unsplash API'
        ];
    }
    
    $data = json_decode($response, true);
    
    // Check if we got results
    if (empty($data['results'])) {
        return [
            'error' => 'No results',
            'message' => 'No images found for query: ' . $query
        ];
    }
    
    $photo = $data['results'][0];
    
    // Build resized image URL
    $imageUrl = $photo['urls']['regular'];
    if (strpos($imageUrl, '?') === false) {
        $imageUrl .= '?';
    } else {
        $imageUrl .= '&';
    }
    $imageUrl .= "w={$width}&h={$height}&fit=crop";
    
    return [
        'success' => true,
        'url' => $imageUrl,
        'author' => $photo['user']['name'] ?? 'Unknown',
        'description' => $photo['description'] ?? $photo['alt_description'] ?? '',
        'source' => 'unsplash',
        'photo_id' => $photo['id']
    ];
}

// Handle request
$action = $_GET['action'] ?? 'fetch';

switch ($action) {
    case 'fetch':
        $query = $_GET['q'] ?? '';
        $width = (int)($_GET['w'] ?? 400);
        $height = (int)($_GET['h'] ?? 300);
        
        // Sanitize inputs
        $query = trim($query);
        $width = max(100, min(1600, $width));
        $height = max(100, min(1600, $height));
        
        $result = getUnsplashImage($query, $width, $height);
        
        if (isset($result['error'])) {
            http_response_code(400);
            echo json_encode($result);
        } else {
            // Redirect to image or return URL
            if ($_GET['redirect'] ?? false) {
                header('Location: ' . $result['url']);
            } else {
                http_response_code(200);
                echo json_encode($result);
            }
        }
        break;
    
    case 'health':
        echo json_encode([
            'status' => 'ok',
            'api' => 'Unsplash',
            'timestamp' => date('c')
        ]);
        break;
    
    default:
        http_response_code(400);
        echo json_encode([
            'error' => 'Invalid action',
            'available' => ['fetch', 'health']
        ]);
}
?>
