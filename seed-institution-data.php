<?php
require_once 'backend/config.php';

$institution_id = 1;

// Insert sample institution if not exists
$stmt = $pdo->prepare("INSERT IGNORE INTO institutions (id, name, slug) VALUES (?, 'Sample University', 'sample-uni')");
$stmt->execute([$institution_id]);

// Insert sample assessments
$assessments = [
    [
        'title' => 'English Proficiency Certification',
        'invite_code' => 'CMU-EN-01',
        'invite_link' => 'https://playmates.test/invite/CMU-EN-01',
        'language_pair' => 'en-rw',
        'exam_level' => 4,
        'duration_minutes' => 50,
        'skills' => 'Reading, Writing, Listening',
        'passing_score' => 70,
        'assessment_model' => 'CMU English Proficiency Model',
        'total_questions' => 25
    ],
    [
        'title' => 'French Proficiency Certification',
        'invite_code' => 'CMU-FR-01',
        'invite_link' => 'https://playmates.test/invite/CMU-FR-01',
        'language_pair' => 'fr-rw',
        'exam_level' => 4,
        'duration_minutes' => 55,
        'skills' => 'Reading, Writing, Speaking',
        'passing_score' => 72,
        'assessment_model' => 'CMU French Proficiency Model',
        'total_questions' => 28
    ]
];

foreach ($assessments as $assessment) {
    $daysAgo = rand(1, 30);
    $createdAt = date('Y-m-d H:i:s', strtotime("-{$daysAgo} days"));
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO institution_assessments (
            institution_id, title, invite_code, invite_link, language_pair,
            exam_level, duration_minutes, skills, passing_score, assessment_model,
            total_questions, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $institution_id,
        $assessment['title'],
        $assessment['invite_code'],
        $assessment['invite_link'],
        $assessment['language_pair'],
        $assessment['exam_level'],
        $assessment['duration_minutes'],
        $assessment['skills'],
        $assessment['passing_score'],
        $assessment['assessment_model'],
        $assessment['total_questions'],
        $createdAt
    ]);
}

// Create sample users first, so candidate records can link to real users
$sampleUsers = [
    ['full_name' => 'Emma Watson', 'email' => 'emma@university.edu', 'school_name' => 'Sample High School'],
    ['full_name' => 'James Mwangi', 'email' => 'james@example.com', 'school_name' => 'Kigali College'],
    ['full_name' => 'Sofia Ramirez', 'email' => 'sofia@global.edu', 'school_name' => 'Mountain View Secondary'],
    ['full_name' => 'Liam Chen', 'email' => 'liam.chen@tech.org', 'school_name' => 'Rwanda Language Institute'],
    ['full_name' => 'Aisha Diallo', 'email' => 'aisha.d@africa.edu', 'school_name' => 'Sample High School'],
    ['full_name' => 'Oliver Schmidt', 'email' => 'oliver@europe.de', 'school_name' => 'Kigali College'],
    ['full_name' => 'Priya Kapoor', 'email' => 'priya@in.edu', 'school_name' => 'Mountain View Secondary'],
    ['full_name' => 'Marcus Johnson', 'email' => 'marcus@state.edu', 'school_name' => 'Rwanda Language Institute'],
    ['full_name' => 'Yuki Tanaka', 'email' => 'yuki@japan.edu', 'school_name' => 'Sample High School'],
    ['full_name' => 'Fatou Sy', 'email' => 'fatou@dakar.edu', 'school_name' => 'Kigali College'],
];

$usersByEmail = [];
foreach ($sampleUsers as $userData) {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$userData['email']]);
    $uid = $stmt->fetchColumn();
    if (!$uid) {
        $stmt = $pdo->prepare('INSERT INTO users (full_name, email, password, created_at, is_student, school_name) VALUES (?, ?, ?, NOW(), 1, ?)');
        $stmt->execute([$userData['full_name'], $userData['email'], password_hash('password123', PASSWORD_DEFAULT), $userData['school_name']]);
        $uid = $pdo->lastInsertId();
    }
    $usersByEmail[$userData['email']] = $uid;
}

$assessmentMap = [];
foreach ($assessments as $assessment) {
    $stmt = $pdo->prepare('SELECT id FROM institution_assessments WHERE invite_code = ? AND institution_id = ? LIMIT 1');
    $stmt->execute([$assessment['invite_code'], $institution_id]);
    $assessmentMap[$assessment['invite_code']] = $stmt->fetchColumn();
}

$candidates = [
    ['full_name' => 'Emma Watson', 'email' => 'emma@university.edu', 'school_name' => 'Sample High School', 'status' => 'completed', 'score' => 88, 'time_taken_seconds' => 2880, 'assessment_code' => 'CMU-EN-01', 'attempts' => 1],
    ['full_name' => 'James Mwangi', 'email' => 'james@example.com', 'school_name' => 'Kigali College', 'status' => 'pending', 'score' => null, 'time_taken_seconds' => null, 'assessment_code' => 'CMU-FR-01', 'attempts' => 0],
    ['full_name' => 'Sofia Ramirez', 'email' => 'sofia@global.edu', 'school_name' => 'Mountain View Secondary', 'status' => 'started', 'score' => null, 'time_taken_seconds' => 1320, 'assessment_code' => 'CMU-EN-01', 'attempts' => 1],
    ['full_name' => 'Liam Chen', 'email' => 'liam.chen@tech.org', 'school_name' => 'Rwanda Language Institute', 'status' => 'completed', 'score' => 94, 'time_taken_seconds' => 2460, 'assessment_code' => 'CMU-FR-01', 'attempts' => 1],
    ['full_name' => 'Aisha Diallo', 'email' => 'aisha.d@africa.edu', 'school_name' => 'Sample High School', 'status' => 'completed', 'score' => 71, 'time_taken_seconds' => 3480, 'assessment_code' => 'CMU-EN-01', 'attempts' => 2],
    ['full_name' => 'Oliver Schmidt', 'email' => 'oliver@europe.de', 'school_name' => 'Kigali College', 'status' => 'pending', 'score' => null, 'time_taken_seconds' => null, 'assessment_code' => 'CMU-FR-01', 'attempts' => 0],
    ['full_name' => 'Priya Kapoor', 'email' => 'priya@in.edu', 'school_name' => 'Mountain View Secondary', 'status' => 'completed', 'score' => 96, 'time_taken_seconds' => 2340, 'assessment_code' => 'CMU-EN-01', 'attempts' => 1],
    ['full_name' => 'Marcus Johnson', 'email' => 'marcus@state.edu', 'school_name' => 'Rwanda Language Institute', 'status' => 'completed', 'score' => 62, 'time_taken_seconds' => 3300, 'assessment_code' => 'CMU-EN-01', 'attempts' => 2],
    ['full_name' => 'Yuki Tanaka', 'email' => 'yuki@japan.edu', 'school_name' => 'Sample High School', 'status' => 'completed', 'score' => 79, 'time_taken_seconds' => 2640, 'assessment_code' => 'CMU-FR-01', 'attempts' => 1],
    ['full_name' => 'Fatou Sy', 'email' => 'fatou@dakar.edu', 'school_name' => 'Kigali College', 'status' => 'started', 'score' => null, 'time_taken_seconds' => 900, 'assessment_code' => 'CMU-FR-01', 'attempts' => 1],
];

foreach ($candidates as $candidate) {
    $assessmentId = $assessmentMap[$candidate['assessment_code']] ?? null;
    if (!$assessmentId) {
        continue;
    }
    $createdAt = date('Y-m-d H:i:s', strtotime('-' . rand(1, 30) . ' days'));
    $userId = $usersByEmail[$candidate['email']] ?? null;
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO institution_candidates (
            institution_id, assessment_id, user_id, full_name, email, status, score,
            time_taken_seconds, attempts, invited_at, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $institution_id,
        $assessmentId,
        $userId,
        $candidate['full_name'],
        $candidate['email'],
        $candidate['status'],
        $candidate['score'],
        $candidate['time_taken_seconds'],
        $candidate['attempts'],
        $createdAt,
        $createdAt,
        $createdAt,
    ]);
}

// Create exam attempts and certificates for completed candidates
foreach ($candidates as $candidate) {
    $email = $candidate['email'];
    $name = $candidate['full_name'];
    $schoolName = $candidate['school_name'] ?? 'Sample High School';
    // ensure user exists for each candidate sample
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $uid = $stmt->fetchColumn();
    if (!$uid) {
        $pdo->prepare('INSERT INTO users (full_name, email, password, created_at, is_student, school_name) VALUES (?, ?, ?, NOW(), 1, ?)')
            ->execute([$name, $email, password_hash('password123', PASSWORD_DEFAULT), $schoolName]);
        $uid = $pdo->lastInsertId();
    }

    $icStmt = $pdo->prepare('SELECT id FROM institution_candidates WHERE institution_id = ? AND email = ? LIMIT 1');
    $icStmt->execute([$institution_id, $email]);
    $icId = $icStmt->fetchColumn();
    if ($icId) {
        $update = $pdo->prepare('UPDATE institution_candidates SET user_id = ?, updated_at = NOW() WHERE id = ?');
        $update->execute([$uid, $icId]);
    }

    if ($candidate['status'] !== 'completed') {
        continue;
    }

    // create exam_attempt for this user linked to one of the exams if available
    if ($pdo->query("SHOW TABLES LIKE 'exams'")->fetchColumn()) {
        // pick a sample exam
        $examIdStmt = $pdo->query('SELECT id FROM exams ORDER BY id ASC LIMIT 1');
        $examId = $examIdStmt->fetchColumn();
        if ($examId) {
            $score = is_numeric($candidate['score']) ? $candidate['score'] : rand(50, 98);
            $passed = $score >= 70 ? 1 : 0;
            $attemptCheck = $pdo->prepare('SELECT id FROM exam_attempts WHERE user_id = ? AND exam_id = ? LIMIT 1');
            $attemptCheck->execute([$uid, $examId]);
            $attemptId = $attemptCheck->fetchColumn();
            if (!$attemptId) {
                $pdo->prepare('INSERT INTO exam_attempts (user_id, exam_id, status, started_at, submitted_at, graded_at, score, passing_score, passed, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())')
                    ->execute([$uid, $examId, 'graded', date('Y-m-d H:i:s', strtotime('-3 days')), date('Y-m-d H:i:s', strtotime('-3 days')), date('Y-m-d H:i:s', strtotime('-3 days')), $score, 70, $passed]);
                $attemptId = $pdo->lastInsertId();
            }

            // link to institution_candidates row
            $icStmt = $pdo->prepare('SELECT id FROM institution_candidates WHERE institution_id = ? AND email = ? LIMIT 1');
            $icStmt->execute([$institution_id, $email]);
            $icId = $icStmt->fetchColumn();
            if ($icId) {
                $update = $pdo->prepare('UPDATE institution_candidates SET exam_attempt_id = ?, user_id = ?, updated_at = NOW() WHERE id = ?');
                $update->execute([$attemptId, $uid, $icId]);
            }

            // create certificate if passed and certificates table exists
            if ($passed && $pdo->query("SHOW TABLES LIKE 'certificates'")->fetchColumn()) {
                $certId = strtoupper('CERT-' . substr(md5($email . time()), 0, 8));
                $cstmt = $pdo->prepare('SELECT id FROM certificates WHERE certificate_id = ? LIMIT 1');
                $cstmt->execute([$certId]);
                if (!$cstmt->fetchColumn()) {
                    // ensure student_name column exists
                    if (!$pdo->query("SHOW COLUMNS FROM certificates LIKE 'student_name'")->fetchColumn()) {
                        $pdo->exec("ALTER TABLE certificates ADD COLUMN student_name VARCHAR(255) NULL AFTER certificate_id");
                    }
                    $pdo->prepare('INSERT INTO certificates (certificate_id, student_name, user_id, exam_attempt_id, language_pair, level_number, score, passing_score, issued_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())')
                        ->execute([$certId, $name, $uid, $attemptId, 'en-rw', 4, $score, 70]);
                }
            }
        }
    }
}

// Add popcorn batches for four different schools to support dashboard school analytics
$popcornSchools = [
    ['school_name' => 'Sample High School', 'school_prefix' => 'SHS', 'popcorn_count' => 16, 'notes' => 'First batch for Sample High School'],
    ['school_name' => 'Kigali College', 'school_prefix' => 'KC', 'popcorn_count' => 12, 'notes' => 'Reward popcorn distribution for Kigali College'],
    ['school_name' => 'Mountain View Secondary', 'school_prefix' => 'MVS', 'popcorn_count' => 8, 'notes' => 'Extracurricular reward stash'],
    ['school_name' => 'Rwanda Language Institute', 'school_prefix' => 'RLI', 'popcorn_count' => 14, 'notes' => 'Certificate achievement reward'],
];

foreach ($popcornSchools as $school) {
    $stmt = $pdo->prepare('SELECT id FROM institution_popcorns WHERE institution_id = ? AND school_name = ? LIMIT 1');
    $stmt->execute([$institution_id, $school['school_name']]);
    if (!$stmt->fetchColumn()) {
        $pdo->prepare('INSERT INTO institution_popcorns (institution_id, created_by, school_name, school_prefix, popcorn_code, popcorn_count, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())')
            ->execute([$institution_id, null, $school['school_name'], $school['school_prefix'], 'POP-' . strtoupper(substr(md5($school['school_name']), 0, 6)), $school['popcorn_count'], $school['notes']]);
    }
}

// Create a sample user to satisfy certificate records
$sampleUserEmail = 'sample.student@example.com';
$sampleUserName = 'Ariane Nshuti';
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$sampleUserEmail]);
$sampleUserId = $stmt->fetchColumn();
if (!$sampleUserId) {
    $existingUserColumns = array_column($pdo->query('SHOW COLUMNS FROM users')->fetchAll(PDO::FETCH_ASSOC), 'Field');
    $insertFields = ['full_name', 'email', 'password', 'created_at'];
    $insertValues = [$sampleUserName, $sampleUserEmail, password_hash('password123', PASSWORD_DEFAULT), date('Y-m-d H:i:s')];
    if (in_array('last_login', $existingUserColumns, true)) {
        $insertFields[] = 'last_login';
        $insertValues[] = date('Y-m-d H:i:s');
    }
    if (in_array('current_streak', $existingUserColumns, true)) {
        $insertFields[] = 'current_streak';
        $insertValues[] = 0;
    }
    if (in_array('longest_streak', $existingUserColumns, true)) {
        $insertFields[] = 'longest_streak';
        $insertValues[] = 0;
    }
    if (in_array('total_xp', $existingUserColumns, true)) {
        $insertFields[] = 'total_xp';
        $insertValues[] = 0;
    }
    if (in_array('daily_goal', $existingUserColumns, true)) {
        $insertFields[] = 'daily_goal';
        $insertValues[] = 0;
    }
    if (in_array('is_student', $existingUserColumns, true)) {
        $insertFields[] = 'is_student';
        $insertValues[] = 1;
    }
    if (in_array('school_name', $existingUserColumns, true)) {
        $insertFields[] = 'school_name';
        $insertValues[] = 'Sample High School';
    }
    if (in_array('student_id', $existingUserColumns, true)) {
        $insertFields[] = 'student_id';
        $insertValues[] = 'SAMPLE-001';
    }
    $columnsSql = implode(', ', $insertFields);
    $placeholders = implode(', ', array_fill(0, count($insertFields), '?'));
    $stmt = $pdo->prepare("INSERT INTO users ({$columnsSql}) VALUES ({$placeholders})");
    $stmt->execute($insertValues);
    $sampleUserId = $pdo->lastInsertId();
}

if ($sampleUserId) {
    $stmt = $pdo->prepare('SELECT id FROM institution_users WHERE institution_id = ? AND user_id = ? LIMIT 1');
    $stmt->execute([$institution_id, $sampleUserId]);
    if (!$stmt->fetchColumn()) {
        $pdo->prepare('INSERT IGNORE INTO institution_users (institution_id, user_id, role, joined_at) VALUES (?, ?, ?, NOW())')->execute([$institution_id, $sampleUserId, 'admin']);
    }
}

$sampleExamId = null;
if ($pdo->query("SHOW TABLES LIKE 'exams'")->fetchColumn()) {
    $stmt = $pdo->prepare('SELECT id FROM exams WHERE title = ? LIMIT 1');
    $stmt->execute(['Sample English Proficiency Exam']);
    $sampleExamId = $stmt->fetchColumn();
    if (!$sampleExamId) {
        $stmt = $pdo->prepare('INSERT INTO exams (language_pair, level_number, title, description, exam_language, duration_minutes, passing_score, total_questions, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute(['en-rw', 4, 'Sample English Proficiency Exam', 'A seeded sample exam for institution dashboard testing.', 'en', 60, 70, 20]);
        $sampleExamId = $pdo->lastInsertId();
    }
}

$assessmentId = null;
$stmt = $pdo->prepare('SELECT id FROM institution_assessments WHERE invite_code = ? AND institution_id = ? LIMIT 1');
$stmt->execute(['CMU-EN-01', $institution_id]);
$assessmentId = $stmt->fetchColumn();
if (!$assessmentId) {
    $stmt = $pdo->prepare('SELECT id FROM institution_assessments WHERE institution_id = ? ORDER BY created_at DESC LIMIT 1');
    $stmt->execute([$institution_id]);
    $assessmentId = $stmt->fetchColumn();
}

if ($assessmentId) {
    $sampleCandidateEmail = 'ariane.nshuti@example.com';
    $stmt = $pdo->prepare('SELECT id FROM institution_candidates WHERE institution_id = ? AND email = ? LIMIT 1');
    $stmt->execute([$institution_id, $sampleCandidateEmail]);
    $sampleCandidateId = $stmt->fetchColumn();
    if (!$sampleCandidateId) {
        $stmt = $pdo->prepare('INSERT IGNORE INTO institution_candidates (institution_id, assessment_id, user_id, full_name, email, status, score, time_taken_seconds, attempts, invited_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
        $stmt->execute([$institution_id, $assessmentId, $sampleUserId, 'Ariane Nshuti', $sampleCandidateEmail, 'completed', 92.4, 3480, 1]);
        $sampleCandidateId = $pdo->lastInsertId();
    }

    $sampleAttemptId = null;
    if ($sampleUserId && $sampleExamId) {
        $stmt = $pdo->prepare('SELECT id FROM exam_attempts WHERE user_id = ? AND exam_id = ? AND status = ? LIMIT 1');
        $stmt->execute([$sampleUserId, $sampleExamId, 'graded']);
        $sampleAttemptId = $stmt->fetchColumn();
        if (!$sampleAttemptId) {
            $stmt = $pdo->prepare('INSERT INTO exam_attempts (user_id, exam_id, status, started_at, submitted_at, graded_at, score, passing_score, passed, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
            $stmt->execute([$sampleUserId, $sampleExamId, 'graded', date('Y-m-d H:i:s', strtotime('-2 days')), date('Y-m-d H:i:s', strtotime('-2 days')), date('Y-m-d H:i:s', strtotime('-2 days')), 92.4, 70, 1]);
            $sampleAttemptId = $pdo->lastInsertId();
        }
    }

    if ($sampleCandidateId && $sampleAttemptId) {
            $setClauses = ['exam_attempt_id = ?'];
            $params = [$sampleAttemptId];
            if ($pdo->query("SHOW COLUMNS FROM institution_candidates LIKE 'cert_status'")->fetchColumn()) {
                $setClauses[] = 'cert_status = ?';
                $params[] = 'issued';
            }
            if ($pdo->query("SHOW COLUMNS FROM institution_candidates LIKE 'cert_issued_at'")->fetchColumn()) {
                $setClauses[] = 'cert_issued_at = NOW()';
            }
            $params[] = $sampleCandidateId;
            $pdo->prepare('UPDATE institution_candidates SET ' . implode(', ', $setClauses) . ' WHERE id = ?')->execute($params);

            if ($pdo->query("SHOW TABLES LIKE 'certificates'")->fetchColumn()) {
                if (!$pdo->query("SHOW COLUMNS FROM certificates LIKE 'student_name'")->fetchColumn()) {
                    $pdo->exec("ALTER TABLE certificates ADD COLUMN student_name VARCHAR(255) NULL AFTER certificate_id");
                }
                $sampleCertificateId = 'SAMPLE-CERT-2026';
                $stmt = $pdo->prepare('SELECT id FROM certificates WHERE certificate_id = ? LIMIT 1');
                $stmt->execute([$sampleCertificateId]);
                if (!$stmt->fetchColumn()) {
                    $stmt = $pdo->prepare('INSERT INTO certificates (certificate_id, student_name, user_id, exam_attempt_id, language_pair, level_number, score, passing_score, issued_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
                    $stmt->execute([$sampleCertificateId, 'Ariane Nshuti', $sampleUserId, $sampleAttemptId, 'en-rw', 4, 92.4, 70]);
                }
        }
    }
}

if ($pdo->query("SHOW TABLES LIKE 'institution_popcorns'")->fetchColumn()) {
    $stmt = $pdo->prepare('SELECT id FROM institution_popcorns WHERE popcorn_code = ? LIMIT 1');
    $stmt->execute(['SAMPLE-POP-001']);
    if (!$stmt->fetchColumn()) {
        $stmt = $pdo->prepare('INSERT INTO institution_popcorns (institution_id, created_by, school_name, school_prefix, popcorn_code, popcorn_count, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$institution_id, $sampleUserId, 'Sample High School', 'SHS', 'SAMPLE-POP-001', 1, 'Seeded popcorn award for sample institution dashboard data.']);
    }
}

echo "Sample data inserted successfully!\n";
?>