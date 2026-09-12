<?php
/**
 * Picture Word Image URL Updater
 * Fetches correct image URLs from Unsplash API based on correct_answer field
 * and updates YAML files
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Unsplash API Configuration
const UNSPLASH_API_URL = 'https://api.unsplash.com';
const UNSPLASH_ACCESS_KEY = 'nI0_dkU_xrUmf6BNwnhgfXtfD7GUDrZ7byHymVmhKEg';

// Configurations
$searchMappings = [
    'One' => 'number 1',
    'Two' => 'number 2',
    'Three' => 'number 3',
    'Ten' => 'number 10',
    'One hundred' => 'number 100',
    'Five hundred RWF' => 'five hundred rwandan francs',
];

/**
 * Search and get image URL from Unsplash
 */
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
        CURLOPT_TIMEOUT => 10,
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

/**
 * Parse YAML and update picture_word exercises
 */
function updateYamlFile($filePath, $searchMappings) {
    if (!file_exists($filePath)) {
        return ['success' => false, 'message' => "File not found: $filePath"];
    }
    
    $content = file_get_contents($filePath);
    $lines = explode("\n", $content);
    $updatedLines = [];
    $i = 0;
    $updates = [];
    
    while ($i < count($lines)) {
        $line = $lines[$i];
        
        // Check if this is a picture_word type
        if (preg_match('/^\s*type:\s*picture_word\s*$/', $line)) {
            // Look ahead to find correct_answer
            $j = $i + 1;
            $correctAnswer = null;
            $correctAnswerLineIndex = null;
            $imageUrlLineIndex = null;
            
            while ($j < count($lines) && !preg_match('/^\s*-\s*id:/', $lines[$j])) {
                if (preg_match('/^\s*correct_answer:\s*"?([^"]*)"?\s*$/', $lines[$j], $matches)) {
                    $correctAnswer = trim($matches[1]);
                    $correctAnswerLineIndex = $j;
                }
                if (preg_match('/^\s*image_url:\s*/', $lines[$j])) {
                    $imageUrlLineIndex = $j;
                }
                $j++;
            }
            
            // If we have correct_answer and image_url line, fetch new URL
            if ($correctAnswer && isset($searchMappings[$correctAnswer]) && $imageUrlLineIndex !== null) {
                $searchQuery = $searchMappings[$correctAnswer];
                echo "Searching for: $correctAnswer (query: $searchQuery)\n";
                
                $result = getUnsplashImageUrl($searchQuery);
                
                if ($result['success']) {
                    $newUrl = $result['url'];
                    $author = $result['author'];
                    $photoId = $result['photo_id'];
                    
                    // Update the image_url line
                    $indentation = preg_match('/^(\s*)/', $lines[$imageUrlLineIndex], $matches) ? $matches[1] : '    ';
                    $lines[$imageUrlLineIndex] = $indentation . 'image_url: "' . $newUrl . '"';
                    
                    $updates[] = [
                        'answer' => $correctAnswer,
                        'newUrl' => $newUrl,
                        'author' => $author,
                        'photoId' => $photoId
                    ];
                    
                    echo "✓ Updated: $correctAnswer -> by $author\n";
                } else {
                    echo "✗ Failed to fetch for $correctAnswer: " . $result['message'] . "\n";
                }
            }
        }
        
        $updatedLines[] = $line;
        $i++;
    }
    
    // Write back to file
    $updatedContent = implode("\n", $updatedLines);
    if (file_put_contents($filePath, $updatedContent)) {
        return [
            'success' => true,
            'file' => $filePath,
            'updates' => $updates,
            'message' => count($updates) . ' images updated'
        ];
    } else {
        return [
            'success' => false,
            'message' => "Failed to write to: $filePath"
        ];
    }
}

// Main execution
echo "=== Picture Word Image URL Updater ===\n\n";

// Process RW-TO-EN level1 numbers.yaml
$yamlPath = __DIR__ . '/../content/RW-TO-EN/level1/numbers.yaml';
$result = updateYamlFile($yamlPath, $searchMappings);

echo "\nResult:\n";
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
echo "\n";
?>
