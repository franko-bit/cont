<?php
require 'backend/config.php';

echo "=== EXAM TYPE SELECTION TEST ===\n\n";

// Check exam titles
echo "1. Available Exams:\n";
$stmt = $pdo->prepare('SELECT id, title FROM exams WHERE is_active = 1 LIMIT 10');
$stmt->execute();
foreach ($stmt->fetchAll() as $exam) {
    if (stripos($exam['title'], 'Academic') !== false) {
        $type = 'ACADEMIC';
    } elseif (stripos($exam['title'], 'Business') !== false) {
        $type = 'BUSINESS';
    } else {
        $type = 'UNKNOWN';
    }
    echo "   - ID {$exam['id']}: {$exam['title']} [$type]\n";
}

echo "\n2. Test YAML File Discovery:\n";

// Test Academic
$topic = 'AcademicEnglish';
$topic_clean = preg_replace('/[^a-zA-Z0-9]/', '', $topic ?? 'ENGLISH');
$yaml_filenames = [];
$yaml_filenames[] = $topic_clean . ".yaml";
$yaml_filenames[] = $topic_clean . ".yml";
$yaml_filenames[] = strtolower($topic_clean) . ".yaml";
if (stripos($topic, 'Business') === false) {
    $yaml_filenames[] = "BusinessEnglish.yaml";
} else {
    $yaml_filenames[] = "AcademicEnglish.yaml";
}
$yaml_filenames[] = "ENGLISH.yaml";

echo "   Topic: $topic\n";
echo "   Filenames to check (in order):\n";
foreach ($yaml_filenames as $i => $fn) {
    echo "      " . ($i+1) . ". $fn\n";
}

$base_path = "C:/xampp/htdocs/language-platform/content/EXAMS/";
echo "   First file found:\n";
foreach ($yaml_filenames as $fn) {
    $full_path = $base_path . $fn;
    if (file_exists($full_path)) {
        echo "      ✓ $fn (size: " . filesize($full_path) . " bytes)\n";
        break;
    }
}

// Test Business
echo "\n   Testing Business selection:\n";
$topic = 'BusinessEnglish';
$topic_clean = preg_replace('/[^a-zA-Z0-9]/', '', $topic ?? 'ENGLISH');
$yaml_filenames = [];
$yaml_filenames[] = $topic_clean . ".yaml";
$yaml_filenames[] = $topic_clean . ".yml";
$yaml_filenames[] = strtolower($topic_clean) . ".yaml";
if (stripos($topic, 'Business') === false) {
    $yaml_filenames[] = "BusinessEnglish.yaml";
} else {
    $yaml_filenames[] = "AcademicEnglish.yaml";
}
$yaml_filenames[] = "ENGLISH.yaml";

echo "   Topic: $topic\n";
echo "   Filenames to check (in order):\n";
foreach ($yaml_filenames as $i => $fn) {
    echo "      " . ($i+1) . ". $fn\n";
}

echo "   First file found:\n";
foreach ($yaml_filenames as $fn) {
    $full_path = $base_path . $fn;
    if (file_exists($full_path)) {
        echo "      ✓ $fn (size: " . filesize($full_path) . " bytes)\n";
        break;
    }
}

echo "\n3. Test exam_verification.php topic extraction:\n";
$titles = [
    'Academic English Exam - Level 1',
    'Business English Exam - Level 1',
    'French Exam - Level 1'
];

foreach ($titles as $title) {
    if (stripos($title, 'Business') !== false) {
        $extracted = 'BusinessEnglish';
    } elseif (stripos($title, 'Academic') !== false) {
        $extracted = 'AcademicEnglish';
    } else {
        $extracted = 'French';
    }
    echo "   Title: '$title' → $extracted\n";
}

echo "\n✓ All tests complete!\n";
?>
