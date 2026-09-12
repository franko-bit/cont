<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once 'backend/config.php';

// Simulate logged-in session
$_SESSION['user_id'] = 1;

// Get user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get certificates
$stmt = $pdo->prepare("
    SELECT c.*, a.title as exam_title, a.exam_language,
           CASE 
               WHEN a.title LIKE '%Academic%' THEN 'Academic English Exam'
               WHEN a.title LIKE '%Business%' THEN 'Business English Exam'
               ELSE a.title
           END as exam_type,
           u.full_name
    FROM certificates c
    LEFT JOIN institution_assessments a ON c.assessment_id = a.id
    LEFT JOIN users u ON c.user_id = u.id
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$allCerts = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

// Group by exam_type
$certificates = [];
$seenTypes = [];
foreach ($allCerts as $cert) {
    $examType = $cert['exam_type'] ?? $cert['exam_title'];
    if (!in_array($examType, $seenTypes)) {
        $certificates[] = $cert;
        $seenTypes[] = $examType;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Certificate Display Test</title>
    <style>
        :root {
            --sand: #f5ede6;
            --sage: #c6dfd5;
            --sage-mid: #8bbfad;
            --sage-dark: #3a7a68;
            --sage-deep: #225548;
            --border: rgba(0,0,0,0.07);
            --muted: #8a8178;
            --ink: #1a1a18;
            --surface: #faf8f5;
            --radius: 14px;
        }
        
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DM Sans', sans-serif; background: var(--sand); padding: 20px; }
        
        .container { max-width: 1000px; margin: 0 auto; }
        h1 { margin-bottom: 30px; font-size: 28px; }
        
        .cert-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-top: 16px;
        }
        
        .cert-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 18px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            min-height: 280px;
        }
        
        .cert-card-wrapper {
            position: relative;
            width: 100%;
            height: 140px;
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid var(--border);
        }
        
        .cert-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 0;
            border: none;
            background: #fff;
        }
        
        .cert-card-watermark {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0,0,0,0.05);
            z-index: 10;
            pointer-events: none;
        }
        
        .cert-card-watermark-text {
            font-size: 32px;
            font-weight: 900;
            color: rgba(255,0,0,0.3);
            text-transform: uppercase;
            letter-spacing: 3px;
            transform: rotate(-45deg);
            text-align: center;
            line-height: 1.2;
        }
        
        .cert-card h4 {
            font-size: 15px;
            color: var(--sage-deep);
            font-weight: 600;
        }
        
        .cert-meta {
            font-size: 12px;
            color: var(--muted);
            line-height: 1.6;
            flex: 1;
        }
        
        .cert-meta div {
            margin-bottom: 6px;
        }
        
        .cert-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        
        .btn-primary {
            background: var(--sage-dark);
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 40px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid var(--sage-mid);
            color: var(--sage-deep);
            padding: 7px 14px;
            border-radius: 40px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .status-verified {
            color: var(--sage-dark);
            font-weight: 600;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
            transition: background 0.15s;
            margin-top: 8px;
        }
        
        .status-verified:hover {
            background: rgba(58, 122, 104, 0.1);
        }
        
        .status-unverified {
            color: #c44;
            font-weight: 600;
            margin-top: 8px;
        }
        
        .info { 
            background: #f0f0f0; 
            padding: 15px; 
            border-radius: 8px; 
            margin-bottom: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎓 Your Certificates</h1>
        
        <div class="info">
            <strong>Logged in as:</strong> <?= htmlspecialchars($user['full_name']) ?> (ID: <?= $user['id'] ?>)<br>
            <strong>Total Certificates:</strong> <?= count($allCerts) ?><br>
            <strong>After Deduplication:</strong> <?= count($certificates) ?>
        </div>
        
        <?php if(count($certificates) > 0): ?>
            <div class="cert-grid">
                <?php foreach($certificates as $cert): ?>
                    <?php $cstatus = strtolower($cert['status'] ?? 'pending'); ?>
                    <?php $isVerified = ($cstatus === 'approved' || $cstatus === 'issued'); ?>
                    <?php $examKind = strtolower((string)($cert['exam_type'] ?? $cert['exam_title'] ?? '')); ?>
                    <?php $certImage = 'assets/images/Academic.svg'; ?>
                    <?php if (strpos($examKind, 'academic') !== false): ?>
                        <?php $certImage = 'assets/images/Academic.svg'; ?>
                    <?php elseif (strpos($examKind, 'business') !== false): ?>
                        <?php $certImage = 'assets/images/Business.svg'; ?>
                    <?php endif; ?>
                    <div class="cert-card">
                        <div class="cert-card-wrapper">
                            <img src="<?= htmlspecialchars($certImage) ?>" alt="Certificate">
                            <?php if (!$isVerified): ?>
                                <div class="cert-card-watermark">
                                    <div class="cert-card-watermark-text">Not Verified</div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <h4><?= htmlspecialchars($cert['exam_title'] ?? 'Verified Certificate') ?></h4>
                        <div class="cert-meta">
                            <div><strong>Holder:</strong> <?= htmlspecialchars($cert['student_name'] ?? $cert['full_name'] ?? 'Not Specified') ?></div>
                            <div><strong>Score:</strong> <?= htmlspecialchars($cert['score'] ?? '—') ?>%</div>
                            <div><strong>Issued:</strong> <?= date('M d, Y', strtotime($cert['created_at'])) ?></div>
                            <div><strong>Certificate ID:</strong></div>
                            <div style="font-family:monospace;font-size:11px;word-break:break-all;margin-top:2px"><?= htmlspecialchars($cert['certificate_id'] ?? 'N/A') ?></div>
                            <?php if ($isVerified): ?>
                                <div class="status-verified">✓ Verified</div>
                            <?php else: ?>
                                <div class="status-unverified">⚠ Not Verified</div>
                            <?php endif; ?>
                        </div>
                        <div class="cert-actions">
                            <button class="btn-primary">View Certificate</button>
                            <button class="btn-outline">Download</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: var(--muted);">No certificates found</p>
        <?php endif; ?>
    </div>
</body>
</html>
