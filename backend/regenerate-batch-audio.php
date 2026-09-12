<?php
/**
 * BATCH AUDIO REGENERATION - Resume from last point
 * Generates audio files in smaller batches to avoid timeouts
 */

require_once __DIR__ . '/config.php';

set_time_limit(3600);
ignore_user_abort(true);

define('CONTENT_ROOT', __DIR__ . '/../content');
define('AUDIO_DIR', __DIR__ . '/../audio/swahili');
define('LOG_FILE', __DIR__ . '/../audio_regeneration_batch.txt');

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

logMessage("=== BATCH AUDIO REGENERATION STARTED ===");

// Sanitize audio filename
function sanitizeAudioKey($text) {
    $text = trim((string)$text);
    if ($text === '') return 'audio';
    $text = preg_replace('/[^\p{L}\p{N}]+/u', '_', $text);
    $text = trim((string)$text, '_');
    $text = preg_replace('/_+/', '_', $text);
    return strtolower((string)$text);
}

// Generate audio via ElevenLabs
function generateAudioFile($text, $audioDir) {
    $key = sanitizeAudioKey($text);
    $audioFile = $key . '.mp3';
    $audioPath = $audioDir . '/' . $audioFile;

    // Skip if already exists
    if (file_exists($audioPath)) {
        return ['status' => 'exists', 'file' => $audioFile];
    }

    $apiKey = defined('ELEVENLABS_API_KEY') ? ELEVENLABS_API_KEY : '';
    $voiceId = defined('ELEVENLABS_VOICE_ID') ? ELEVENLABS_VOICE_ID : '';

    if ($apiKey === '' || $voiceId === '') {
        return ['status' => 'missing-config', 'file' => $audioFile];
    }

    $endpoint = 'https://api.elevenlabs.io/v1/text-to-speech/' . rawurlencode($voiceId);

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

    usleep(300000); // 0.3 seconds - avoid rate limiting

    return [
        'status' => 'generated',
        'file' => $audioFile,
        'size' => filesize($audioPath),
    ];
}

// Extract audio texts from YAML
function extractAudioTexts($filePath) {
    $content = file_get_contents($filePath);
    $lines = explode("\n", $content);
    $texts = [];
    $exerciseId = 0;

    foreach ($lines as $line) {
        $trimmed = trim($line);
        
        if ($trimmed === '' || strpos($trimmed, '#') === 0) continue;

        if (preg_match('/^\s*-\s*id:\s*(\d+)/', $line, $m)) {
            $exerciseId = $m[1];
        }

        if (preg_match('/^\s*(?:tts_text|audio|prompt):\s*(.+?)(?:\s*#|$)/i', $trimmed, $m)) {
            $text = trim($m[1]);
            
            if ((strpos($text, '"') === 0 && strrpos($text, '"') > 0) ||
                (strpos($text, "'") === 0 && strrpos($text, "'") > 0)) {
                $text = substr($text, 1, -1);
            }
            
            $text = trim($text);
            $text = preg_replace('/\s+#.*$/', '', $text);
            $text = trim($text);
            
            if (!empty($text)) {
                $key = sanitizeAudioKey($text);
                if (!isset($texts[$key])) {
                    $texts[$key] = $text;
                }
            }
        }
    }

    return $texts;
}

// Find all YAML files
$yamlFiles = [];
foreach (glob(CONTENT_ROOT . '/*/level*/*.yaml') as $file) {
    $yamlFiles[] = $file;
}
logMessage("Found " . count($yamlFiles) . " YAML files");

// Extract unique texts
$allTexts = [];
foreach ($yamlFiles as $file) {
    $texts = extractAudioTexts($file);
    foreach ($texts as $key => $text) {
        $allTexts[$key] = $text;
    }
}
logMessage("Extracted " . count($allTexts) . " unique audio texts");

// Ensure audio directory exists
if (!is_dir(AUDIO_DIR)) {
    if (!mkdir(AUDIO_DIR, 0755, true)) {
        logMessage("ERROR: Could not create audio directory");
        fclose($logFile);
        exit(1);
    }
    logMessage("Created audio directory");
}

// Regenerate audio files
$generated = 0;
$skipped = 0;
$failed = 0;
$batchSize = 20; // Process 20 at a time
$count = 0;

foreach ($allTexts as $key => $text) {
    if (++$count % $batchSize === 0) {
        logMessage("Progress: $count / " . count($allTexts));
        gc_collect_cycles();
    }

    logMessage("Generating: $key <- '$text'");
    $result = generateAudioFile($text, AUDIO_DIR);
    
    if ($result['status'] === 'generated') {
        $generated++;
        logMessage("  ✓ Success (" . $result['size'] . " bytes)");
    } elseif ($result['status'] === 'exists') {
        $skipped++;
        logMessage("  - Skipped (already exists)");
    } else {
        $failed++;
        logMessage("  ✗ Error: " . $result['status'] . " - " . ($result['message'] ?? 'Unknown'));
    }
}

logMessage("\n=== BATCH REGENERATION COMPLETE ===");
logMessage("Generated: $generated files");
logMessage("Skipped: $skipped files");
logMessage("Failed: $failed files");
logMessage("Total: " . ($generated + $skipped + $failed) . " / " . count($allTexts));

$currentFiles = count(glob(AUDIO_DIR . '/*.mp3'));
logMessage("Current audio files on disk: $currentFiles");

fclose($logFile);
echo "\n✓ Batch regeneration complete.\n";
?>
