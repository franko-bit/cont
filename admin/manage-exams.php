<?php
/**
 * ADMIN: EXAM MANAGEMENT PAGE
 */
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
session_start();

require_once '../backend/config.php';

// Check admin access
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: ../index.php");
    exit;
}

$ui_lang = $_GET['ui_lang'] ?? 'en';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_exam') {
        $stmt = $pdo->prepare("
            INSERT INTO exams (language_pair, level_number, title, description, exam_language, duration_minutes, passing_score, total_questions)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_POST['language_pair'],
            (int)$_POST['level_number'],
            $_POST['title'],
            $_POST['description'],
            $_POST['exam_language'],
            (int)$_POST['duration_minutes'],
            (int)$_POST['passing_score'],
            (int)$_POST['total_questions']
        ]);
        $message = "Exam created successfully!";
    }
    
    if ($action === 'add_question') {
        $stmt = $pdo->prepare("
            INSERT INTO exam_questions (exam_id, language_pair, level_number, question_type, question_text, correct_answer, options, hints, difficulty, points)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            (int)$_POST['exam_id'],
            $_POST['language_pair'],
            (int)$_POST['level_number'],
            $_POST['question_type'],
            $_POST['question_text'],
            $_POST['correct_answer'],
            $_POST['options'] ? json_encode(explode("\n", trim($_POST['options']))) : null,
            $_POST['hints'] ?? null,
            $_POST['difficulty'] ?? 'medium',
            (int)($_POST['points'] ?? 10)
        ]);
        $message = "Question added successfully!";
    }
    
    if ($action === 'delete_exam') {
        $stmt = $pdo->prepare("DELETE FROM exams WHERE id = ?");
        $stmt->execute([(int)$_POST['exam_id']]);
        $message = "Exam deleted successfully!";
    }
}

// Get exams grouped by language pair
$stmt = $pdo->prepare("SELECT * FROM exams ORDER BY language_pair, level_number");
$stmt->execute();
$exams = $stmt->fetchAll();

// Get recent exam attempts
$stmt = $pdo->prepare("
    SELECT ea.*, u.full_name, u.email, e.title as exam_title
    FROM exam_attempts ea
    JOIN users u ON ea.user_id = u.id
    JOIN exams e ON ea.exam_id = e.id
    ORDER BY ea.created_at DESC
    LIMIT 20
");
$stmt->execute();
$recent_attempts = $stmt->fetchAll();

// Get stats
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_attempts,
        SUM(passed) as passed_count,
        AVG(score) as avg_score
    FROM exam_attempts
    WHERE status = 'submitted'
");
$stmt->execute();
$stats = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="<?= $ui_lang ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Exam Management · Admin</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
:root {
    --sand: #f5ede6; --sand-mid: #ede3d8; --sand-dark: #ddd0c2;
    --sage: #c6dfd5; --sage-mid: #8bbfad; --sage-dark: #3a7a68; --sage-deep: #225548;
    --lavender: #d4cfed; --peach: #f5d6c4; --butter: #f5e8b0;
    --ink: #1a1a18; --ink-mid: #4a4845; --muted: #8a8178;
    --surface: #faf8f5; --border: rgba(0,0,0,0.07);
    --radius: 14px;
}
html, body { height: 100%; font-family: 'DM Sans', sans-serif; font-size: 14px; color: var(--ink); background: var(--sand); }

.admin-container { max-width: 1200px; margin: 0 auto; padding: 24px; }

.header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; }
.page-title { font-family: 'DM Serif Display', serif; font-size: 24px; }
.back-link { color: var(--muted); text-decoration: none; font-size: 13px; }
.back-link:hover { color: var(--ink); }

.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 28px; }
.stat-card { background: var(--surface); border-radius: var(--radius); padding: 20px; border: 1px solid var(--border); }
.stat-value { font-family: 'DM Serif Display', serif; font-size: 28px; color: var(--sage-dark); }
.stat-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.8px; color: var(--muted); margin-top: 4px; }

.section { background: var(--surface); border-radius: var(--radius); border: 1px solid var(--border); margin-bottom: 24px; overflow: hidden; }
.section-header { padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
.section-title { font-family: 'DM Serif Display', serif; font-size: 18px; }
.btn { padding: 8px 18px; border-radius: 20px; font-weight: 500; font-size: 13px; cursor: pointer; border: none; font-family: 'DM Sans', sans-serif; display: inline-flex; align-items: center; gap: 6px; }
.btn-primary { background: var(--ink); color: #fff; }
.btn-secondary { background: var(--sage); color: var(--sage-dark); }

table { width: 100%; border-collapse: collapse; }
th, td { padding: 12px 16px; text-align: left; border-bottom: 1px solid var(--border); }
th { font-size: 11px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; color: var(--muted); background: var(--sand); }
tr:hover { background: var(--sand); }
tr:last-child td { border-bottom: none; }

.badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 500; }
.badge-active { background: var(--sage); color: var(--sage-dark); }
.badge-passed { background: var(--sage-mid); color: var(--sage-deep); }
.badge-failed { background: var(--peach); color: #7a3020; }
.badge-pending { background: var(--butter); color: #7a5c1e; }

.actions { display: flex; gap: 8px; }
.action-btn { padding: 5px 10px; border-radius: 6px; border: 1px solid var(--border); background: var(--surface); cursor: pointer; font-size: 12px; font-family: 'DM Sans', sans-serif; }
.action-btn:hover { background: var(--sand-mid); }
.action-btn.delete { color: #b04428; border-color: #f5d6c4; }

.modal { display: none; position: fixed; inset: 0; background: rgba(26,26,24,0.45); z-index: 999; align-items: center; justify-content: center; }
.modal.open { display: flex; }
.modal-box { background: var(--surface); border-radius: var(--radius); padding: 24px; width: 500px; max-width: 92vw; max-height: 90vh; overflow-y: auto; }
.modal-title { font-family: 'DM Serif Display', serif; font-size: 20px; margin-bottom: 20px; }
.form-group { margin-bottom: 16px; }
.form-label { display: block; font-size: 12px; font-weight: 500; margin-bottom: 6px; }
.form-input, .form-select, .form-textarea { width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: var(--radius); font-size: 14px; font-family: 'DM Sans', sans-serif; background: var(--surface); }
.form-textarea { min-height: 80px; resize: vertical; }
.form-select { cursor: pointer; }
.form-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }

.message { padding: 12px 16px; background: var(--sage); color: var(--sage-dark); border-radius: var(--radius); margin-bottom: 16px; font-size: 13px; }

@media (max-width: 768px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .section { overflow-x: auto; }
}
</style>
</head>
<body>

<div class="admin-container">
    <div class="header">
        <a href="index.php" class="back-link">← Back to Admin</a>
        <h1 class="page-title">Exam Management</h1>
        <div></div>
    </div>
    
    <?php if (isset($message)): ?>
    <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?= number_format($stats['total_attempts'] ?? 0) ?></div>
            <div class="stat-label">Total Attempts</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= number_format($stats['passed_count'] ?? 0) ?></div>
            <div class="stat-label">Passed</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= round($stats['avg_score'] ?? 0, 1) ?>%</div>
            <div class="stat-label">Average Score</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= count($exams) ?></div>
            <div class="stat-label">Active Exams</div>
        </div>
    </div>
    
    <!-- Exams List -->
    <div class="section">
        <div class="section-header">
            <h2 class="section-title">Exams</h2>
            <button class="btn btn-primary" onclick="openModal('examModal')">+ Create Exam</button>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Language Pair</th>
                    <th>Level</th>
                    <th>Duration</th>
                    <th>Questions</th>
                    <th>Pass Score</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($exams as $exam): ?>
                <?php
                // Get question count
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM exam_questions WHERE exam_id = ?");
                $stmt->execute([$exam['id']]);
                $q_count = $stmt->fetchColumn();
                ?>
                <tr>
                    <td><?= htmlspecialchars($exam['title']) ?></td>
                    <td><?= htmlspecialchars($exam['language_pair']) ?></td>
                    <td><?= $exam['level_number'] ?></td>
                    <td><?= $exam['duration_minutes'] ?> min</td>
                    <td><?= $q_count ?> / <?= $exam['total_questions'] ?></td>
                    <td><?= $exam['passing_score'] ?>%</td>
                    <td>
                        <span class="badge <?= $exam['is_active'] ? 'badge-active' : 'badge-pending' ?>">
                            <?= $exam['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                    <td>
                        <div class="actions">
                            <button class="action-btn" onclick="viewExamQuestions(<?= $exam['id'] ?>)">Questions</button>
                            <button class="action-btn" onclick="addQuestion(<?= $exam['id'] ?>)">+ Q</button>
                            <button class="action-btn delete" onclick="deleteExam(<?= $exam['id'] ?>)">Delete</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Recent Attempts -->
    <div class="section">
        <div class="section-header">
            <h2 class="section-title">Recent Exam Attempts</h2>
        </div>
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Exam</th>
                    <th>Score</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_attempts as $attempt): ?>
                <tr>
                    <td><?= htmlspecialchars($attempt['full_name'] ?? $attempt['email']) ?></td>
                    <td><?= htmlspecialchars($attempt['exam_title']) ?></td>
                    <td><?= $attempt['score'] ? round($attempt['score']) . '%' : '-' ?></td>
                    <td>
                        <?php if ($attempt['status'] === 'submitted' || $attempt['status'] === 'graded'): ?>
                            <span class="badge <?= $attempt['passed'] ? 'badge-passed' : 'badge-failed' ?>">
                                <?= $attempt['passed'] ? 'Passed' : 'Failed' ?>
                            </span>
                        <?php elseif ($attempt['status'] === 'in_progress'): ?>
                            <span class="badge badge-pending">In Progress</span>
                        <?php else: ?>
                            <span class="badge badge-pending"><?= ucfirst($attempt['status']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('M j, Y H:i', strtotime($attempt['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create Exam Modal -->
<div class="modal" id="examModal">
    <div class="modal-box">
        <h2 class="modal-title">Create New Exam</h2>
        <form method="POST">
            <input type="hidden" name="action" value="create_exam">
            
            <div class="form-group">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-input" required placeholder="e.g., English-Kinyarwanda Level 1 Assessment">
            </div>
            
            <div class="form-group">
                <label class="form-label">Language Pair</label>
                <select name="language_pair" class="form-select">
                    <option value="en-rw">English → Kinyarwanda</option>
                    <option value="rw-en">Kinyarwanda → English</option>
                    <option value="fr-rw">French → Kinyarwanda</option>
                    <option value="rw-fr">Kinyarwanda → French</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Level</label>
                <select name="level_number" class="form-select">
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                    <option value="<?= $i ?>">Level <?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Exam Language (Target Language)</label>
                <select name="exam_language" class="form-select">
                    <option value="en">English</option>
                    <option value="fr">French</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-textarea" placeholder="Exam description..."></textarea>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
                <div class="form-group">
                    <label class="form-label">Duration (min)</label>
                    <input type="number" name="duration_minutes" class="form-input" value="45" min="15" max="180">
                </div>
                <div class="form-group">
                    <label class="form-label">Passing Score %</label>
                    <input type="number" name="passing_score" class="form-input" value="60" min="1" max="100">
                </div>
                <div class="form-group">
                    <label class="form-label">Total Questions</label>
                    <input type="number" name="total_questions" class="form-input" value="25" min="5" max="100">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('examModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Exam</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Question Modal -->
<div class="modal" id="questionModal">
    <div class="modal-box">
        <h2 class="modal-title">Add Question</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add_question">
            <input type="hidden" name="exam_id" id="qExamId">
            
            <div class="form-group">
                <label class="form-label">Language Pair</label>
                <select name="language_pair" class="form-select">
                    <option value="en-rw">English → Kinyarwanda</option>
                    <option value="rw-en">Kinyarwanda → English</option>
                    <option value="fr-rw">French → Kinyarwanda</option>
                    <option value="rw-fr">Kinyarwanda → French</option>
                </select>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="form-group">
                    <label class="form-label">Level</label>
                    <select name="level_number" class="form-select">
                        <?php for ($i = 1; $i <= 6; $i++): ?>
                        <option value="<?= $i ?>">Level <?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Question Type</label>
                    <select name="question_type" class="form-select">
                        <option value="reading">Reading</option>
                        <option value="listening">Listening</option>
                        <option value="translation">Translation</option>
                        <option value="speaking">Speaking</option>
                        <option value="writing">Writing</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Question Text</label>
                <textarea name="question_text" class="form-textarea" required placeholder="Enter the question..."></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label">Correct Answer</label>
                <input type="text" name="correct_answer" class="form-input" required placeholder="The correct answer">
            </div>
            
            <div class="form-group">
                <label class="form-label">Options (one per line, for multiple choice)</label>
                <textarea name="options" class="form-textarea" placeholder="Option A&#10;Option B&#10;Option C&#10;Option D"></textarea>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
                <div class="form-group">
                    <label class="form-label">Difficulty</label>
                    <select name="difficulty" class="form-select">
                        <option value="easy">Easy</option>
                        <option value="medium">Medium</option>
                        <option value="hard">Hard</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Points</label>
                    <input type="number" name="points" class="form-input" value="10" min="1" max="100">
                </div>
                <div class="form-group">
                    <label class="form-label">Hints (optional)</label>
                    <input type="text" name="hints" class="form-input" placeholder="Optional hint">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('questionModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Question</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).classList.add('open');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('open');
}

function addQuestion(examId) {
    document.getElementById('qExamId').value = examId;
    openModal('questionModal');
}

function viewExamQuestions(examId) {
    window.location.href = 'exam-questions.php?exam_id=' + examId;
}

function deleteExam(examId) {
    if (confirm('Are you sure you want to delete this exam? This cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_exam">
            <input type="hidden" name="exam_id" value="${examId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Close modals on escape
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal').forEach(m => m.classList.remove('open'));
    }
});

// Close modal on background click
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.remove('open');
        }
    });
});
</script>

</body>
</html>
