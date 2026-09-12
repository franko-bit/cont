<?php
/**
 * COMPREHENSIVE AUDIO FIELD AUDIT
 * Scans ALL EN-TO-SW content for audio, tts_text, and prompt fields
 * Generates audio for all unique texts
 */

define('CONTENT_ROOT', __DIR__ . '/../content');
define('AUDIO_DIR', __DIR__ . '/../audio/swahili');
define('LOG_FILE', __DIR__ . '/../audio_audit_results.txt');

require_once __DIR__ . '/config.php';

// Initialize results
$results = [
    'audit_time' => date('Y-m-d H:i:s'),
    'field_types' => [],
    'texts_by_field' => [],
    'total_unique_texts' => 0,
    'existing_files' => 0,
    'generated_files' => 0,
    'errors' => []
];

// Get all existing audio files
$existingFiles = array_map(function($f) { 
    return pathinfo($f, PATHINFO_FILENAME); 
}, glob(AUDIO_DIR . '/*.mp3'));

$results['existing_files'] = count($existingFiles);

// Helper function to sanitize audio filename
function sanitizeAudioKey($text) {
    $text = trim($text);
    $text = mb_strtolower($text, 'UTF-8');
    // Handle apostrophes specially
    $text = str_replace("'", "_", $text);
    // Replace spaces and special chars
    $text = preg_replace('/[^a-z0-9_]/u', '', $text);
    return $text;
}

// Recursively collect texts from YAML structure
function collectAllAudioTexts(&$structure, &$collected, $exerciseType = '') {
    if (is_array($structure)) {
        foreach ($structure as $key => $value) {
            // Check for audio-related fields
            if ($key === 'type' && in_array($value, ['speaking', 'pronunciation', 'listen_and_choose', 'listen_and_type', 'tap_hear'])) {
                $exerciseType = $value;
            }
            
            if ($key === 'audio' && !empty($value)) {
                if (!isset($collected['audio'])) {
                    $collected['audio'] = [];
                }
                if (!in_array($value, $collected['audio'])) {
                    $collected['audio'][] = $value;
                }
            }
            
            if ($key === 'tts_text' && !empty($value)) {
                if (!isset($collected['tts_text'])) {
                    $collected['tts_text'] = [];
                }
                if (!in_array($value, $collected['tts_text'])) {
                    $collected['tts_text'][] = $value;
                }
            }
            
            if ($key === 'prompt' && !empty($value)) {
                if (!isset($collected['prompt'])) {
                    $collected['prompt'] = [];
                }
                if (!in_array($value, $collected['prompt'])) {
                    $collected['prompt'][] = $value;
                }
            }
            
            // Recurse into nested structures
            if (is_array($value)) {
                collectAllAudioTexts($value, $collected, $exerciseType);
            }
        }
    }
}

// Parse YAML files (simple YAML parser)
function parseYamlFile($filePath) {
    $content = file_get_contents($filePath);
    $lines = explode("\n", $content);
    $data = [];
    $stack = [&$data];
    $lastKey = null;
    $lastLevel = 0;
    
    foreach ($lines as $lineNum => $line) {
        if (trim($line) === '' || strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Calculate indentation level
        $level = strlen($line) - strlen(ltrim($line));
        $indent = $level / 2;
        
        // Parse line
        if (preg_match('/^\s*-\s+(.+):\s+(.*)$/', $line, $m)) {
            // List item with key-value
            $key = trim($m[1]);
            $value = trim($m[2]);
            
            // Remove quotes if present
            if ((strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) ||
                (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1)) {
                $value = substr($value, 1, -1);
            }
            
            if (!isset($stack[0][$key])) {
                $stack[0][$key] = $value;
            }
        } elseif (preg_match('/^\s+(\w+):\s+(.*)$/', $line, $m)) {
            // Key-value pair
            $key = trim($m[1]);
            $value = trim($m[2]);
            
            // Remove quotes if present
            if ((strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) ||
                (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1)) {
                $value = substr($value, 1, -1);
            }
            
            if (!is_numeric($value)) {
                $stack[0][$key] = $value;
            }
        } elseif (preg_match('/^\s*-\s*$/', $line)) {
            // List item (array)
            $newItem = [];
            $stack[0][] = &$newItem;
            $stack = [&$newItem];
        }
    }
    
    return $data;
}

// Scan all EN-TO-SW YAML files
$allCollected = ['audio' => [], 'tts_text' => [], 'prompt' => []];
$levelFiles = glob(CONTENT_ROOT . '/EN-TO-SW/level*/*.yaml');

echo "Scanning " . count($levelFiles) . " YAML files...\n";

foreach ($levelFiles as $file) {
    echo "Processing: " . basename($file) . "\n";
    $content = file_get_contents($file);
    
    // Simple regex-based extraction for speed
    // Extract audio: "value"
    if (preg_match_all('/audio:\s*["\']?([^"\'\n]+)["\']?/', $content, $matches)) {
        foreach ($matches[1] as $text) {
            $text = trim($text);
            if (!empty($text) && !in_array($text, $allCollected['audio'])) {
                $allCollected['audio'][] = $text;
            }
        }
    }
    
    // Extract tts_text: "value"
    if (preg_match_all('/tts_text:\s*["\']?([^"\'\n]+)["\']?/', $content, $matches)) {
        foreach ($matches[1] as $text) {
            $text = trim($text);
            if (!empty($text) && !in_array($text, $allCollected['tts_text'])) {
                $allCollected['tts_text'][] = $text;
            }
        }
    }
    
    // Extract prompt: "value"
    if (preg_match_all('/prompt:\s*["\']?([^"\'\n]+)["\']?/', $content, $matches)) {
        foreach ($matches[1] as $text) {
            $text = trim($text);
            if (!empty($text) && !in_array($text, $allCollected['prompt'])) {
                $allCollected['prompt'][] = $text;
            }
        }
    }
}

// Count unique texts
foreach ($allCollected as $fieldType => $texts) {
    $results['field_types'][$fieldType] = count($texts);
    $results['total_unique_texts'] += count($texts);
}

echo "\n=== AUDIT RESULTS ===\n";
echo "Audio field texts: " . count($allCollected['audio']) . "\n";
echo "TTS text field texts: " . count($allCollected['tts_text']) . "\n";
echo "Prompt field texts: " . count($allCollected['prompt']) . "\n";
echo "Total unique texts: " . $results['total_unique_texts'] . "\n";
echo "Existing audio files: " . $results['existing_files'] . "\n";

// Generate audio files for all texts
$textsToGenerate = array_merge($allCollected['audio'], $allCollected['tts_text'], $allCollected['prompt']);
$uniqueTexts = array_unique($textsToGenerate);
sort($uniqueTexts);

echo "\nGenerating audio files for " . count($uniqueTexts) . " unique texts...\n";

$apiKey = defined('ELEVENLABS_API_KEY') ? ELEVENLABS_API_KEY : '';
$voiceId = defined('ELEVENLABS_VOICE_ID') ? ELEVENLABS_VOICE_ID : '';

if (!$apiKey || !$voiceId) {
    die("ERROR: ELEVENLABS_API_KEY or ELEVENLABS_VOICE_ID not defined in config.php\n");
}

$generated = 0;
$skipped = 0;

foreach ($uniqueTexts as $text) {
    $filename = sanitizeAudioKey($text) . '.mp3';
    $filePath = AUDIO_DIR . '/' . $filename;
    
    // Check if file already exists
    if (file_exists($filePath)) {
        $skipped++;
        continue;
    }
    
    // Generate audio
    echo "Generating: $text -> $filename ... ";
    
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.elevenlabs.io/v1/text-to-speech/$voiceId",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode([
            'text' => $text,
            'model_id' => 'eleven_multilingual_v2',
            'voice_settings' => [
                'stability' => 0.5,
                'similarity_boost' => 0.8,
            ]
        ]),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'xi-api-key: ' . $apiKey,
        ],
    ]);
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curlError = curl_error($curl);
    curl_close($curl);
    
    if ($httpCode === 200) {
        file_put_contents($filePath, $response);
        echo "OK\n";
        $generated++;
    } else {
        echo "FAILED (HTTP $httpCode)\n";
        $results['errors'][] = "$text: HTTP $httpCode";
        if ($curlError) {
            $results['errors'][] = "  Error: $curlError";
        }
    }
    
    // Rate limiting - ElevenLabs allows ~200 requests per minute
    usleep(300000); // 0.3 second delay
}

$results['generated_files'] = $generated;
$results['skipped_files'] = $skipped;

// Save results
$logContent = "=== COMPREHENSIVE AUDIO FIELD AUDIT ===\n";
$logContent .= "Audit Time: " . $results['audit_time'] . "\n";
$logContent .= "Total Existing Files: " . $results['existing_files'] . "\n\n";

$logContent .= "=== FIELD BREAKDOWN ===\n";
foreach ($results['field_types'] as $field => $count) {
    $logContent .= "$field: $count unique texts\n";
}

$logContent .= "\nTotal Unique Texts: " . $results['total_unique_texts'] . "\n";
$logContent .= "Files Generated: " . $results['generated_files'] . "\n";
$logContent .= "Files Skipped (already exist): " . $results['skipped_files'] . "\n";

if (!empty($results['errors'])) {
    $logContent .= "\n=== ERRORS ===\n";
    foreach ($results['errors'] as $error) {
        $logContent .= "$error\n";
    }
}

file_put_contents(LOG_FILE, $logContent);

echo "\n=== GENERATION COMPLETE ===\n";
echo "Generated: $generated\n";
echo "Skipped: $skipped\n";
echo "Results logged to: audio_audit_results.txt\n";

// Output unique text lists for reference
echo "\n=== UNIQUE TEXTS BY FIELD TYPE ===\n";
echo "\nAUDIO FIELD (" . count($allCollected['audio']) . " texts):\n";
foreach (array_slice($allCollected['audio'], 0, 10) as $text) {
    echo "  - $text\n";
}
if (count($allCollected['audio']) > 10) {
    echo "  ... and " . (count($allCollected['audio']) - 10) . " more\n";
}

echo "\nTTS_TEXT FIELD (" . count($allCollected['tts_text']) . " texts):\n";
foreach (array_slice($allCollected['tts_text'], 0, 10) as $text) {
    echo "  - $text\n";
}
if (count($allCollected['tts_text']) > 10) {
    echo "  ... and " . (count($allCollected['tts_text']) - 10) . " more\n";
}

echo "\nPROMPT FIELD (" . count($allCollected['prompt']) . " texts):\n";
foreach (array_slice($allCollected['prompt'], 0, 10) as $text) {
    echo "  - $text\n";
}
if (count($allCollected['prompt']) > 10) {
    echo "  ... and " . (count($allCollected['prompt']) - 10) . " more\n";
}

?>
