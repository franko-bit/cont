<?php
// Enhanced script with improved keyword mappings for better Unsplash API results
define('UNSPLASH_ACCESS_KEY', 'nI0_dkU_xrUmf6BNwnhgfXtfD7GUDrZ7byHymVmhKEg');

// Enhanced keyword mappings - using more specific search terms for failed items
$keywordMappings = [
    'water' => 'glass of water',
    'beans' => 'legumes dry beans',
    'banana' => 'yellow banana',
    'orange' => 'orange fruit',
    'mango' => 'mango tropical fruit',
    'pineapple' => 'pineapple fruit',
    'potato' => 'raw potato',
    'window' => 'house window',
    'bedroom' => 'bedroom interior design',
    'bathroom' => 'modern bathroom',
    'chair' => 'wooden chair furniture',
    'table' => 'wooden dining table',
    'sofa' => 'living room sofa',
    'key' => 'door key house',
    'left' => 'direction left arrow',
    'right' => 'direction right arrow',
    'forward' => 'going forward movement',
    'traffic' => 'busy street traffic',
    'street' => 'city street road',
    'intersection' => 'street intersection',
    'restaurant' => 'restaurant dining',
    'coffee' => 'coffee cup hot',
    'juice' => 'fresh juice glass',
    'fork' => 'dinner fork utensil',
    'spoon' => 'spoon utensil',
    'knife' => 'dinner knife',
    'plate' => 'empty plate white',
    'napkin' => 'table napkin',
    'proud' => 'feeling proud confidence',
    'love' => 'love emotion heart',
    'market' => 'marketplace outdoor market',
    'money' => 'cash money bills',
    'bag' => 'shopping bag',
    'shoes' => 'sneaker shoes',
    'fruit' => 'fresh fruit assorted',
    'vegetables' => 'fresh vegetables market',
    'clothes' => 'clothing fashion',
    'electronics' => 'electronic devices gadgets',
    'airplane' => 'passenger airplane',
    'bus' => 'city bus transport',
    'train' => 'passenger train',
    'car' => 'parked car vehicle',
    'motorcycle' => 'street motorcycle',
    'bicycle' => 'bicycle bike',
    'passport' => 'travel passport document',
    'ticket' => 'travel ticket',
    'luggage' => 'travel luggage suitcase',
    'hotel' => 'hotel building exterior',
    'map' => 'paper map tourist',
    'airport' => 'airport terminal',
    'rain' => 'rainy day weather',
    'studying' => 'student studying books',
    'helping' => 'helping others hands',
    'tomorrow' => 'future tomorrow calendar',
    'calendar' => 'wall calendar dates',
    'soon' => 'coming soon text',
    'travel' => 'travel adventure journey',
    'debate' => 'debate discussion group',
    'opinion' => 'giving opinion speech',
    'agreement' => 'people agreeing handshake',
    'disagreement' => 'disagreement conflict',
    'evidence' => 'evidence proof document',
    'fact' => 'factual information book',
    'speaker' => 'public speaker podium',
    'audience' => 'listening audience crowd',
    'contract' => 'business contract papers',
    'investment' => 'financial investment money',
    'clothing' => 'traditional clothing fashion',
    'community' => 'community people together',
    'ceremony' => 'cultural ceremony celebration',
    'language' => 'language learning education',
    'sunday' => 'sunday rest relaxation',
    'morning' => 'morning sunrise landscape',
    'night' => 'night sky stars',
    'happy' => 'happy smiling face',
    'maybe' => 'uncertain maybe gesture',
];

// Get Unsplash image URL from API
function getUnsplashImageUrl($query) {
    $apiUrl = 'https://api.unsplash.com/search/photos';
    $params = [
        'query' => $query,
        'per_page' => 1,
        'orientation' => 'landscape',
        'client_id' => UNSPLASH_ACCESS_KEY
    ];
    
    $fullUrl = $apiUrl . '?' . http_build_query($params);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $fullUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 || !$response) {
        return null;
    }
    
    $data = json_decode($response, true);
    
    if (!isset($data['results']) || count($data['results']) === 0) {
        return null;
    }
    
    $photo = $data['results'][0];
    $photoId = $photo['id'];
    $params = "crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=" . $photo['links']['download_location'] . "&ixlib=rb-4.1.0&q=80&w=600&h=400&fit=crop";
    
    return "https://images.unsplash.com/photo-{$photoId}?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w5NzA5MzJ8MHwxfHNlYXJjaHwxfHx" . urlencode(strtolower(str_replace(' ', '%20', $query))) . "&ixlib=rb-4.1.0&q=80&w=600&h=400&fit=crop";
}

// Process all YAML files
$basePath = 'c:\\xampp\\htdocs\\language-platform\\content\\RW-TO-EN';
$updatedCount = 0;
$failedCount = 0;

echo "Starting enhanced Unsplash image URL updates...\n\n";

for ($level = 1; $level <= 6; $level++) {
    $levelPath = "{$basePath}\\level{$level}";
    
    if (!is_dir($levelPath)) {
        continue;
    }
    
    $files = glob("{$levelPath}\\*.yaml");
    
    foreach ($files as $file) {
        $content = file_get_contents($file);
        $filename = basename($file);
        
        // Find all featured URLs and replace them
        preg_match_all('/image_url:\s*"https:\/\/source\.unsplash\.com\/featured\/600x400\/\?([^"]+)"/', $content, $matches);
        
        if (empty($matches[1])) {
            continue;
        }
        
        $keywords = $matches[1];
        foreach ($keywords as $keyword) {
            // Use enhanced mapping if available
            $searchQuery = isset($keywordMappings[$keyword]) ? $keywordMappings[$keyword] : $keyword;
            
            // Get URL from API
            $newUrl = getUnsplashImageUrl($searchQuery);
            
            if ($newUrl) {
                $oldUrl = "https://source.unsplash.com/featured/600x400/?{$keyword}";
                $content = str_replace("image_url: \"{$oldUrl}\"", "image_url: \"{$newUrl}\"", $content);
                echo "✓ Updated {$keyword} in {$filename}\n";
                $updatedCount++;
            } else {
                echo "✗ FAILED to get URL for {$keyword} in {$filename}\n";
                $failedCount++;
            }
            
            // Rate limiting - be nice to the API
            usleep(500000); // 0.5 second delay
        }
        
        // Save updated file
        file_put_contents($file, $content);
    }
}

echo "\n\nCompleted! Updated {$updatedCount} items, {$failedCount} failed\n";
?>
