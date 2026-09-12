<?php
/**
 * CLEAN AUDIO REGENERATION
 * Regenerates all audio files ensuring they contain ONLY the exact text
 * with no extra content added before or after
 */

require_once __DIR__ . '/config.php';

set_time_limit(0);
ignore_user_abort(true);

define('CONTENT_ROOT', __DIR__ . '/../content');
define('AUDIO_DIR', __DIR__ . '/../audio/swahili');
define('LOG_FILE', __DIR__ . '/../audio_regeneration_log.txt');
http://localhost/language-platform/backend/regenerate-clean-audio.php
// Initialize log
$logFile = fopen(LOG_FILE, 'w');

function logMessage($msg) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $line = "[$timestamp] $msg\n";
    fwrite($logFile, $line);
    echo $line;
    flush();
}

logMessage("=== CLEAN AUDIO REGENERATION STARTED ===");
logMessage("Audio Directory: " . AUDIO_DIR);

// Function to sanitize audio filename (same as in generate-audio.php)
function sanitizeAudioKey($text) {
    $text = trim((string)$text);
    if ($text === '') {
        return 'audio';
    }
    $text = preg_replace('/[^\p{L}\p{N}]+/u', '_', $text);
    $text = trim((string)$text, '_');
    $text = preg_replace('/_+/', '_', $text);
    return strtolower((string)$text);
}

// Function to generate audio via ElevenLabs
function generateAudioFile($text, $audioDir) {
    $key = sanitizeAudioKey($text);
    $audioFile = $key . '.mp3';
    $audioPath = $audioDir . '/' . $audioFile;

    $apiKey = defined('ELEVENLABS_API_KEY') ? ELEVENLABS_API_KEY : '';
    $voiceId = defined('ELEVENLABS_VOICE_ID') ? ELEVENLABS_VOICE_ID : '';

    if ($apiKey === '' || $voiceId === '') {
        return ['status' => 'missing-config', 'file' => $audioFile];
    }

    $endpoint = 'https://api.elevenlabs.io/v1/text-to-speech/' . rawurlencode($voiceId);

    // CLEAN PAYLOAD - just the text, nothing else
    $payload = [
        'text' => $text,
        'model_id' => 'eleven_multilingual_v2',
        'voice_settings' => [
            'stability' => 0.6,
            'similarity_boost' => 0.9,
            'style' => 0.4,
            'use_speaker_boost' => true
        ],
    ];

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'xi-api-key: ' . $apiKey,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false || $httpCode !== 200) {
        return [
            'status' => 'error',
            'file' => $audioFile,
            'message' => ($curlError ?: 'HTTP ' . $httpCode),
        ];
    }

    if (!file_put_contents($audioPath, $response)) {
        return [
            'status' => 'write-error',
            'file' => $audioFile,
        ];
    }

    // Brief delay to avoid rate limiting
    usleep(300000); // 0.3 seconds

    return [
        'status' => 'generated',
        'file' => $audioFile,
        'size' => filesize($audioPath),
    ];
}

// Parse YAML file - extract audio fields only
function extractAudioTexts($filePath) {
    $content = file_get_contents($filePath);
    $lines = explode("\n", $content);
    $texts = [];
    $inExercise = false;
    $exerciseId = 0;

    foreach ($lines as $lineNum => $line) {
        $trimmed = trim($line);
        
        // Skip empty lines and comments
        if ($trimmed === '' || strpos($trimmed, '#') === 0) {
            continue;
        }

        // Check for id marker
        if (preg_match('/^\s*-\s*id:\s*(\d+)/', $line, $m)) {
            $inExercise = true;
            $exerciseId = $m[1];
        }

        // Extract audio fields - handle both quoted and unquoted values
        if (preg_match('/^\s*(?:tts_text|audio|prompt):\s*(.+?)(?:\s*#|$)/i', $trimmed, $m)) {
            $text = trim($m[1]);
            
            // Remove quotes (both single and double)
            if ((strpos($text, '"') === 0 && strrpos($text, '"') > 0) ||
                (strpos($text, "'") === 0 && strrpos($text, "'") > 0)) {
                $text = substr($text, 1, -1);
            }
            
            // Clean up the text - remove extra spaces and comments
            $text = trim($text);
            // If there's a # inside quotes, that's fine, but strip trailing inline comments
            $text = preg_replace('/\s+#.*$/', '', $text);
            $text = trim($text);
            
            if (!empty($text) && !in_array($text, $texts)) {
                $texts[] = [
                    'text' => $text,
                    'exerciseId' => $exerciseId,
                    'file' => basename($filePath)
                ];
            }
        }
    }

    return $texts;
}

// Get all YAML files from both language pairs
$yamlFiles = array_merge(
    glob(CONTENT_ROOT . '/EN-TO-SW/level*//*.yaml'),
    glob(CONTENT_ROOT . '/FR-TO-SW/level*//*.yaml')
);

logMessage("Found " . count($yamlFiles) . " YAML files");

$allTexts = [];
$textsWithSources = [];

// Extract all audio texts
foreach ($yamlFiles as $yamlFile) {
    $extracted = extractAudioTexts($yamlFile);
    foreach ($extracted as $item) {
        $key = sanitizeAudioKey($item['text']);
        if (!isset($allTexts[$key])) {
            $allTexts[$key] = $item['text'];
            $textsWithSources[$key] = $item;
        }
    }
}

logMessage("Extracted " . count($allTexts) . " unique audio texts");

// Backup existing audio
$backupDir = AUDIO_DIR . '_backup_' . date('YmdHis');
if (is_dir(AUDIO_DIR)) {
    if (!mkdir($backupDir)) {
        logMessage("WARNING: Could not create backup directory");
    } else {
        logMessage("Backup directory created: " . $backupDir);
    }
}

// Delete all existing audio files
$existingFiles = glob(AUDIO_DIR . '/*.mp3');
foreach ($existingFiles as $file) {
    if (unlink($file)) {
        logMessage("Deleted: " . basename($file));
    }
}

logMessage("Deleted " . count($existingFiles) . " old audio files");

// Ensure audio directory exists
if (!is_dir(AUDIO_DIR)) {
    if (!mkdir(AUDIO_DIR, 0755, true)) {
        logMessage("ERROR: Could not create audio directory: " . AUDIO_DIR);
        fclose($logFile);
        exit(1);
    }
    logMessage("Created audio directory: " . AUDIO_DIR);
}

// Regenerate all audio files
$generated = 0;
$failed = 0;
$skipped = 0;

foreach ($allTexts as $key => $text) {
    logMessage("Generating: $key <- '$text'");
    $result = generateAudioFile($text, AUDIO_DIR);
    
    if ($result['status'] === 'generated') {
        $generated++;
        logMessage("  ✓ Success ({$result['size']} bytes)");
    } elseif ($result['status'] === 'cached') {
        $skipped++;
        logMessage("  - Skipped (cached)");
    } else {
        $failed++;
        logMessage("  ✗ Error: {$result['status']} - " . ($result['message'] ?? 'Unknown error'));
    }
}

logMessage("\n=== REGENERATION COMPLETE ===");
logMessage("Generated: $generated files");
logMessage("Failed: $failed files");
logMessage("Skipped: $skipped files");
logMessage("Total: " . ($generated + $failed + $skipped) . " / " . count($allTexts));

fclose($logFile);

echo "\n✓ Regeneration complete. Check audio_regeneration_log.txt for details.\n";
?>
