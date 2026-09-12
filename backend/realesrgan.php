<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'POST required']);
    exit;
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No image uploaded or upload error.']);
    exit;
}

$target = isset($_POST['target']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['target']) : 'unknown';
$allowedTargets = ['id', 'selfie'];
if (!in_array($target, $allowedTargets, true)) {
    $target = 'unknown';
}

$inputFile = $_FILES['image'];
$tmpName = $inputFile['tmp_name'];
if (!is_uploaded_file($tmpName)) {
    echo json_encode(['success' => false, 'message' => 'Invalid uploaded file.']);
    exit;
}

$enhancedDir = __DIR__ . '/enhanced';
if (!is_dir($enhancedDir) && !mkdir($enhancedDir, 0755, true)) {
    echo json_encode(['success' => false, 'message' => 'Unable to create enhancement directory.']);
    exit;
}

$timestamp = time();
$outputName = sprintf('realesrgan_%s_%s.jpg', $target, $timestamp);
$outputPath = $enhancedDir . '/' . $outputName;

$inputPath = tempnam(sys_get_temp_dir(), 'realesrgan_in_');
if ($inputPath === false) {
    echo json_encode(['success' => false, 'message' => 'Unable to create temporary file.']);
    exit;
}

$finalInputPath = $inputPath . '.jpg';
unlink($inputPath);
if (!move_uploaded_file($tmpName, $finalInputPath)) {
    echo json_encode(['success' => false, 'message' => 'Failed to store uploaded image.']);
    exit;
}
$inputPath = $finalInputPath;

$result = runRealESRGAN($inputPath, $outputPath);
unlink($inputPath);

if (!$result['success']) {
    echo json_encode(['success' => false, 'message' => $result['message'], 'debug' => $result['debug']]);
    exit;
}

$url = sprintf('backend/enhanced/%s', $outputName);
echo json_encode(['success' => true, 'data' => ['url' => $url, 'message' => 'Image enhanced successfully.']]);
exit;

function findPythonBinary() {
    $candidates = ['python', 'python3', 'py'];
    foreach ($candidates as $cmd) {
        $check = stripos(PHP_OS_FAMILY, 'Windows') === 0 ? "where $cmd" : "command -v $cmd";
        $output = null;
        $status = null;
        exec($check . ' 2>&1', $output, $status);
        if ($status === 0 && !empty($output[0])) {
            return trim($output[0]);
        }
    }
    return null;
}

function runRealESRGAN($inputPath, $outputPath) {
    $python = findPythonBinary();
    if (!$python) {
        return ['success' => false, 'message' => 'Python interpreter not found on server.', 'debug' => null];
    }

    $scriptPath = __DIR__ . '/realesrgan.py';
    if (!file_exists($scriptPath)) {
        return ['success' => false, 'message' => 'RealESRGAN Python helper script missing.', 'debug' => null];
    }

    $cmd = sprintf(
        '%s %s --input %s --output %s',
        escapeshellcmd($python),
        escapeshellarg($scriptPath),
        escapeshellarg($inputPath),
        escapeshellarg($outputPath)
    );

    $output = [];
    $status = null;
    exec($cmd . ' 2>&1', $output, $status);

    $debug = implode("\n", $output);
    if ($status !== 0 || !file_exists($outputPath)) {
        return ['success' => false, 'message' => 'RealESRGAN enhancement failed.', 'debug' => $debug];
    }

    return ['success' => true, 'message' => 'RealESRGAN enhancement completed.', 'debug' => $debug];
}
