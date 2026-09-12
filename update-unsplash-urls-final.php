<?php
// Update remaining featured URLs (without 600x400 dimensions)

$curatedUrls = [
    'village' => 'https://images.unsplash.com/photo-1488747807830-63789f68bb65?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=600&h=400&fit=crop',
    'forest' => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=600&h=400&fit=crop',
    'mountain' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=600&h=400&fit=crop',
    'river' => 'https://images.unsplash.com/photo-1511379938547-c1f69b13d835?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=600&h=400&fit=crop',
    'boy' => 'https://images.unsplash.com/photo-1503454537688-e0fa8fdd8271?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=600&h=400&fit=crop',
    'girl' => 'https://images.unsplash.com/photo-1494438639946-1ebd1d20bf85?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=600&h=400&fit=crop',
    'mother' => 'https://images.unsplash.com/photo-1494438639946-1ebd1d20bf85?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=600&h=400&fit=crop',
    'bird' => 'https://images.unsplash.com/photo-1444464666175-1642156e4e68?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=600&h=400&fit=crop',
    'eating' => 'https://images.unsplash.com/photo-1495521821757-a1efb6729352?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=600&h=400&fit=crop',
    'drinking' => 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=600&h=400&fit=crop',
    'walking-away' => 'https://images.unsplash.com/photo-1518895949257-7621c3c786d7?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=600&h=400&fit=crop',
    'looking' => 'https://images.unsplash.com/photo-1503454537688-e0fa8fdd8271?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=600&h=400&fit=crop',
    'meeting' => 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=600&h=400&fit=crop',
    'shopping' => 'https://images.unsplash.com/photo-1488459716781-6518c6e4b65b?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=600&h=400&fit=crop',
    'playing' => 'https://images.unsplash.com/photo-1594615802327-68416c6f468a?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=600&h=400&fit=crop',
    'sleeping' => 'https://images.unsplash.com/photo-1541961017774-22349e4a1262?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=600&h=400&fit=crop',
];

$basePath = 'c:\\xampp\\htdocs\\language-platform\\content\\RW-TO-EN';
$updatedCount = 0;

echo "Updating remaining featured URLs...\n\n";

foreach (['storytelling.yaml', 'past-tense.yaml'] as $filename) {
    $file = "{$basePath}\\level4\\{$filename}";
    
    if (!file_exists($file)) {
        continue;
    }
    
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Find all featured URLs (both with and without 600x400)
    foreach ($curatedUrls as $keyword => $newUrl) {
        // Check both formats
        $oldUrlFormat1 = "https://source.unsplash.com/featured/600x400/?{$keyword}";
        $oldUrlFormat2 = "https://source.unsplash.com/featured/?{$keyword}";
        
        if (strpos($content, $oldUrlFormat1) !== false) {
            $content = str_replace("image_url: \"{$oldUrlFormat1}\"", "image_url: \"{$newUrl}\"", $content);
            echo "✓ Updated {$keyword} in {$filename}\n";
            $updatedCount++;
        } elseif (strpos($content, $oldUrlFormat2) !== false) {
            $content = str_replace("image_url: \"{$oldUrlFormat2}\"", "image_url: \"{$newUrl}\"", $content);
            echo "✓ Updated {$keyword} in {$filename}\n";
            $updatedCount++;
        }
    }
    
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
    }
}

echo "\n\nCompleted! Updated {$updatedCount} items\n";
?>
