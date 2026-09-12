<?php
header('Content-Type: application/json');
try {
    require_once __DIR__ . '/config.php';
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Config error']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$assessment_id = isset($_POST['assessment_id']) ? intval($_POST['assessment_id']) : 0;
if (!$assessment_id) {
    echo json_encode(['success' => false, 'message' => 'Missing assessment id']);
    exit;
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Please upload a valid file']);
    exit;
}

$file = $_FILES['file'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$rows = [];

function deleteDirectory($dir) {
    if (!is_dir($dir)) {
        return;
    }
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($it as $item) {
        if ($item->isDir()) {
            rmdir($item->getRealPath());
        } else {
            unlink($item->getRealPath());
        }
    }
    rmdir($dir);
}

function findPowerShell() {
    if (stripos(PHP_OS, 'WIN') === false) {
        return null;
    }
    $output = [];
    $return = 1;
    exec('where powershell.exe 2>&1', $output, $return);
    if ($return === 0 && !empty($output)) {
        return trim($output[0]);
    }
    return null;
}

function parseXlsxFile($tmpName, &$error = null) {
    $sharedStringsXml = null;
    $sheetXml = null;

    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        if ($zip->open($tmpName) !== true) {
            $error = 'Unable to open XLSX file';
            return null;
        }
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
    } else {
        $extracted = false;
        $pharError = null;
        if (class_exists('Phar') || class_exists('PharData')) {
            $extractDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'xlsx_extract_' . uniqid();
            if (mkdir($extractDir, 0700, true)) {
                $tempZip = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'xlsx_temp_' . uniqid() . '.zip';
                if (copy($tmpName, $tempZip)) {
                    try {
                        if (class_exists('Phar')) {
                            $phar = new Phar($tempZip);
                        } else {
                            $phar = new PharData($tempZip);
                        }
                        $phar->extractTo($extractDir, null, true);
                        $sharedStringsPath = $extractDir . DIRECTORY_SEPARATOR . 'xl' . DIRECTORY_SEPARATOR . 'sharedStrings.xml';
                        $sheetPath = $extractDir . DIRECTORY_SEPARATOR . 'xl' . DIRECTORY_SEPARATOR . 'worksheets' . DIRECTORY_SEPARATOR . 'sheet1.xml';
                        if (file_exists($sharedStringsPath)) {
                            $sharedStringsXml = file_get_contents($sharedStringsPath);
                        }
                        if (file_exists($sheetPath)) {
                            $sheetXml = file_get_contents($sheetPath);
                        }
                        $extracted = true;
                    } catch (Exception $e) {
                        $pharError = $e->getMessage();
                    }
                }
                deleteDirectory($extractDir);
                @unlink($tempZip);
            }
        }

        if (!$sheetXml && ($powershell = findPowerShell()) !== null) {
            $extractDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'xlsx_extract_' . uniqid();
            if (!mkdir($extractDir, 0700, true)) {
                $error = 'Unable to create temporary extraction directory';
                return null;
            }
            $tempZip = $extractDir . '.zip';
            if (!copy($tmpName, $tempZip)) {
                deleteDirectory($extractDir);
                $error = 'Unable to copy XLSX file for PowerShell extraction';
                return null;
            }
            $quotedZip = escapeshellarg($tempZip);
            $quotedDest = escapeshellarg($extractDir);
            $cmd = "\"$powershell\" -NoProfile -Command \"Expand-Archive -Path $quotedZip -DestinationPath $quotedDest -Force\"";
            exec($cmd, $output, $return);
            if ($return === 0) {
                $sharedStringsPath = $extractDir . DIRECTORY_SEPARATOR . 'xl' . DIRECTORY_SEPARATOR . 'sharedStrings.xml';
                $sheetPath = $extractDir . DIRECTORY_SEPARATOR . 'xl' . DIRECTORY_SEPARATOR . 'worksheets' . DIRECTORY_SEPARATOR . 'sheet1.xml';
                if (file_exists($sharedStringsPath)) {
                    $sharedStringsXml = file_get_contents($sharedStringsPath);
                }
                if (file_exists($sheetPath)) {
                    $sheetXml = file_get_contents($sheetPath);
                }
                $extracted = true;
            }
            deleteDirectory($extractDir);
            @unlink($tempZip);
            if (!$extracted) {
                $error = 'Unable to extract XLSX file with PowerShell: ' . implode(' ', $output);
                return null;
            }
        }

        if (!$sheetXml) {
            if ($pharError) {
                $error = 'Unable to open XLSX file with Phar: ' . $pharError;
            } else {
                $error = 'XLSX upload requires ZipArchive, Phar, or PowerShell support to parse the file.';
            }
            return null;
        }
    }

    if (!$sheetXml) {
        $error = 'Spreadsheet file missing worksheet data (sheet1.xml)';
        return null;
    }

    $sharedStrings = [];
    if ($sharedStringsXml) {
        $sharedXml = simplexml_load_string($sharedStringsXml);
        if ($sharedXml) {
            foreach ($sharedXml->si as $si) {
                if (isset($si->t)) {
                    $sharedStrings[] = (string)$si->t;
                } else {
                    $text = '';
                    foreach ($si->r as $r) {
                        $text .= (string)$r->t;
                    }
                    $sharedStrings[] = $text;
                }
            }
        }
    }

    $xml = simplexml_load_string($sheetXml);
    $rowsRaw = [];
    foreach ($xml->sheetData->row as $row) {
        $cells = [];
        foreach ($row->c as $c) {
            $ref = (string)$c['r'];
            preg_match('/([A-Z]+)(\d+)/', $ref, $matches);
            $col = 0;
            if (!empty($matches[1])) {
                foreach (str_split($matches[1]) as $letter) {
                    $col = $col * 26 + (ord($letter) - 64);
                }
                $col -= 1;
            }
            $value = isset($c->v) ? (string)$c->v : '';
            if ((string)$c['t'] === 's') {
                $value = $sharedStrings[intval($value)] ?? $value;
            }
            $cells[$col] = $value;
        }
        if ($cells) {
            ksort($cells);
            $rowsRaw[] = array_values($cells);
        }
    }

    if (empty($rowsRaw)) {
        return [];
    }
    $header = array_map(function($h){ return strtolower(trim(preg_replace('/[^a-z0-9_]+/', '_', $h))); }, array_shift($rowsRaw));
    $rows = [];
    foreach ($rowsRaw as $row) {
        while (count($row) < count($header)) {
            $row[] = '';
        }
        $rows[] = array_combine($header, $row);
    }
    return $rows;
}

if ($ext === 'csv') {
    if (($handle = fopen($file['tmp_name'], 'r')) === false) {
        echo json_encode(['success' => false, 'message' => 'Unable to read uploaded CSV file']);
        exit;
    }
    $header = null;
    while (($row = fgetcsv($handle, 0, ',')) !== false) {
        if (!$header) {
            $header = array_map(function($h){ return strtolower(trim(preg_replace('/[^a-z0-9_]+/', '_', $h))); }, $row);
            continue;
        }
        if (!array_filter($row)) {
            continue;
        }
        $rows[] = array_combine($header, $row);
    }
    fclose($handle);
} elseif ($ext === 'xlsx') {
    $error = null;
    $rows = parseXlsxFile($file['tmp_name'], $error);
    if ($rows === null) {
        echo json_encode(['success' => false, 'message' => $error]);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unsupported file type. Use CSV or XLSX.']);
    exit;
}

if (empty($rows)) {
    echo json_encode(['success' => false, 'message' => 'No rows were parsed from the uploaded file']);
    exit;
}

$clean = [];
foreach ($rows as $row) {
    $row = array_change_key_case($row, CASE_LOWER);
    $name = trim($row['name'] ?? $row['full_name'] ?? $row['student'] ?? $row['student_name'] ?? '');
    $email = trim($row['email'] ?? $row['e_mail'] ?? $row['email_address'] ?? '');
    if (!$name || !$email) {
        continue;
    }
    $email = strtolower($email);
    $clean[$email] = ['name' => $name, 'email' => $email];
}

if (empty($clean)) {
    echo json_encode(['success' => false, 'message' => 'No valid name/email rows found in the file']);
    exit;
}

try {
    $pdo->beginTransaction();
    $inserted = 0;
    $updated = 0;
    foreach ($clean as $email => $record) {
        $stmt = $pdo->prepare('SELECT id FROM institution_candidates WHERE assessment_id = ? AND email = ? LIMIT 1');
        $stmt->execute([$assessment_id, $email]);
        $existing = $stmt->fetchColumn();
        if ($existing) {
            $stmt = $pdo->prepare('UPDATE institution_candidates SET full_name = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute([$record['name'], $existing]);
            $updated++;
        } else {
            $stmt = $pdo->prepare('INSERT INTO institution_candidates (institution_id, assessment_id, user_id, full_name, email, status, attempts, invited_at, updated_at) VALUES (?, ?, NULL, ?, ?, \'pending\', 0, NOW(), NOW())');
            $stmt->execute([1, $assessment_id, $record['name'], $record['email']]);
            $inserted++;
        }
    }
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => "Imported {$inserted} new students, updated {$updated} existing records.", 'inserted' => $inserted, 'updated' => $updated]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Import failed: ' . $e->getMessage()]);
}
