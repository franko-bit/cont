<?php
/**
 * Generate Missing Flashcards for All Language Pairs
 * 
 * Reads translation exercises and creates flashcards for missing language folders
 */

$base_path = 'c:\\xampp\\htdocs\\language-platform\\content';

// Mapping: [SOURCE_LANG] => Unsplash/Pexels image URLs (rotated for variety)
$generic_images = [
    'https://images.unsplash.com/photo-1568308844852-6c20ae76caa5?w=400&h=267&fit=crop',
    'https://images.unsplash.com/photo-1570042225831-d98fa7577f1e?w=400&h=267&fit=crop',
    'https://images.unsplash.com/photo-1575502742937-87b0e848c3b8?w=400&h=267&fit=crop',
    'https://images.pexels.com/photos/45201/kitty-cat-kitten-pet-45201.jpeg?w=400&h=267&fit=crop',
    'https://images.pexels.com/photos/675420/pexels-photo-675420.jpeg?w=400&h=267&fit=crop',
    'https://images.pexels.com/photos/1805164/pexels-photo-1805164.jpeg?w=400&h=267&fit=crop',
    'https://images.pexels.com/photos/247502/pexels-photo-247502.jpeg?w=400&h=267&fit=crop',
    'https://images.pexels.com/photos/265216/pexels-photo-265216.jpeg?w=400&h=267&fit=crop',
    'https://images.pexels.com/photos/257488/pexels-photo-257488.jpeg?w=400&h=267&fit=crop',
    'https://images.pexels.com/photos/128756/pexels-photo-128756.jpeg?w=400&h=267&fit=crop',
];

// Language folders that need flashcards added
$folders_to_process = [
    'EN-TO-RW',
    'EN-TO-SW',
    'FR-TO-RW',
    'FR-TO-SW',
    'RW-TO-EN',
    'RW-TO-FR',
    'SW-TO-EN',
    'SW-TO-FR'
];

echo "=== Generating Missing Flashcards ===\n\n";

$total_added = 0;
$total_skipped = 0;

foreach ($folders_to_process as $folder) {
    echo "Processing: $folder\n";
    $folder_path = "$base_path/$folder";
    
    if (!is_dir($folder_path)) {
        echo "  [SKIP] Folder not found\n";
        continue;
    }
    
    for ($level = 1; $level <= 6; $level++) {
        $level_path = "$folder_path/level$level";
        
        if (!is_dir($level_path)) {
            continue;
        }
        
        $yaml_files = glob("$level_path/*.yaml");
        
        foreach ($yaml_files as $yaml_file) {
            $filename = basename($yaml_file);
            $content = file_get_contents($yaml_file);
            
            // Skip if already has flashcards
            if (strpos($content, 'type: flashcards') !== false) {
                echo "  ✓ $filename: Already has flashcards\n";
                continue;
            }
            
            // Extract word pairs from translation exercises
            $pairs = extract_translation_pairs($content);
            
            if (empty($pairs)) {
                echo "  ✗ $filename: No translation exercises found\n";
                $total_skipped++;
                continue;
            }
            
            // Create flashcards from first 10 pairs
            $flashcards_yaml = build_flashcards_yaml(array_slice($pairs, 0, 10), $generic_images);
            
            // Append to file
            file_put_contents($yaml_file, $content . $flashcards_yaml);
            
            echo "  + $filename: Added " . count($pairs) . " translation pairs\n";
            $total_added++;
        }
    }
    echo "\n";
}

echo "=== Summary ===\n";
echo "Files updated: $total_added\n";
echo "Files skipped: $total_skipped\n";

/**
 * Extract translation exercise question-answer pairs
 * Returns array of [front_word, back_word] pairs
 */
function extract_translation_pairs($yaml_content) {
    $pairs = [];
    
    // Split by exercises and parse each one
    // Look for exercises that have "type: translation" with question and answer
    
    // Find all "type: translation" blocks and extract their question/answer pairs
    if (preg_match_all('/type:\s*translation\s+question:\s*"([^"]+)"\s+answer:\s*"([^"]+)"/', $yaml_content, $matches)) {
        for ($i = 0; $i < count($matches[0]); $i++) {
            $question = trim($matches[1][$i]);
            $answer = trim($matches[2][$i]);
            
            // Skip very long phrases (flashcards work best with single words)
            if (strlen($answer) > 50 || strlen($question) > 100) {
                continue;
            }
            
            // Extract the key word from the question (usually in single quotes)
            // Format: "How do you say 'Cow' in Kinyarwanda?" => "Cow"
            $key_word = '';
            if (preg_match('/["\']([^"\']+)["\']/', $question, $m)) {
                $key_word = trim($m[1]);
            }
            
            if ($key_word && $answer) {
                $pairs[] = [
                    'front' => $answer,      // Target language (what user learns)
                    'back' => $key_word      // Source language (reference)
                ];
            }
        }
    }
    
    return array_values(array_unique($pairs, SORT_REGULAR));
}

/**
 * Build YAML flashcards section
 */
function build_flashcards_yaml($pairs, $images) {
    $yaml = "\n# Flashcards\n  - id: 111\n    type: flashcards\n    question: \"Flashcards\"\n    flashcards:\n";
    
    foreach ($pairs as $idx => $pair) {
        $image = $images[$idx % count($images)];
        $front = str_replace("'", "\\'", $pair['front'] ?? '');
        $back = str_replace("'", "\\'", $pair['back'] ?? '');
        
        $yaml .= "    - front: \"$front\"\n";
        $yaml .= "      back: \"$back\"\n";
        $yaml .= "      image: \"$image\"\n";
    }
    
    $yaml .= "    xp_reward: 30\n";
    
    return $yaml;
}

?>
