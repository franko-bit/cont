<?php
session_start();
require_once '../backend/config.php';

if (!isset($_SESSION['user_id'])) {
    die("Not logged in");
}

// Define pair_folder_map (same as dashboard and lesson)
$pair_folder_map = [
    'en-rw:en' => 'EN-TO-RW',
    'en-rw:rw' => 'RW-TO-EN',
    'fr-rw:fr' => 'FR-TO-RW',
    'fr-rw:rw' => 'RW-TO-FR',
    'en-sw:en' => 'EN-TO-SW',
    'en-sw:sw' => 'SW-TO-EN',
    'fr-sw:fr' => 'FR-TO-SW',
    'fr-sw:sw' => 'SW-TO-FR',
];

// Define lang_pairs (same as dashboard)
$lang_pairs = [
    'en-rw' => [
        'code' => 'en-rw',
        'name' => 'English ↔ Kinyarwanda',
        'directions' => ['en', 'rw']
    ],
    'fr-rw' => [
        'code' => 'fr-rw',
        'name' => 'French ↔ Kinyarwanda',
        'directions' => ['fr', 'rw']
    ],
    'en-sw' => [
        'code' => 'en-sw',
        'name' => 'English ↔ Kiswahili',
        'directions' => ['en', 'sw']
    ],
    'fr-sw' => [
        'code' => 'fr-sw',
        'name' => 'French ↔ Kiswahili',
        'directions' => ['fr', 'sw']
    ]
];

$test_combinations = [
    ['pair' => 'fr-rw', 'direction' => 'rw'],
    ['pair' => 'fr-rw', 'direction' => 'fr'],
    ['pair' => 'fr-sw', 'direction' => 'fr'],
    ['pair' => 'fr-sw', 'direction' => 'sw'],
    ['pair' => 'en-rw', 'direction' => 'en'],
    ['pair' => 'en-rw', 'direction' => 'rw'],
    ['pair' => 'en-sw', 'direction' => 'en'],
    ['pair' => 'en-sw', 'direction' => 'sw'],
];

echo "<h1>Pair+Direction Mapping Debug</h1>";
echo "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse:collapse'>";
echo "<tr><th>Pair</th><th>Direction</th><th>Valid?</th><th>Mapped Folder</th><th>Folder Exists?</th></tr>";

foreach ($test_combinations as $test) {
    $pair = $test['pair'];
    $direction = $test['direction'];
    
    // Check if combination is valid
    $valid = isset($lang_pairs[$pair]) && in_array($direction, $lang_pairs[$pair]['directions']);
    
    // Get folder
    $key = $pair . ':' . $direction;
    $folder = $pair_folder_map[$key] ?? 'NOT MAPPED';
    
    // Check if folder exists
    $folder_exists = false;
    if ($folder !== 'NOT MAPPED') {
        $path = dirname(__FILE__) . "/../content/$folder";
        $folder_exists = is_dir($path);
    }
    
    $valid_text = $valid ? '<span style="color:green">✓ Yes</span>' : '<span style="color:red">✗ No</span>';
    $exists_text = $folder_exists ? '<span style="color:green">✓ Yes</span>' : '<span style="color:red">✗ No</span>';
    
    echo "<tr>";
    echo "<td>$pair</td>";
    echo "<td>$direction</td>";
    echo "<td>$valid_text</td>";
    echo "<td>$folder</td>";
    echo "<td>$exists_text</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>Test with GET parameters:</h2>";
echo "<p>Current URL parameters:</p>";
echo "<ul>";
echo "<li>pair: " . htmlspecialchars($_GET['pair'] ?? 'not set') . "</li>";
echo "<li>direction: " . htmlspecialchars($_GET['direction'] ?? 'not set') . "</li>";
echo "</ul>";

if (isset($_GET['pair']) && isset($_GET['direction'])) {
    $pair = $_GET['pair'];
    $direction = $_GET['direction'];
    $key = $pair . ':' . $direction;
    $folder = $pair_folder_map[$key] ?? null;
    
    echo "<h3>Mapping Result:</h3>";
    echo "<p><strong>Pair:</strong> " . htmlspecialchars($pair) . "</p>";
    echo "<p><strong>Direction:</strong> " . htmlspecialchars($direction) . "</p>";
    echo "<p><strong>Mapped Folder:</strong> " . htmlspecialchars($folder ?: 'NOT FOUND') . "</p>";
    
    if ($folder) {
        $path = dirname(__FILE__) . "/../content/$folder";
        echo "<p><strong>Folder Path:</strong> " . htmlspecialchars($path) . "</p>";
        echo "<p><strong>Folder Exists:</strong> " . (is_dir($path) ? '<span style="color:green">✓ Yes</span>' : '<span style="color:red">✗ No</span>') . "</p>";
        
        // List YAML files
        if (is_dir($path)) {
            echo "<h4>Available Levels:</h4>";
            for ($level = 1; $level <= 6; $level++) {
                $level_path = "$path/level$level";
                if (is_dir($level_path)) {
                    $files = glob("$level_path/*.yaml");
                    echo "<p><strong>Level $level:</strong> " . count($files) . " lesson(s)";
                    if (count($files) > 0) {
                        echo " (" . implode(", ", array_map('basename', array_slice($files, 0, 3))) . "...)";
                    }
                    echo "</p>";
                }
            }
        }
    }
}
?>
