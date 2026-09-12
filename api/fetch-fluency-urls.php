<?php
/**
 * Fetch all Unsplash image URLs for fluency.yaml picture_word exercises
 */

const UNSPLASH_API_URL = 'https://api.unsplash.com';
const UNSPLASH_ACCESS_KEY = 'nI0_dkU_xrUmf6BNwnhgfXtfD7GUDrZ7byHymVmhKEg';

// Mapping from correct_answer to search query
$searchMappings = [
    'Speak' => 'person speaking',
    'Listen' => 'person listening',
    'Understand' => 'person thinking',
    'Repeat' => 'repeat words',
    'Slowly' => 'slow motion',
    'Quickly' => 'fast speed',
    'Conversation' => 'two people talking',
    'Question' => 'question mark',
    'Answer' => 'person answering',
    'Practice' => 'practice speaking'
];

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
        return ['error' => "HTTP $httpCode"];
    }
    
    $data = json_decode($response, true);
    
    if (empty($data['results'])) {
        return ['error' => 'No results found'];
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

echo "Fetching image URLs for fluency.yaml...\n\n";

$results = [];
foreach ($searchMappings as $answer => $query) {
    echo "Fetching: $answer ($query)...\n";
    $result = getUnsplashImageUrl($query);
    $results[$answer] = $result;
    sleep(1); // Rate limiting
}

echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
