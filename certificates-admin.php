<?php
/**
 * Certificate Management Page
 * Dedicated interface for viewing, approving, and declining certificates
 */
require_once __DIR__ . '/backend/config.php';

if (empty($_SESSION['user_id'])) {
  header('Location: ' . SITE_URL . '/frontend/signin.php?redirect_to=' . urlencode('/language-platform/certificates-admin.php'));
  exit;
}

$institutionId = isset($_SESSION['institution_id']) ? (int)$_SESSION['institution_id'] : 1;
$pdo = getPDO();

// Handle approve/decline actions
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $certificateId = $_POST['certificate_id'] ?? '';
    
    if ($action === 'approve') {
        try {
            $stmt = $pdo->prepare("UPDATE certificates SET status = 'approved' WHERE certificate_id = ?");
            $stmt->execute([$certificateId]);
            $message = '<div style="background: #d4edda; padding: 12px; border-radius: 6px; margin-bottom: 16px; color: #155724;">✓ Certificate approved</div>';
        } catch (Exception $e) {
            $message = '<div style="background: #f8d7da; padding: 12px; border-radius: 6px; margin-bottom: 16px; color: #721c24;">✗ Error: ' . $e->getMessage() . '</div>';
        }
    } elseif ($action === 'decline') {
        try {
            $stmt = $pdo->prepare("DELETE FROM certificates WHERE certificate_id = ?");
            $stmt->execute([$certificateId]);
            $message = '<div style="background: #d4edda; padding: 12px; border-radius: 6px; margin-bottom: 16px; color: #155724;">✓ Certificate declined and removed</div>';
        } catch (Exception $e) {
            $message = '<div style="background: #f8d7da; padding: 12px; border-radius: 6px; margin-bottom: 16px; color: #721c24;">✗ Error: ' . $e->getMessage() . '</div>';
        }
    }
}

// Get auto-approved certificates (successfully completed exams)
$cr = $pdo->prepare(
  'SELECT c.id, c.certificate_id, c.student_name, c.score, c.passing_score, c.issued_at, c.exam_attempt_id, c.status,
          ic.id AS candidate_id, ic.full_name AS candidate_name, ic.assessment_id, ia.title AS assessment_title, i.name AS school_name
   FROM certificates c
   INNER JOIN institution_candidates ic ON ic.exam_attempt_id = c.exam_attempt_id
   LEFT JOIN institution_assessments ia ON ia.id = ic.assessment_id
   LEFT JOIN institutions i ON i.id = ic.institution_id
   WHERE ic.institution_id = ? AND c.status = "approved"
   ORDER BY c.issued_at DESC
   LIMIT 100'
);
$cr->execute([$institutionId]);
$approvedCerts = $cr->fetchAll(PDO::FETCH_ASSOC);

// Get auto-declined certificates (failed exams)
$dr = $pdo->prepare(
  'SELECT c.id, c.certificate_id, c.student_name, c.score, c.passing_score, c.issued_at, c.exam_attempt_id, c.status,
          ic.id AS candidate_id, ic.full_name AS candidate_name, ic.assessment_id, ia.title AS assessment_title, i.name AS school_name
   FROM certificates c
   INNER JOIN institution_candidates ic ON ic.exam_attempt_id = c.exam_attempt_id
   LEFT JOIN institution_assessments ia ON ia.id = ic.assessment_id
   LEFT JOIN institutions i ON i.id = ic.institution_id
   WHERE ic.institution_id = ? AND c.status = "declined"
   ORDER BY c.issued_at DESC
   LIMIT 100'
);
$dr->execute([$institutionId]);
$declinedCerts = $dr->fetchAll(PDO::FETCH_ASSOC);

// Get any legacy pending certificates (if they exist)
$pr = $pdo->prepare(
  'SELECT c.id, c.certificate_id, c.student_name, c.score, c.passing_score, c.issued_at, c.exam_attempt_id, c.status,
          ic.id AS candidate_id, ic.full_name AS candidate_name, ic.assessment_id, ia.title AS assessment_title, i.name AS school_name
   FROM certificates c
   INNER JOIN institution_candidates ic ON ic.exam_attempt_id = c.exam_attempt_id
   LEFT JOIN institution_assessments ia ON ia.id = ic.assessment_id
   LEFT JOIN institutions i ON i.id = ic.institution_id
   WHERE ic.institution_id = ? AND (c.status IS NULL OR c.status = "pending")
   ORDER BY c.issued_at DESC
   LIMIT 100'
);
$pr->execute([$institutionId]);
$pendingCerts = $pr->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Certificate Management</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f7fa; color: #333; }
    .container { max-width: 1000px; margin: 0 auto; padding: 24px; }
    header { margin-bottom: 32px; }
    h1 { font-size: 32px; margin-bottom: 8px; }
    .subtitle { color: #666; font-size: 14px; }
    
    .tabs { display: flex; gap: 16px; margin-bottom: 24px; border-bottom: 1px solid #e0e0e0; }
    .tab-btn { padding: 12px 16px; background: none; border: none; cursor: pointer; border-bottom: 3px solid transparent; color: #666; font-size: 14px; font-weight: 500; transition: all 0.2s; }
    .tab-btn.active { color: #007bff; border-bottom-color: #007bff; }
    .tab-content { display: none; }
    .tab-content.active { display: block; }
    
    .cert-list { display: grid; gap: 12px; }
    .cert-item { background: white; padding: 16px; border-radius: 8px; border-left: 4px solid #007bff; }
    .cert-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px; }
    .cert-name { font-size: 18px; font-weight: 600; }
    .cert-badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; }
    .cert-badge.pending { background: #fff3cd; color: #856404; }
    .cert-badge.approved { background: #d4edda; color: #155724; }
    .cert-badge.declined { background: #f8d7da; color: #721c24; }
    
    .cert-info { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-bottom: 12px; font-size: 13px; }
    .info-row { }
    .info-label { color: #999; font-size: 12px; }
    
    .cert-actions { display: flex; gap: 8px; }
    .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 500; transition: all 0.2s; }
    .btn-approve { background: #28a745; color: white; }
    .btn-approve:hover { background: #218838; }
    .btn-decline { background: #dc3545; color: white; }
    .btn-decline:hover { background: #c82333; }
    .btn-view { background: #007bff; color: white; }
    .btn-view:hover { background: #0056b3; }
    
    .empty { text-align: center; padding: 32px; color: #999; }
    
    @media (max-width: 640px) {
      .tabs { flex-wrap: wrap; }
      .tab-btn { padding: 10px 12px; font-size: 13px; }
      .cert-header { flex-direction: column; gap: 8px; }
      .cert-info { grid-template-columns: 1fr; }
      .cert-actions { flex-wrap: wrap; }
    }
  </style>
</head>
<body>

<div class="container">
  <header>
    <h1>📜 Certificate Management</h1>
    <p class="subtitle">Certificates are automatically approved or declined based on exam scores</p>
  </header>

  <?= $message ?>

  <div style="background: #e7f3ff; border-left: 4px solid #007bff; padding: 16px; margin-bottom: 24px; border-radius: 4px;">
    <strong>ℹ️ Auto-Approval System Active</strong><br>
    <small>Certificates are now automatically approved when students score at or above the passing threshold, and automatically declined when they score below it. No manual approval is needed.</small>
  </div>

  <div class="tabs">
    <button class="tab-btn active" onclick="switchTab('approved')">
      ✓ Auto-Approved <span style="font-weight: 700;"><?= count($approvedCerts) ?></span>
    </button>
    <button class="tab-btn" onclick="switchTab('declined')">
      ✗ Auto-Declined <span style="font-weight: 700;"><?= count($declinedCerts) ?></span>
    </button>
    <?php if (!empty($pendingCerts)): ?>
      <button class="tab-btn" onclick="switchTab('pending')">
        ⏳ Legacy Pending <span style="font-weight: 700;"><?= count($pendingCerts) ?></span>
      </button>
    <?php endif; ?>
  </div>

  <!-- APPROVED TAB -->
  <div id="approved-tab" class="tab-content active">
    <?php if (empty($approvedCerts)): ?>
      <div class="empty">No approved certificates yet</div>
    <?php else: ?>
      <div class="cert-list">
        <?php foreach ($approvedCerts as $cert): ?>
          <div class="cert-item" style="border-left-color: #28a745;">
            <div class="cert-header">
              <div>
                <div class="cert-name"><?= htmlspecialchars($cert['student_name'] ?? $cert['candidate_name']) ?></div>
                <span class="cert-badge approved">✓ Auto-Approved</span>
              </div>
              <div style="text-align: right;">
                <div style="font-size: 24px; font-weight: 700; color: #28a745;"><?= round($cert['score']) ?>%</div>
              </div>
            </div>

            <div class="cert-info">
              <div class="info-row">
                <div class="info-label">School</div>
                <div><?= htmlspecialchars($cert['school_name'] ?? '—') ?></div>
              </div>
              <div class="info-row">
                <div class="info-label">Assessment</div>
                <div><?= htmlspecialchars($cert['assessment_title'] ?? '—') ?></div>
              </div>
              <div class="info-row">
                <div class="info-label">Passing Score Required</div>
                <div><?= htmlspecialchars($cert['passing_score'] ?? '0') ?>%</div>
              </div>
              <div class="info-row">
                <div class="info-label">Certificate ID</div>
                <div style="font-family: monospace; font-size: 12px;"><?= htmlspecialchars($cert['certificate_id']) ?></div>
              </div>
              <div class="info-row">
                <div class="info-label">Issued</div>
                <div><?= $cert['issued_at'] ? date('M d, Y', strtotime($cert['issued_at'])) : '—' ?></div>
              </div>
            </div>

            <div class="cert-actions">
              <a href="<?= SITE_URL ?>/certificate.php?certificate_id=<?= urlencode($cert['certificate_id']) ?>" target="_blank" class="btn btn-view">👁 View Certificate</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- DECLINED TAB -->
  <div id="declined-tab" class="tab-content">
    <?php if (empty($declinedCerts)): ?>
      <div class="empty">No declined certificates</div>
    <?php else: ?>
      <div class="cert-list">
        <?php foreach ($declinedCerts as $cert): ?>
          <div class="cert-item" style="border-left-color: #dc3545;">
            <div class="cert-header">
              <div>
                <div class="cert-name"><?= htmlspecialchars($cert['student_name'] ?? $cert['candidate_name']) ?></div>
                <span class="cert-badge" style="background: #f8d7da; color: #721c24;">✗ Auto-Declined</span>
              </div>
              <div style="text-align: right;">
                <div style="font-size: 24px; font-weight: 700; color: #dc3545;"><?= round($cert['score']) ?>%</div>
              </div>
            </div>

            <div class="cert-info">
              <div class="info-row">
                <div class="info-label">School</div>
                <div><?= htmlspecialchars($cert['school_name'] ?? '—') ?></div>
              </div>
              <div class="info-row">
                <div class="info-label">Assessment</div>
                <div><?= htmlspecialchars($cert['assessment_title'] ?? '—') ?></div>
              </div>
              <div class="info-row">
                <div class="info-label">Passing Score Required</div>
                <div><?= htmlspecialchars($cert['passing_score'] ?? '0') ?>%</div>
              </div>
              <div class="info-row">
                <div class="info-label">Score Below Threshold</div>
                <div style="color: #dc3545; font-weight: 600;"><?= htmlspecialchars($cert['score']) ?>% (needed: <?= htmlspecialchars($cert['passing_score']) ?>%)</div>
              </div>
              <div class="info-row">
                <div class="info-label">Exam Date</div>
                <div><?= $cert['issued_at'] ? date('M d, Y', strtotime($cert['issued_at'])) : '—' ?></div>
              </div>
            </div>

            <div class="cert-actions">
              <small style="color: #999;">Student can retake the exam to improve their score</small>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- LEGACY PENDING TAB (if any exist) -->
  <?php if (!empty($pendingCerts)): ?>
  <div id="pending-tab" class="tab-content">
    <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 16px; margin-bottom: 16px; border-radius: 4px;">
      <strong>⚠️ Legacy Pending Certificates</strong><br>
      <small>These certificates were created before the auto-approval system was enabled. You can manually review and approve or delete them, or they will be automatically converted on next update.</small>
    </div>
    <div class="cert-list">
      <?php foreach ($pendingCerts as $cert): ?>
        <div class="cert-item">
          <div class="cert-header">
            <div>
              <div class="cert-name"><?= htmlspecialchars($cert['student_name'] ?? $cert['candidate_name']) ?></div>
              <span class="cert-badge pending">Pending (Legacy)</span>
            </div>
            <div style="text-align: right;">
              <div style="font-size: 24px; font-weight: 700; color: #007bff;"><?= round($cert['score']) ?>%</div>
            </div>
          </div>

          <div class="cert-info">
            <div class="info-row">
              <div class="info-label">School</div>
              <div><?= htmlspecialchars($cert['school_name'] ?? '—') ?></div>
            </div>
            <div class="info-row">
              <div class="info-label">Assessment</div>
              <div><?= htmlspecialchars($cert['assessment_title'] ?? '—') ?></div>
            </div>
            <div class="info-row">
              <div class="info-label">Certificate ID</div>
              <div style="font-family: monospace; font-size: 12px;"><?= htmlspecialchars($cert['certificate_id']) ?></div>
            </div>
            <div class="info-row">
              <div class="info-label">Issued</div>
              <div><?= $cert['issued_at'] ? date('M d, Y', strtotime($cert['issued_at'])) : '—' ?></div>
            </div>
          </div>

          <div class="cert-actions">
            <form method="POST" style="display: inline;">
              <input type="hidden" name="action" value="approve">
              <input type="hidden" name="certificate_id" value="<?= htmlspecialchars($cert['certificate_id']) ?>">
              <button type="submit" class="btn btn-approve" onclick="return confirm('Approve this legacy certificate?')">✓ Approve</button>
            </form>
            <form method="POST" style="display: inline;">
              <input type="hidden" name="action" value="decline">
              <input type="hidden" name="certificate_id" value="<?= htmlspecialchars($cert['certificate_id']) ?>">
              <button type="submit" class="btn btn-decline" onclick="return confirm('Delete this legacy certificate?')">✗ Delete</button>
            </form>
            <a href="<?= SITE_URL ?>/certificate.php?certificate_id=<?= urlencode($cert['certificate_id']) ?>" target="_blank" class="btn btn-view">👁 View</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

<script>
  function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    
    // Show selected tab
    document.getElementById(tabName + '-tab').classList.add('active');
    event.target.classList.add('active');
  }
</script>

</body>
</html>
