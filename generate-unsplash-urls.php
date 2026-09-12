<?php
/**
 * Unsplash URL Generator
 * Converts simple keywords to proper Unsplash API URLs with dimensions
 */

function generateUnsplashUrl($keyword) {
    // URL-encode the keyword
    $encoded_keyword = urlencode($keyword);
    
    // Generate Unsplash featured URL with dimensions for better consistency
    return "https://source.unsplash.com/featured/600x400/?{$encoded_keyword}";
}

function generateDirectUnsplashUrl($keyword) {
    // Format for direct API calls with better parameters
    // This uses the Unsplash source API which is more reliable
    $encoded_keyword = urlencode($keyword);
    
    return "https://images.unsplash.com/search/photos/{$encoded_keyword}?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixlib=rb-4.0.3&q=80&w=600&h=400&auto=format,compress";
}

// Keywords mapping for each YAML file
$keywords_mapping = [
    'level2/food.yaml' => [
        'bread', 'milk', 'cheese', 'meat', 'vegetables',
        'fruit', 'rice', 'soup', 'cooking', 'kitchen',
        'table', 'plate', 'drink', 'dessert', 'restaurant', 'grocery'
    ],
    'level2/house.yaml' => [
        'house', 'room', 'bedroom', 'kitchen', 'bathroom',
        'living room', 'door', 'window', 'furniture', 'bed'
    ],
    'level2/weather.yaml' => [
        'sun', 'rain', 'snow', 'cloud', 'wind',
        'storm', 'rainbow', 'temperature', 'weather', 'sky'
    ],
    'level3/directions.yaml' => [
        'map', 'north', 'south', 'east', 'west',
        'street', 'road', 'building', 'navigation', 'path'
    ],
    'level3/emotions.yaml' => [
        'happy', 'sad', 'angry', 'surprised', 'afraid',
        'love', 'confused', 'calm', 'excited', 'worried'
    ],
    'level3/restaurant.yaml' => [
        'restaurant', 'table', 'chair', 'coffee', 'beer',
        'water', 'juice', 'food', 'dish', 'cutlery', 'waiter'
    ],
    'level3/shopping.yaml' => [
        'market', 'money', 'shopping-bag', 'shoes', 'fruit',
        'vegetables', 'clothes', 'electronics', 'shopping', 'cart'
    ],
    'level3/travel.yaml' => [
        'airplane', 'bus', 'train', 'taxi', 'car',
        'motorcycle', 'bicycle', 'passport', 'ticket', 'luggage',
        'hotel', 'map', 'airport', 'journey', 'tourism'
    ],
    'level4/conditionals.yaml' => [
        'rain', 'studying', 'money', 'car', 'helping', 'bus'
    ],
    'level4/future-tense.yaml' => [
        'tomorrow', 'calendar', 'soon', 'clock', 'travel',
        'studying', 'buying', 'moving', 'planning', 'future'
    ],
    'level4/opinions.yaml' => [
        'happy', 'sad', 'agreement', 'disagreement', 'thinking', 'expression'
    ],
    'level5/business.yaml' => [
        'business', 'office', 'employee', 'meeting', 'profit',
        'contract', 'product', 'client', 'investment', 'teamwork'
    ],
    'level5/culture.yaml' => [
        'dance', 'music', 'storytelling', 'art', 'traditional-clothing',
        'community', 'ceremony', 'language', 'heritage', 'culture'
    ],
    'level5/days.yaml' => [
        'monday', 'sunday', 'morning', 'evening', 'night', 'clock'
    ],
    'level5/debates.yaml' => [
        'debate', 'opinion', 'agreement', 'disagreement', 'evidence',
        'fact', 'speaker', 'audience', 'discussion', 'conversation'
    ]
];

// Generate output
foreach ($keywords_mapping as $file => $keywords) {
    echo "=== $file ===\n";
    foreach ($keywords as $keyword) {
        echo "Keyword: $keyword\n";
        echo "  Featured: " . generateUnsplashUrl($keyword) . "\n";
        echo "  Direct: " . generateDirectUnsplashUrl($keyword) . "\n";
        echo "\n";
    }
    echo "\n";
}
?>
