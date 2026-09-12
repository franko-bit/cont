<?php
/**
 * Update YAML files with direct Unsplash image URLs
 * Fetches real image URLs from Unsplash API and updates all lesson YAML files
 */

// Unsplash API Configuration
const UNSPLASH_API_URL = 'https://api.unsplash.com';
const UNSPLASH_ACCESS_KEY = 'nI0_dkU_xrUmf6BNwnhgfXtfD7GUDrZ7byHymVmhKEg';

/**
 * Fetch image URL from Unsplash API
 */
function getUnsplashImageUrl($query) {
    $searchUrl = UNSPLASH_API_URL . '/search/photos?' . http_build_query([
        'query' => $query,
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
    curl_close($ch);
    
    if ($httpCode !== 200) {
        return null;
    }
    
    $data = json_decode($response, true);
    
    if (empty($data['results'])) {
        return null;
    }
    
    $photo = $data['results'][0];
    
    // Build direct image URL with proper formatting
    $imageUrl = $photo['urls']['raw'];
    $imageUrl .= '?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixlib=rb-4.1.0&q=80&w=600&h=400&fit=crop';
    
    return $imageUrl;
}

/**
 * Extract keywords from featured URL and fetch real image
 */
function convertFeaturedToDirectUrl($featuredUrl) {
    // Extract keyword from URL like: https://source.unsplash.com/featured/600x400/?keyword
    if (preg_match('/\?(.+)$/', $featuredUrl, $matches)) {
        $keyword = $matches[1];
        $imageUrl = getUnsplashImageUrl($keyword);
        if ($imageUrl) {
            return $imageUrl;
        }
    }
    return null;
}

/**
 * Update YAML file with new image URLs
 */
function updateYamlFile($filePath) {
    if (!file_exists($filePath)) {
        return false;
    }
    
    $content = file_get_contents($filePath);
    $updated = false;
    
    // Find all featured URLs and replace them
    $pattern = '/image_url:\s*"https:\/\/source\.unsplash\.com\/featured\/600x400\/\?([^"]+)"/';
    
    if (preg_match_all($pattern, $content, $matches)) {
        foreach ($matches[1] as $keyword) {
            $oldUrl = "image_url: \"https://source.unsplash.com/featured/600x400/?" . $keyword . "\"";
            $directUrl = getUnsplashImageUrl($keyword);
            
            if ($directUrl) {
                $newUrl = 'image_url: "' . $directUrl . '"';
                $content = str_replace($oldUrl, $newUrl, $content);
                $updated = true;
                echo "Updated $keyword in " . basename($filePath) . "\n";
                
                // Small delay to avoid rate limiting
                usleep(500000); // 0.5 second
            } else {
                echo "FAILED to get URL for $keyword in " . basename($filePath) . "\n";
            }
        }
    }
    
    if ($updated) {
        file_put_contents($filePath, $content);
        return true;
    }
    
    return false;
}

// Find all YAML files in levels 1-6
$levels_dir = __DIR__ . '/content/RW-TO-EN';
$yaml_files = [];

for ($level = 1; $level <= 6; $level++) {
    $level_dir = $levels_dir . "/level$level";
    if (is_dir($level_dir)) {
        $files = glob($level_dir . '/*.yaml');
        $yaml_files = array_merge($yaml_files, $files);
    }
}

echo "Found " . count($yaml_files) . " YAML files to update\n";
echo "Starting Unsplash image URL updates...\n\n";

$success_count = 0;
$total_count = count($yaml_files);

foreach ($yaml_files as $file) {
    if (updateYamlFile($file)) {
        $success_count++;
    }
}

echo "\n\nCompleted! Updated $success_count out of $total_count files\n";
?>
