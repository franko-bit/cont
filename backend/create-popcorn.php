<?php
require_once __DIR__ . "/config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Method not allowed');
}

$user_id = validateSession();
$input = json_decode(file_get_contents('php://input'), true);
$school_name = trim($input['school_name'] ?? '');
$quantity = isset($input['quantity']) ? intval($input['quantity']) : 0;
$notes = trim($input['notes'] ?? '');

if (!$school_name) {
    sendResponse(false, 'School name is required');
}
if ($quantity <= 0) {
    sendResponse(false, 'Popcorn quantity must be greater than zero');
}

$pdo = getPDO();

function normalizePrefix($schoolName) {
    $prefix = preg_replace('/[^A-Z0-9]/', '', strtoupper($schoolName));
    if ($prefix === '') {
        return 'SCHOOL';
    }
    return substr($prefix, 0, 20);
}

function ensurePopcornStructure($pdo) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS institution_popcorns (
        id INT AUTO_INCREMENT PRIMARY KEY,
        institution_id INT NOT NULL,
        created_by INT NULL,
        school_name VARCHAR(255) NOT NULL,
        school_prefix VARCHAR(50) NOT NULL,
        popcorn_code VARCHAR(100) NOT NULL UNIQUE,
        popcorn_count INT NOT NULL DEFAULT 1,
        notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE CASCADE,
        INDEX idx_institution_popcorns (institution_id),
        INDEX idx_school_name (school_name),
        INDEX idx_school_prefix (school_prefix)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Ensure school_prefix exists before attempting to add popcorn_code (which may be placed AFTER it)
    $columns = $pdo->query("SHOW COLUMNS FROM institution_popcorns LIKE 'school_prefix'")->fetch();
    if (!$columns) {
        $pdo->exec("ALTER TABLE institution_popcorns ADD COLUMN school_prefix VARCHAR(50) NOT NULL AFTER school_name");
    }
    $columns = $pdo->query("SHOW COLUMNS FROM institution_popcorns LIKE 'popcorn_code'")->fetch();
    if (!$columns) {
        // add popcorn_code after school_prefix (now guaranteed to exist)
        $pdo->exec("ALTER TABLE institution_popcorns ADD COLUMN popcorn_code VARCHAR(100) NOT NULL UNIQUE AFTER school_prefix");
    }
}

try {
    ensurePopcornStructure($pdo);

    $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $school_name));
    $stmt = $pdo->prepare('SELECT id, name FROM institutions WHERE LOWER(name) = ? OR slug = ? LIMIT 1');
    $stmt->execute([mb_strtolower($school_name), $slug]);
    $institution = $stmt->fetch();

    if (!$institution) {
        $stmt = $pdo->prepare('INSERT INTO institutions (name, slug, created_at, updated_at) VALUES (?, ?, NOW(), NOW())');
        $stmt->execute([$school_name, $slug]);
        $institution_id = (int)$pdo->lastInsertId();
    } else {
        $institution_id = (int)$institution['id'];
    }

    $schoolPrefix = normalizePrefix($school_name);
    $countStmt = $pdo->prepare('SELECT COALESCE(SUM(popcorn_count), 0) AS total FROM institution_popcorns WHERE UPPER(school_name) = ?');
    $countStmt->execute([mb_strtoupper($school_name)]);
    $existingTotal = (int)$countStmt->fetchColumn();
    $nextSequence = $existingTotal + 1;

    $insertStmt = $pdo->prepare('INSERT INTO institution_popcorns (institution_id, created_by, school_name, school_prefix, popcorn_code, popcorn_count, notes, created_at) VALUES (?, ?, ?, ?, ?, 1, ?, NOW())');
    $generatedCodes = [];
    for ($i = 0; $i < $quantity; $i++) {
        $code = sprintf('%s_%03d', $schoolPrefix, $nextSequence);
        $insertStmt->execute([$institution_id, $user_id, $school_name, $schoolPrefix, $code, $notes ?: null]);
        $generatedCodes[] = $code;
        $nextSequence++;
    }

    sendResponse(true, 'Popcorns generated successfully', [
        'institution_id' => $institution_id,
        'school_name' => $school_name,
        'quantity' => $quantity,
        'generated_codes' => $generatedCodes,
        'notes' => $notes,
        'created_at' => date('Y-m-d H:i:s'),
    ]);
} catch (Exception $e) {
    sendResponse(false, 'Failed to generate popcorns: ' . $e->getMessage());
}
