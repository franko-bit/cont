<?php
/**
 * Generate Missing Flashcards for All Language Pairs
 * 
 * This script:
 * 1. Reads translation exercises from each lesson YAML
 * 2. Extracts front (source language) and back (target language) word pairs
 * 3. Adds flashcards section with images to lessons missing them
 */

$base_path = __DIR__ . '/content';

// Image URLs for different topics (from existing flashcard patterns)
$topic_images = [
    'animals' => [
        'Vache' => 'https://images.pexels.com/photos/675420/pexels-photo-675420.jpeg?w=400&h=267&fit=crop',
        'Chien' => 'https://images.pexels.com/photos/1805164/pexels-photo-1805164.jpeg?w=400&h=267&fit=crop',
        'Chat' => 'https://images.pexels.com/photos/45201/kitty-cat-kitten-pet-45201.jpeg?w=400&h=267&fit=crop',
        'Chèvre' => 'https://images.pexels.com/photos/257532/pexels-photo-257532.jpeg?w=400&h=267&fit=crop',
        'Mouton' => 'https://images.pexels.com/photos/3687770/pexels-photo-3687770.jpeg?w=400&h=267&fit=crop',
        'Poulet' => 'https://images.pexels.com/photos/162140/duckling-bird-yellow-fluffy-162140.jpeg?w=400&h=267&fit=crop',
        'Lion' => 'https://images.pexels.com/photos/247502/pexels-photo-247502.jpeg?w=400&h=267&fit=crop',
        'Éléphant' => 'https://images.pexels.com/photos/265216/pexels-photo-265216.jpeg?w=400&h=267&fit=crop',
        'Oiseau' => 'https://images.pexels.com/photos/257488/pexels-photo-257488.jpeg?w=400&h=267&fit=crop',
        'Poisson' => 'https://images.pexels.com/photos/128756/pexels-photo-128756.jpeg?w=400&h=267&fit=crop',
    ],
    'colors' => [
        'Red' => 'https://images.unsplash.com/photo-1590080876/red-paint.jpg?w=400&h=267&fit=crop',
        'Blue' => 'https://images.unsplash.com/photo-1575502742937-87b0e848c3b8?w=400&h=267&fit=crop',
        'Green' => 'https://images.unsplash.com/photo-1568308844852-6c20ae76caa5?w=400&h=267&fit=crop',
    ]
];

// Language folders to process
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

echo "=== Flashcard Generation Report ===\n\n";

foreach ($folders_to_process as $folder) {
    echo "Processing: $folder\n";
    $folder_path = "$base_path/$folder";
    
    for ($level = 1; $level <= 6; $level++) {
        $level_path = "$folder_path/level$level";
        
        if (!is_dir($level_path)) {
            continue;
        }
        
        $yaml_files = glob("$level_path/*.yaml");
        
        foreach ($yaml_files as $yaml_file) {
            $filename = basename($yaml_file);
            $topic = pathinfo($filename, PATHINFO_FILENAME);
            
            // Skip if already has flashcards
            $content = file_get_contents($yaml_file);
            if (strpos($content, 'type: flashcards') !== false) {
                echo "  ✓ $filename: Already has flashcards\n";
                continue;
            }
            
            // Extract word pairs from translation exercises
            $word_pairs = extract_translations($content);
            
            if (empty($word_pairs)) {
                echo "  ✗ $filename: No translation exercises found\n";
                continue;
            }
            
            // Take first 10 word pairs for flashcards
            $flashcards = array_slice($word_pairs, 0, 10);
            
            // Add flashcards section to YAML
            $flashcard_yaml = generate_flashcards_section($flashcards, $topic);
            
            // Append to file
            $updated_content = $content . "\n" . $flashcard_yaml;
            file_put_contents($yaml_file, $updated_content);
            
            echo "  + $filename: Added " . count($flashcards) . " flashcards\n";
        }
    }
    echo "\n";
}

function extract_translations($content) {
    $word_pairs = [];
    
    // Find all translation exercises
    if (preg_match_all('/type:\s*translation.*?question:\s*["\']([^"\']+)["\'].*?answer:\s*["\']([^"\']+)["\'].*?xp_reward:/s', $content, $matches)) {
        for ($i = 0; $i < count($matches[1]); $i++) {
            $question = trim($matches[1][$i]);
            $answer = trim($matches[2][$i]);
            
            // Skip compound phrases - only use single words
            if (strlen($answer) > 20 || strpos($answer, ' ') !== false) {
                continue;
            }
            
            $word_pairs[] = [
                'front' => $answer,  // The answer is typically the target language word
                'back' => $question   // The question contains the source language word
            ];
        }
    }
    
    return $word_pairs;
}

function generate_flashcards_section($word_pairs, $topic) {
    $yaml = "\n# Flashcards\n";
    $yaml .= "  - id: 111\n";
    $yaml .= "    type: flashcards\n";
    $yaml .= "    question: \"Flashcards\"\n";
    $yaml .= "    flashcards:\n";
    
    // Generic images - we'll use unsplash URLs
    $images = [
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
    
    foreach ($word_pairs as $idx => $pair) {
        $image = $images[$idx % count($images)];
        $yaml .= "    - front: \"" . addslashes($pair['front']) . "\"\n";
        $yaml .= "      back: \"" . addslashes($pair['back']) . "\"\n";
        $yaml .= "      image: \"$image\"\n";
    }
    
    $yaml .= "    xp_reward: 30\n";
    
    return $yaml;
}

echo "✓ Flashcard generation complete!\n";
?>
