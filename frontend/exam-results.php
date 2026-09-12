<?php
/**
 * EXAM RESULTS PAGE
 * Shows detailed results after completing an exam
 */
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
session_start();

require_once '../backend/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$attempt_id = (int)($_GET['attempt_id'] ?? 0);
$ui_lang = $_GET['ui_lang'] ?? $_SESSION['ui_lang'] ?? 'en';
$_SESSION['ui_lang'] = $ui_lang;

// Get attempt and results
$stmt = $pdo->prepare("
    SELECT ea.*, e.title, e.language_pair, e.level_number, e.exam_language, e.passing_score
    FROM exam_attempts ea
    JOIN exams e ON ea.exam_id = e.id
    WHERE ea.id = ? AND ea.user_id = ? AND ea.status = 'submitted'
");
$stmt->execute([$attempt_id, $user_id]);
$attempt = $stmt->fetch();

if (!$attempt) {
    header("Location: exam-portal.php?error=results_not_found");
    exit;
}

// Get detailed results from certificates first, then fall back to exam_results
$stmt = $pdo->prepare("SELECT * FROM certificates WHERE exam_attempt_id = ? LIMIT 1");
$stmt->execute([$attempt_id]);
$results = $stmt->fetch();
if (!$results) {
    $stmt = $pdo->prepare("SELECT * FROM exam_results WHERE attempt_id = ?");
    $stmt->execute([$attempt_id]);
    $results = $stmt->fetch();
}

// Get certificate if available (may be pending or approved)
$certificate = null;
if (!empty($results['certificate_id'])) {
    $certificate = $results;
} elseif (!empty($attempt['certificate_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM certificates WHERE certificate_id = ? LIMIT 1");
    $stmt->execute([$attempt['certificate_id']]);
    $certificate = $stmt->fetch();
}

// Get strengths/weaknesses
$strengths = $results ? json_decode($results['strengths'], true) : [];
$weaknesses = $results ? json_decode($results['weaknesses'], true) : [];
$recommendations = $results ? json_decode($results['recommendations'], true) : [];

// Translation function
function t($key) {
    global $ui_lang;
    $translations = [
        'en' => [
            'exam_results' => 'Exam Results',
            'congratulations' => 'Congratulations!',
            'passed' => 'You Passed!',
            'failed' => 'Not Quite',
            'score' => 'Score',
            'passing_score' => 'Passing Score',
            'exam_language' => 'Exam Language',
            'level' => 'Level',
            'date' => 'Date',
            'time_taken' => 'Time Taken',
            'overall' => 'Overall',
            'breakdown' => 'Score Breakdown',
            'reading' => 'Reading',
            'listening' => 'Listening',
            'translation' => 'Translation',
            'speaking' => 'Speaking',
            'writing' => 'Writing',
            'strengths' => 'Strengths',
            'weaknesses' => 'Areas to Improve',
            'recommendations' => 'Recommendations',
            'certificate' => 'Certificate',
            'download_certificate' => 'Download Certificate',
            'view_certificate' => 'View Certificate',
            'retake_exam' => 'Retake Exam',
            'continue_learning' => 'Continue Learning',
            'back_to_portal' => 'Back to Portal',
            'next_lesson' => 'Next Lesson',
            'unlock_message' => 'Great job! Your next lesson has been unlocked.',
            'keep_practicing' => 'Keep practicing to improve your weak areas.',
            'minutes' => 'min',
            'your_answer' => 'Your Answer',
            'correct_answer' => 'Correct Answer',
            'correct' => 'Correct',
            'incorrect' => 'Incorrect',
        ],
        'rw' => [
            'exam_results' => 'Ibisubizo by\'Ikizamini',
            'congratulations' => 'Murakoze!',
            'passed' => 'Watsinze!',
            'failed' => 'Ntiwatsinze',
            'score' => 'Nterwa',
            'passing_score' => 'Nterwa yo pasan',
            'exam_language' => 'Ururimi rw\'ikizamini',
            'level' => 'Rwego',
            'date' => 'Italiki',
            'time_taken' => 'Igihe wakoresheje',
            'overall' => 'Yose',
            'breakdown' => 'Ibizamini by\'Abana',
            'reading' => 'Gusoma',
            'listening' => 'Kumva',
            'translation' => 'Guhindura',
            'speaking' => 'Kuvuga',
            'writing' => 'Kwandika',
            'strengths' => 'Byakunze',
            'weaknesses' => 'Hari ho gukora',
            'recommendations' => 'Inyobolanire',
            'certificate' => 'Icyinyamazama',
            'download_certificate' => 'Kureka Icyinyamazama',
            'view_certificate' => 'Reba Icyinyamazama',
            'retake_exam' => 'Subiramo Ikizamini',
            'continue_learning' => 'Komeza Kwiga',
            'back_to_portal' => 'Subira kuri Portal',
            'next_lesson' => 'Ikindi kiganiro',
            'unlock_message' => 'Wakoze neza! Ikindi kiganiro kirakuziyemo.',
            'keep_practicing' => 'Komeza gukora neza bidade uzamuke.',
            'minutes' => 'dk',
            'your_answer' => 'Igisubizo cyawe',
            'correct_answer' => 'Igisubizo numvikana',
            'correct' => 'Nibye',
            'incorrect' => 'Ibinyamazama',
        ],
    ];
    return $translations[$ui_lang][$key] ?? $key;
}

// Calculate time taken
$time_taken = '';
if ($attempt['started_at'] && $attempt['submitted_at']) {
    $start = new DateTime($attempt['started_at']);
    $end = new DateTime($attempt['submitted_at']);
    $diff = $start->diff($end);
    $time_taken = $diff->format('%i') . ' ' . t('minutes');
}
?>
<!DOCTYPE html>
<html lang="<?= $ui_lang ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= t('exam_results') ?> · Playmates</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons/css/flag-icons.min.css">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
:root {
    --sand: #f5ede6; --sand-mid: #ede3d8; --sand-dark: #ddd0c2;
    --sage: #c6dfd5; --sage-mid: #8bbfad; --sage-dark: #3a7a68; --sage-deep: #225548;
    --lavender: #d4cfed; --butter: #f5e8b0; --peach: #f5d6c4; --blush: #f9d8cd;
    --ink: #1a1a18; --ink-mid: #4a4845; --muted: #8a8178; --soft: #b8b0a5;
    --surface: #faf8f5; --border: rgba(0,0,0,0.07);
    --radius-sm: 8px; --radius: 14px; --radius-lg: 20px;
    --transition: 0.25s cubic-bezier(.4,0,.2,1);
}
html, body { height: 100%; font-family: 'DM Sans', sans-serif; font-size: 14px; color: var(--ink); background: var(--sand); }

.results-container { max-width: 900px; margin: 0 auto; padding: 24px; }

.header { display: flex; align-items: center; gap: 14px; margin-bottom: 28px; }
.back-link { color: var(--muted); text-decoration: none; font-size: 13px; }
.back-link:hover { color: var(--ink); }
.page-title { font-family: 'DM Serif Display', serif; font-size: 24px; }

/* Result Card */
.result-card { background: var(--surface); border-radius: var(--radius-lg); padding: 32px; margin-bottom: 20px; border: 1px solid var(--border); text-align: center; }
.result-status { font-size: 48px; margin-bottom: 12px; }
.result-title { font-family: 'DM Serif Display', serif; font-size: 28px; margin-bottom: 8px; }
.result-title.passed { color: var(--sage-dark); }
.result-title.failed { color: #b04428; }
.result-message { font-size: 14px; color: var(--muted); margin-bottom: 24px; }

.score-display { display: flex; justify-content: center; gap: 40px; margin-bottom: 24px; flex-wrap: wrap; }
.score-item { text-align: center; }
.score-value { font-family: 'DM Serif Display', serif; font-size: 42px; color: var(--ink); }
.score-value.passed { color: var(--sage-dark); }
.score-value.failed { color: #b04428; }
.score-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.8px; color: var(--muted); margin-top: 4px; }
.score-divider { width: 1px; background: var(--border); align-self: stretch; }

.score-bar { height: 12px; background: var(--sand-dark); border-radius: 10px; overflow: hidden; max-width: 300px; margin: 0 auto 16px; }
.score-bar-fill { height: 100%; border-radius: 10px; transition: width 1s ease; }
.score-bar-fill.passed { background: linear-gradient(90deg, var(--sage-mid), var(--sage-dark)); }
.score-bar-fill.failed { background: linear-gradient(90deg, #f5b4a0, #b04428); }

.result-meta { display: flex; justify-content: center; gap: 24px; flex-wrap: wrap; font-size: 12px; color: var(--muted); }
.result-meta-item { display: flex; align-items: center; gap: 6px; }

/* Actions */
.result-actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; margin-top: 20px; }
.btn { padding: 12px 28px; border-radius: 30px; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.15s; border: none; font-family: 'DM Sans', sans-serif; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; }
.btn-primary { background: var(--ink); color: #fff; }
.btn-primary:hover { opacity: 0.85; transform: translateY(-2px); }
.btn-secondary { background: var(--sage); color: var(--sage-dark); }
.btn-secondary:hover { opacity: 0.8; }
.btn-cert { background: linear-gradient(135deg, var(--butter), #e6d19a); color: #7a5c1e; }
.btn-cert:hover { opacity: 0.9; }

/* Certificate Preview */
.cert-preview { background: linear-gradient(135deg, var(--sage-deep), var(--sage-dark)); border-radius: var(--radius); padding: 24px; color: #fff; text-align: center; margin-top: 20px; position: relative; overflow: hidden; }
.cert-preview::before { content: ''; position: absolute; top: -50%; right: -50%; width: 100%; height: 100%; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%); pointer-events: none; }
.cert-badge { font-size: 48px; margin-bottom: 8px; }
.cert-title { font-family: 'DM Serif Display', serif; font-size: 20px; margin-bottom: 4px; }
.cert-id { font-size: 11px; opacity: 0.7; font-family: monospace; margin-top: 8px; }

/* Breakdown Section */
.section-title { font-family: 'DM Serif Display', serif; font-size: 20px; margin-bottom: 16px; color: var(--ink); }
.breakdown-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; margin-bottom: 24px; }
.breakdown-card { background: var(--surface); border-radius: var(--radius); padding: 16px; text-align: center; border: 1px solid var(--border); }
.breakdown-icon { font-size: 24px; margin-bottom: 8px; }
.breakdown-type { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--muted); margin-bottom: 6px; }
.breakdown-score { font-family: 'DM Serif Display', serif; font-size: 24px; }
.breakdown-bar { height: 4px; background: var(--sand-dark); border-radius: 4px; margin-top: 8px; overflow: hidden; }
.breakdown-bar-fill { height: 100%; border-radius: 4px; }
.breakdown-bar-fill.high { background: var(--sage-dark); }
.breakdown-bar-fill.medium { background: var(--butter); }
.breakdown-bar-fill.low { background: #f5b4a0; }

/* Strengths & Weaknesses */
.strengths-weaknesses { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px; }
.sw-card { background: var(--surface); border-radius: var(--radius); padding: 20px; border: 1px solid var(--border); }
.sw-title { font-weight: 600; font-size: 14px; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
.sw-title.strength { color: var(--sage-dark); }
.sw-title.weakness { color: #b04428; }
.sw-list { list-style: none; }
.sw-list li { padding: 8px 0; border-bottom: 1px solid var(--border); font-size: 13px; display: flex; align-items: center; gap: 8px; }
.sw-list li:last-child { border-bottom: none; }
.sw-icon { width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; }
.sw-icon.positive { background: var(--sage); }
.sw-icon.negative { background: var(--peach); }

/* Recommendations */
.recommendations-card { background: var(--surface); border-radius: var(--radius); padding: 20px; border: 1px solid var(--border); margin-bottom: 24px; }
.rec-title { font-weight: 600; font-size: 14px; margin-bottom: 12px; color: var(--ink); }
.rec-list { list-style: none; }
.rec-list li { padding: 10px 0; border-bottom: 1px solid var(--border); font-size: 13px; display: flex; align-items: flex-start; gap: 10px; }
.rec-list li:last-child { border-bottom: none; }
.rec-icon { flex-shrink: 0; width: 24px; height: 24px; background: var(--lavender); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; }

/* Unlock Message */
.unlock-card { background: linear-gradient(135deg, var(--sage), var(--sage-mid)); border-radius: var(--radius); padding: 20px; color: var(--sage-deep); text-align: center; margin-bottom: 20px; }
.unlock-icon { font-size: 32px; margin-bottom: 8px; }
.unlock-text { font-size: 14px; font-weight: 500; }

/* Responsive */
@media (max-width: 768px) {
    .breakdown-grid { grid-template-columns: repeat(3, 1fr); }
    .strengths-weaknesses { grid-template-columns: 1fr; }
    .score-display { gap: 20px; }
}
</style>
</head>
<body>

<div class="results-container">
    <!-- Header -->
    <div class="header">
        <a href="exam-portal.php?ui_lang=<?= $ui_lang ?>" class="back-link">← <?= t('back_to_portal') ?></a>
    </div>
    
    <?php if ($attempt['passed']): ?>
    <!-- Passed Result -->
    <div class="result-card">
        <div class="result-status">🏆</div>
        <div class="result-title passed"><?= t('congratulations') ?></div>
        <div class="result-title passed"><?= t('passed') ?></div>
        <div class="result-message"><?= $ui_lang === 'en' ? 'You have successfully passed the exam!' : 'Watsinze ikizamini!' ?></div>
        
        <div class="score-bar">
            <div class="score-bar-fill passed" style="width: <?= $attempt['score'] ?>%"></div>
        </div>
        
        <div class="score-display">
            <div class="score-item">
                <div class="score-value passed"><?= round($attempt['score']) ?>%</div>
                <div class="score-label"><?= t('score') ?></div>
            </div>
            <div class="score-divider"></div>
            <div class="score-item">
                <div class="score-value"><?= $attempt['passing_score'] ?>%</div>
                <div class="score-label"><?= t('passing_score') ?></div>
            </div>
        </div>
        
        <div class="result-meta">
            <div class="result-meta-item">📝 <?= htmlspecialchars($attempt['title']) ?></div>
            <div class="result-meta-item">🌐 <?= strtoupper($attempt['exam_language']) ?> <?= t('exam_language') ?></div>
            <div class="result-meta-item">📊 <?= t('level') ?> <?= $attempt['level_number'] ?></div>
            <div class="result-meta-item">📅 <?= date('M j, Y', strtotime($attempt['submitted_at'])) ?></div>
            <div class="result-meta-item">⏱️ <?= $time_taken ?></div>
        </div>
        
        <?php if ($certificate): ?>
        <div class="cert-preview">
            <div class="cert-badge">🏆</div>
            <div class="cert-title">Certificate of Achievement</div>
            <div style="font-size: 12px; opacity: 0.8; margin-top: 8px;">
                <?= htmlspecialchars($user['full_name'] ?? 'Student') ?> · <?= htmlspecialchars($attempt['title']) ?>
            </div>
                        <div class="cert-id"><?= htmlspecialchars($certificate['certificate_id']) ?></div>
                        <?php $cstatus = strtolower($certificate['status'] ?? 'pending'); ?>
                        <div style="margin-top:8px;font-size:13px">
                            <?php if ($cstatus === 'approved' || $cstatus === 'issued'): ?>
                                <span style="color:green;font-weight:600">✓ Approved</span>
                            <?php else: ?>
                                <span style="color:#b98f00;font-weight:600">⏳ Pending approval</span>
                            <?php endif; ?>
                        </div>
        </div>
        <?php endif; ?>
        
        <div class="result-actions">
            <?php if ($certificate): ?>
            <button class="btn btn-cert" onclick="downloadCertificate()">🏆 <?= t('download_certificate') ?></button>
            <?php endif; ?>
            <a href="dashboard.php?ui_lang=<?= $ui_lang ?>" class="btn btn-secondary"><?= t('continue_learning') ?></a>
            <button onclick="retakeExam()" class="btn btn-primary">🔄 <?= t('retake_exam') ?></button>
        </div>
    </div>
    <?php else: ?>
    <!-- Failed Result -->
    <div class="result-card">
        <div class="result-status">📚</div>
        <div class="result-title failed"><?= t('failed') ?></div>
        <div class="result-message"><?= $ui_lang === 'en' ? 'Keep practicing and try again!' : 'Komeza gukora kandi ugerageze!' ?></div>
        
        <div class="score-bar">
            <div class="score-bar-fill failed" style="width: <?= $attempt['score'] ?>%"></div>
        </div>
        
        <div class="score-display">
            <div class="score-item">
                <div class="score-value failed"><?= round($attempt['score']) ?>%</div>
                <div class="score-label"><?= t('score') ?></div>
            </div>
            <div class="score-divider"></div>
            <div class="score-item">
                <div class="score-value"><?= $attempt['passing_score'] ?>%</div>
                <div class="score-label"><?= t('passing_score') ?></div>
            </div>
        </div>
        
        <div class="result-meta">
            <div class="result-meta-item">📝 <?= htmlspecialchars($attempt['title']) ?></div>
            <div class="result-meta-item">📅 <?= date('M j, Y', strtotime($attempt['submitted_at'])) ?></div>
        </div>
        
        <div class="result-actions">
            <a href="dashboard.php?ui_lang=<?= $ui_lang ?>" class="btn btn-secondary"><?= t('continue_learning') ?></a>
            <button onclick="retakeExam()" class="btn btn-primary">🔄 <?= t('retake_exam') ?></button>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if ($results): ?>
    <!-- Score Breakdown -->
    <h2 class="section-title"><?= t('breakdown') ?></h2>
    <div class="breakdown-grid">
        <?php
        $breakdown_items = [
            ['key' => 'reading_score', 'icon' => '📖', 'label' => t('reading')],
            ['key' => 'listening_score', 'icon' => '🎧', 'label' => t('listening')],
            ['key' => 'translation_score', 'icon' => '🔤', 'label' => t('translation')],
            ['key' => 'speaking_score', 'icon' => '🎤', 'label' => t('speaking')],
            ['key' => 'writing_score', 'icon' => '✍️', 'label' => t('writing')],
        ];
        foreach ($breakdown_items as $item):
            $score = round($results[$item['key']] ?? 0);
            $bar_class = $score >= 70 ? 'high' : ($score >= 50 ? 'medium' : 'low');
        ?>
        <div class="breakdown-card">
            <div class="breakdown-icon"><?= $item['icon'] ?></div>
            <div class="breakdown-type"><?= $item['label'] ?></div>
            <div class="breakdown-score" style="color: <?= $score >= 70 ? 'var(--sage-dark)' : ($score >= 50 ? '#a07820' : '#b04428') ?>">
                <?= $score ?>%
            </div>
            <div class="breakdown-bar">
                <div class="breakdown-bar-fill <?= $bar_class ?>" style="width: <?= $score ?>%"></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Strengths & Weaknesses -->
    <div class="strengths-weaknesses">
        <div class="sw-card">
            <div class="sw-title strength">💪 <?= t('strengths') ?></div>
            <?php if (!empty($strengths)): ?>
            <ul class="sw-list">
                <?php foreach ($strengths as $s): ?>
                <li>
                    <span class="sw-icon positive">✓</span>
                    <?= htmlspecialchars(ucfirst($s)) ?>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <p style="color: var(--muted); font-size: 13px;"><?= $ui_lang === 'en' ? 'No strong areas identified yet' : 'Nta matiri mashobo abonetse' ?></p>
            <?php endif; ?>
        </div>
        <div class="sw-card">
            <div class="sw-title weakness">📚 <?= t('weaknesses') ?></div>
            <?php if (!empty($weaknesses)): ?>
            <ul class="sw-list">
                <?php foreach ($weaknesses as $w): ?>
                <li>
                    <span class="sw-icon negative">!</span>
                    <?= htmlspecialchars(ucfirst($w)) ?>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <p style="color: var(--muted); font-size: 13px;"><?= $ui_lang === 'en' ? 'Great job! No weak areas' : 'Wakoze neza! Nta bimenyekanashobo' ?></p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Recommendations -->
    <div class="recommendations-card">
        <div class="rec-title">💡 <?= t('recommendations') ?></div>
        <ul class="rec-list">
            <?php if (!empty($weaknesses)): ?>
            <li>
                <span class="rec-icon">📌</span>
                <?= $ui_lang === 'en' 
                    ? 'Focus on improving: ' . implode(', ', array_map('ucfirst', $weaknesses))
                    : 'Tekerezaho ku: ' . implode(', ', array_map('ucfirst', $weaknesses)) ?>
            </li>
            <?php endif; ?>
            <?php if ($attempt['passed']): ?>
            <li>
                <span class="rec-icon">🎯</span>
                <?= $ui_lang === 'en' 
                    ? 'Congratulations! You can now move to the next level.'
                    : 'Murakoze! Urashobora gukura rwego rurenga.' ?>
            </li>
            <?php else: ?>
            <li>
                <span class="rec-icon">📚</span>
                <?= $ui_lang === 'en' 
                    ? 'Complete more lessons in weak areas before retaking.'
                    : 'Mwararuguke amsomo menshi mu bimenyekanashobo bikeneye gufashwa mbere y\'ugusubiramo.' ?>
            </li>
            <?php endif; ?>
        </ul>
    </div>
    <?php endif; ?>
    
    <?php if ($attempt['passed']): ?>
    <!-- Unlock Message -->
    <div class="unlock-card">
        <div class="unlock-icon">🎉</div>
        <div class="unlock-text"><?= t('unlock_message') ?></div>
    </div>
    <?php else: ?>
    <div class="unlock-card" style="background: linear-gradient(135deg, var(--peach), #f5c4a8); color: #7a3020;">
        <div class="unlock-icon">💪</div>
        <div class="unlock-text"><?= t('keep_practicing') ?></div>
    </div>
    <?php endif; ?>
</div>

<script>
function retakeExam() {
    window.location.href = 'exam-portal.php?ui_lang=<?= $ui_lang ?>';
}

function downloadCertificate() {
    // In production, this would generate/download a PDF certificate
    alert('Certificate download would be generated here in production.');
}
</script>

</body>
</html>
