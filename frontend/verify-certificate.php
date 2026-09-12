<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../backend/config.php';

$cert_id = $_GET['cert_id'] ?? $_GET['certificate_id'] ?? '';
$download = isset($_GET['download']) ? true : false;

if (!$cert_id) {
    die('Certificate ID is required');
}

// Fetch certificate by certificate_id (not by database id)
$stmt = $pdo->prepare("
    SELECT c.*, 
           COALESCE(NULLIF(TRIM(e.title), ''), NULLIF(TRIM(a.title), ''), 'Verified Certificate') AS exam_title,
           COALESCE(NULLIF(TRIM(c.student_name), ''), NULLIF(TRIM(u.full_name), ''), 'Certificate Holder') AS holder_name,
           u.email
    FROM certificates c
    LEFT JOIN exams e ON e.id = c.exam_id
    LEFT JOIN institution_assessments a ON c.assessment_id = a.id
    LEFT JOIN users u ON c.user_id = u.id
    WHERE c.certificate_id = ?
");
$stmt->execute([$cert_id]);
$cert = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cert) {
    die('Certificate not found or invalid');
}

$displayExamTitle = trim((string)($cert['exam_title'] ?? 'Verified Certificate'));
$holderName = trim((string)($cert['holder_name'] ?? 'Certificate Holder'));
$examKind = strtolower($displayExamTitle);
$certImage = SITE_URL . '/assets/images/Academic.svg';
if (strpos($examKind, 'academic') !== false) {
    $certImage = SITE_URL . '/assets/images/Academic.svg';
} elseif (strpos($examKind, 'business') !== false) {
    $certImage = SITE_URL . '/assets/images/Business.svg';
}

// Check verification status
$cstatus = strtolower($cert['status'] ?? 'pending');
$isVerified = ($cstatus === 'approved' || $cstatus === 'issued');

// If download is requested, generate PDF (for now, just show message)
if ($download) {
    // TODO: Implement PDF generation
    // For now, just redirect to display view
    header('Location: verify-certificate.php?cert_id=' . urlencode($cert_id));
    exit;
}

// Display certificate verification page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Verification - <?= htmlspecialchars($cert['exam_title'] ?? 'Playmates') ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --sage-deep: #225548;
            --sage-dark: #3a7a68;
            --sage-mid: #8bbfad;
            --sage: #c6dfd5;
            --border: rgba(0,0,0,0.07);
            --muted: #8a8178;
            --ink: #1a1a18;
            --surface: #faf8f5;
            --sand: #f5ede6;
        }
        
        body {
            font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, var(--sage) 0%, var(--sand) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            max-width: 900px;
            width: 100%;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.12);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, var(--sage-deep) 0%, var(--sage-dark) 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
            font-family: 'DM Serif Display', serif;
        }
        
        .header p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .status-badge {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
            margin-top: 15px;
        }
        
        .status-verified {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid white;
        }
        
        .status-unverified {
            background: rgba(255, 100, 100, 0.2);
            color: white;
            border: 2px solid white;
        }
        
        .content {
            padding: 50px 40px;
        }
        
        .cert-display {
            position: relative;
            margin-bottom: 40px;
            border: 2px solid var(--border);
            border-radius: 15px;
            overflow: hidden;
            background: white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }

        .certificate-preview {
            position: relative;
            width: 100%;
        }

        .certificate-bg {
            width: 100%;
            display: block;
            object-fit: contain;
        }

        .certificate-overlay {
            position: absolute;
            top: 38%;
            left: 0;
            right: 0;
            display: flex;
            justify-content: center;
            pointer-events: none;
        }

        .certificate-name {
            color: #1B3D25;
            font-size: 1.6rem;
            font-weight: 700;
            text-align: center;
            text-shadow: 0 2px 8px rgba(255,255,255,0.9);
            padding: 0 18px;
        }
        
        .cert-watermark {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            pointer-events: none;
        }
        
        .cert-watermark-text {
            font-size: 60px;
            font-weight: 900;
            color: rgba(255, 100, 100, 0.15);
            text-transform: uppercase;
            letter-spacing: 8px;
            transform: rotate(-45deg);
            text-align: center;
            line-height: 1.2;
        }
        
        .details {
            background: var(--surface);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid var(--border);
        }
        
        .details-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 20px;
        }
        
        .details-row:last-child {
            margin-bottom: 0;
        }
        
        .detail-item {
            border-bottom: 1px solid var(--border);
            padding-bottom: 15px;
        }
        
        .detail-item:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--muted);
            letter-spacing: 0.8px;
            margin-bottom: 8px;
        }
        
        .detail-value {
            font-size: 16px;
            color: var(--ink);
            font-weight: 500;
        }
        
        .detail-value.cert-id {
            font-family: monospace;
            font-size: 14px;
            word-break: break-all;
        }
        
        .verification-info {
            background: linear-gradient(135deg, rgba(34, 85, 72, 0.05) 0%, rgba(198, 223, 213, 0.1) 100%);
            border: 2px solid var(--sage);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .verification-info.unverified {
            background: linear-gradient(135deg, rgba(204, 68, 68, 0.05) 0%, rgba(255, 180, 180, 0.1) 100%);
            border-color: #ff8888;
        }
        
        .verification-info h3 {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--sage-deep);
        }
        
        .verification-info.unverified h3 {
            color: #c44;
        }
        
        .verification-info p {
            font-size: 14px;
            color: var(--muted);
            line-height: 1.6;
        }
        
        .actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 28px;
            border-radius: 50px;
            border: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: var(--sage-deep);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--sage-dark);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(34, 85, 72, 0.2);
        }
        
        .btn-secondary {
            background: var(--surface);
            color: var(--sage-deep);
            border: 2px solid var(--sage);
        }
        
        .btn-secondary:hover {
            background: var(--sage);
        }
        
        @media (max-width: 768px) {
            .header {
                padding: 30px 20px;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .content {
                padding: 30px 20px;
            }
            
            .details {
                padding: 20px;
            }
            
            .details-row {
                grid-template-columns: 1fr;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?= $isVerified ? '✓ Certificate Verified' : '⚠ Certificate Not Verified' ?></h1>
            <p><?= htmlspecialchars($cert['exam_title'] ?? 'Verified Certificate') ?></p>
            <div class="status-badge <?= $isVerified ? 'status-verified' : 'status-unverified' ?>">
                <?= $isVerified ? 'VERIFIED & APPROVED' : 'NOT VERIFIED' ?>
            </div>
        </div>
        
        <div class="content">
            <?php if (!$isVerified): ?>
            <div class="verification-info unverified">
                <h3>⚠ This Certificate is Not Verified</h3>
                <p>This certificate has not yet been verified and approved by the institution. It cannot be used as proof of completion until it is officially approved. Please check back later or contact your institution administrator.</p>
            </div>
            <?php else: ?>
            <div class="verification-info">
                <h3>✓ This Certificate is Verified & Approved</h3>
                <p>This is a valid and verified certificate of achievement. It has been approved by the institution and can be used as proof of completion of the course.</p>
            </div>
            <?php endif; ?>
            
            <div class="cert-display">
                <div class="certificate-preview">
                    <img src="<?= htmlspecialchars($certImage) ?>" alt="Certificate" class="certificate-bg">
                    <div class="certificate-overlay">
                        <div class="certificate-name"><?= htmlspecialchars($holderName !== '' ? $holderName : 'Certificate Holder') ?></div>
                    </div>
                </div>
                <?php if (!$isVerified): ?>
                <div class="cert-watermark">
                    <div class="cert-watermark-text">Not Verified</div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="details">
                <h2 style="font-size: 18px; font-weight: 600; margin-bottom: 25px; color: var(--ink);">Certificate Details</h2>
                
                <div class="details-row">
                    <div class="detail-item">
                        <div class="detail-label">Exam/Course</div>
                        <div class="detail-value"><?= htmlspecialchars($displayExamTitle) ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Score Achieved</div>
                        <div class="detail-value"><?= htmlspecialchars($cert['score'] ?? '—') ?>%</div>
                    </div>
                </div>
                
                <div class="details-row">
                    <div class="detail-item">
                        <div class="detail-label">Student Name</div>
                        <div class="detail-value"><?= htmlspecialchars($holderName !== '' ? $holderName : 'Not Provided') ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Date Issued</div>
                        <div class="detail-value"><?= date('F d, Y', strtotime($cert['created_at'])) ?></div>
                    </div>
                </div>
                
                <div class="details-row">
                    <div class="detail-item" style="grid-column: 1 / -1;">
                        <div class="detail-label">Certificate ID (for verification)</div>
                        <div class="detail-value cert-id"><?= htmlspecialchars($cert['certificate_id']) ?></div>
                    </div>
                </div>
                
                <div class="details-row">
                    <div class="detail-item">
                        <div class="detail-label">Status</div>
                        <div class="detail-value" style="color: <?= $isVerified ? 'var(--sage-dark)' : '#c44' ?>">
                            <?= $isVerified ? '✓ Verified & Approved' : '⚠ Pending Verification' ?>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Verification Date</div>
                        <div class="detail-value">
                            <?= $isVerified && $cert['verified_at'] ? date('F d, Y', strtotime($cert['verified_at'])) : 'Pending' ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="actions">
                <button class="btn btn-primary" onclick="window.print()">🖨 Print Certificate</button>
                <button class="btn btn-secondary" onclick="window.history.back()">← Back</button>
            </div>
        </div>
    </div>
</body>
</html>
