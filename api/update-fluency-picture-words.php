<?php
/**
 * Update picture_word image URLs in fluency.yaml with real Unsplash API images
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
        CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPHEADER => [
            'Accept-Version: v1'
        ]
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        return null;
    }
    
    $data = json_decode($response, true);
    
    if (empty($data['results'])) {
        return null;
    }
    
    $photo = $data['results'][0];
    $imageUrl = $photo['urls']['regular'];
    
    // Add sizing parameters
    if (strpos($imageUrl, '?') === false) {
        $imageUrl .= '?w=600&h=400&fit=crop';
    } else {
        $imageUrl .= '&w=600&h=400&fit=crop';
    }
    
    return $imageUrl;
}

function updateYamlFile($filePath, $searchMappings) {
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $output = [];
    $updated = [];
    $failed = [];
    
    $inYaml = false;
    $i = 0;
    
    while ($i < count($lines)) {
        $line = $lines[$i];
        
        // Check if this is a picture_word type exercise
        if (strpos($line, 'type: picture_word') !== false) {
            // Add this line as-is
            $output[] = $line;
            $i++;
            
            // Look for the next image_url line
            $foundImage = false;
            $foundAnswer = false;
            $correctAnswer = null;
            
            // Scan forward to find both image_url and correct_answer
            $tempLines = [];
            $tempI = $i;
            
            while ($tempI < count($lines)) {
                $tempLine = $lines[$tempI];
                $tempLines[] = $tempLine;
                
                if (strpos($tempLine, 'image_url:') !== false && !$foundImage) {
                    $foundImage = true;
                }
                
                if (strpos($tempLine, 'correct_answer:') !== false && !$foundAnswer) {
                    // Extract the correct_answer value
                    if (preg_match('/correct_answer:\s*"?([^"]+)"?/', $tempLine, $matches)) {
                        $correctAnswer = trim($matches[1], '"');
                        $foundAnswer = true;
                    }
                }
                
                if ($foundImage && $foundAnswer) {
                    break;
                }
                
                // Stop if we hit the next exercise
                if (strpos($tempLine, '- id:') !== false && $tempI !== $i - 1) {
                    break;
                }
                
                $tempI++;
            }
            
            // Now process the lines
            while ($i < count($lines) && $i < $tempI) {
                $line = $lines[$i];
                
                // Replace image_url line if it has the old format
                if (strpos($line, 'image_url:') !== false && strpos($line, 'source.unsplash.com/featured') !== false) {
                    if ($correctAnswer && isset($searchMappings[$correctAnswer])) {
                        $newUrl = getUnsplashImageUrl($searchMappings[$correctAnswer]);
                        
                        if ($newUrl) {
                            // Preserve indentation
                            $indent = strlen($line) - strlen(ltrim($line));
                            $output[] = str_repeat(' ', $indent) . 'image_url: "' . $newUrl . '"';
                            $updated[] = $correctAnswer;
                        } else {
                            $output[] = $line;
                            $failed[] = $correctAnswer . ' (API timeout)';
                        }
                    } else {
                        $output[] = $line;
                    }
                } else {
                    $output[] = $line;
                }
                
                $i++;
            }
        } else {
            $output[] = $line;
            $i++;
        }
    }
    
    // Write back to file
    file_put_contents($filePath, implode("\n", $output) . "\n");
    
    return [
        'success' => true,
        'file' => $filePath,
        'updated_count' => count($updated),
        'updated_answers' => $updated,
        'failed_count' => count($failed),
        'failed_answers' => $failed
    ];
}

$filePath = '/xampp/htdocs/language-platform/content/RW-TO-EN/level6/fluency.yaml';

echo "Updating picture_word image URLs in fluency.yaml...\n\n";

$result = updateYamlFile($filePath, $searchMappings);

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
