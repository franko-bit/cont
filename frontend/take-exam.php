<?php
/**
 * TAKE EXAM PAGE
 * The actual exam interface where users answer questions
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

// If no attempt_id is provided, check for dashboard parameters to create/find an attempt
if ($attempt_id <= 0 && !empty($_GET['pair']) && !empty($_GET['level']) && !empty($_GET['direction'])) {
    $exam_pair = $_GET['pair'];
    $exam_direction = $_GET['direction'];
    $exam_level = (int)$_GET['level'];

    // Find the exam
    $stmt = $pdo->prepare("SELECT * FROM exams WHERE language_pair = ? AND level_number = ? AND exam_language = ? AND is_active = 1 LIMIT 1");
    $stmt->execute([$exam_pair, $exam_level, $exam_direction]);
    $exam = $stmt->fetch();

    if ($exam) {
        // Check for existing attempt
        $stmt = $pdo->prepare("SELECT * FROM exam_attempts WHERE user_id = ? AND exam_id = ? AND status IN ('pending', 'verification', 'in_progress') ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$user_id, $exam['id']]);
        $existingAttempt = $stmt->fetch();

        if ($existingAttempt) {
            $attempt_id = $existingAttempt['id'];
        } else {
            // Create new attempt
            $stmt = $pdo->prepare("INSERT INTO exam_attempts (user_id, exam_id, status, verification_status, ip_address, user_agent) VALUES (?, ?, 'verification', 'pending', ?, ?)");
            $stmt->execute([$user_id, $exam['id'], $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);
            $attempt_id = $pdo->lastInsertId();
        }
    }
}

if ($attempt_id <= 0) {
    header("Location: dashboard.php?error=no_exam_found");
    exit;
}

$_SESSION['ui_lang'] = $ui_lang;
$stmt = $pdo->prepare("
    SELECT ea.*, e.title, e.level_number, e.exam_language, e.duration_minutes, e.total_questions
    FROM exam_attempts ea
    JOIN exams e ON ea.exam_id = e.id
    WHERE ea.id = ? AND ea.user_id = ? AND ea.status = 'in_progress'
");
$stmt->execute([$attempt_id, $user_id]);
$attempt = $stmt->fetch();

if (!$attempt) {
    $stmt = $pdo->prepare("
        SELECT ea.*, e.title, e.level_number, e.exam_language, e.duration_minutes, e.total_questions
        FROM exam_attempts ea
        JOIN exams e ON ea.exam_id = e.id
        WHERE ea.id = ? AND ea.user_id = ?
    ");
    $stmt->execute([$attempt_id, $user_id]);
    $existingAttempt = $stmt->fetch();

    if ($existingAttempt) {
        if (in_array($existingAttempt['status'], ['pending', 'verification'], true)) {
            // Redirect to verification flow instead of exam rules
            header("Location: verify.php?attempt_id=" . $attempt_id . "&ui_lang=" . $ui_lang);
            exit;
        }
        if ($existingAttempt['status'] === 'completed') {
            header("Location: exam-results.php?attempt_id=" . $attempt_id . "&ui_lang=" . $ui_lang);
            exit;
        }
    }

    header("Location: exam-portal.php?error=invalid_attempt");
    exit;
}

function t($key) {
    global $ui_lang;
    $translations = [
        'en' => [
            'level' => 'Level',
        ],
        'rw' => [
            'level' => 'Urwego',
        ],
    ];
    return $translations[$ui_lang][$key] ?? $translations['en'][$key] ?? $key;
}

// Get questions - note: exam_questions uses program_id, and individual option columns
$stmt = $pdo->prepare("
    SELECT id, question_type, question_text, option_a, option_b, option_c, option_d, difficulty
    FROM exam_questions
    WHERE program_id = ?
    ORDER BY RAND()
    LIMIT ?
");
$stmt->execute([$attempt['exam_id'], $attempt['total_questions']]);
$questions = $stmt->fetchAll();

// Format questions for client - convert individual options to array
$question_ids = array_column($questions, 'id');
$questions_for_client = [];
foreach ($questions as $q) {
    // Convert individual option columns to options array
    $options = [];
    if (!empty($q['option_a'])) $options[] = $q['option_a'];
    if (!empty($q['option_b'])) $options[] = $q['option_b'];
    if (!empty($q['option_c'])) $options[] = $q['option_c'];
    if (!empty($q['option_d'])) $options[] = $q['option_d'];
    
    $q['options'] = $options;
    unset($q['option_a'], $q['option_b'], $q['option_c'], $q['option_d']);
    
    $questions_for_client[] = $q;
}

// Get saved answers
$stmt = $pdo->prepare("SELECT question_id, user_answer FROM exam_answers WHERE session_id = ?");
$stmt->execute([$attempt_id]);
$saved_answers = [];
foreach ($stmt->fetchAll() as $ans) {
    $saved_answers[$ans['question_id']] = $ans['user_answer'];
}

$questions_json = json_encode($questions_for_client);
$saved_answers_json = json_encode($saved_answers);
?>
<!DOCTYPE html>
<html lang="<?= $ui_lang ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($attempt['title']) ?> · Playmates</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
:root {
    --sand: #f5ede6; --sand-mid: #ede3d8; --sand-dark: #ddd0c2;
    --sage: #c6dfd5; --sage-mid: #8bbfad; --sage-dark: #3a7a68; --sage-deep: #225548;
    --lavender: #d4cfed; --peach: #f5d6c4; --butter: #f5e8b0;
    --ink: #1a1a18; --ink-mid: #4a4845; --muted: #8a8178;
    --surface: #faf8f5; --border: rgba(0,0,0,0.07);
    --radius: 14px; --radius-lg: 20px;
}
html, body { height: 100%; font-family: 'DM Sans', sans-serif; font-size: 14px; color: var(--ink); background: var(--ink); }

.exam-wrapper { height: 100vh; display: flex; flex-direction: column; }

/* Header */
.exam-header { background: var(--sage-deep); color: #fff; padding: 12px 20px; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
.exam-title { font-family: 'DM Serif Display', serif; font-size: 16px; }
.exam-progress { font-size: 12px; opacity: 0.7; }

.timer { display: flex; align-items: center; gap: 8px; font-family: 'DM Serif Display', serif; font-size: 22px; }
.timer-icon { font-size: 18px; }
.timer.warning { color: var(--butter); }
.timer.danger { color: #ff6b6b; animation: pulse 1s infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.6; } }

.header-actions { display: flex; gap: 10px; }
.btn-exit { background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: #fff; padding: 6px 14px; border-radius: 20px; font-size: 12px; cursor: pointer; font-family: 'DM Sans', sans-serif; }
.btn-exit:hover { background: rgba(255,255,255,0.2); }

/* Main content */
.exam-main { flex: 1; overflow: hidden; display: flex; flex-direction: column; background: var(--sand); }

.question-container { flex: 1; padding: 24px; overflow-y: auto; max-width: 800px; margin: 0 auto; width: 100%; }
.question-type { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; background: var(--lavender); color: #4a3e9a; margin-bottom: 14px; }
.question-text { font-family: 'DM Serif Display', serif; font-size: 22px; line-height: 1.4; margin-bottom: 20px; color: var(--ink); }

.question-media { margin-bottom: 20px; }
.question-image { max-width: 100%; max-height: 200px; border-radius: var(--radius); }
.audio-player { display: flex; align-items: center; gap: 10px; padding: 14px; background: var(--surface); border-radius: var(--radius); border: 1px solid var(--border); }
.audio-btn { width: 44px; height: 44px; border-radius: 50%; background: var(--sage-dark); border: none; color: #fff; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: transform 0.15s; }
.audio-btn:hover { transform: scale(1.05); }
.audio-wave { flex: 1; height: 40px; background: linear-gradient(90deg, var(--sage-mid) 0%, var(--sage-dark) 50%, var(--sage-mid) 100%); border-radius: 10px; position: relative; overflow: hidden; }
.audio-wave::after { content: ''; position: absolute; inset: 0; background: repeating-linear-gradient(90deg, transparent, transparent 4px, rgba(255,255,255,0.3) 4px, rgba(255,255,255,0.3) 8px); animation: wave 0.8s linear infinite; }
@keyframes wave { 0% { transform: translateX(-16px); } 100% { transform: translateX(0); } }
.audio-btn.playing + .audio-wave::after { animation-play-state: running; }
.audio-btn:not(.playing) + .audio-wave::after { animation-play-state: paused; }

.hint { padding: 10px 14px; background: var(--butter); border-radius: var(--radius); font-size: 12px; color: #7a5c1e; margin-bottom: 16px; display: flex; align-items: flex-start; gap: 8px; }
.hint-icon { flex-shrink: 0; }

/* Answer options */
.answer-section { margin-top: 20px; }
.answer-label { font-size: 12px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.8px; color: var(--muted); margin-bottom: 10px; }

.options-grid { display: grid; gap: 10px; }
.option-btn { display: flex; align-items: center; gap: 12px; padding: 14px 16px; background: var(--surface); border: 2px solid var(--border); border-radius: var(--radius); cursor: pointer; transition: all 0.15s; font-family: 'DM Sans', sans-serif; font-size: 14px; text-align: left; width: 100%; }
.option-btn:hover { border-color: var(--sage-mid); background: var(--sage); }
.option-btn.selected { border-color: var(--sage-dark); background: var(--sage); }
.option-letter { width: 28px; height: 28px; border-radius: 50%; background: var(--sand-mid); display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 13px; flex-shrink: 0; }
.option-btn.selected .option-letter { background: var(--sage-dark); color: #fff; }
.option-text { flex: 1; }
.option-check { width: 22px; height: 22px; border-radius: 50%; border: 2px solid var(--border); flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 12px; }
.option-btn.selected .option-check { border-color: var(--sage-dark); background: var(--sage-dark); color: #fff; }

.text-answer { width: 100%; min-height: 120px; padding: 14px; border: 2px solid var(--border); border-radius: var(--radius); font-size: 15px; font-family: 'DM Sans', sans-serif; resize: vertical; background: var(--surface); }
.text-answer:focus { outline: none; border-color: var(--sage-dark); }

/* Recording for speaking */
.recording-section { margin-top: 16px; }
.record-btn { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; padding: 16px; border: 2px dashed var(--border); border-radius: var(--radius); background: transparent; cursor: pointer; font-family: 'DM Sans', sans-serif; font-size: 14px; transition: all 0.15s; }
.record-btn:hover { border-color: var(--sage-dark); background: rgba(198, 223, 213, 0.2); }
.record-btn.recording { border-color: #ff6b6b; background: rgba(255, 107, 107, 0.1); color: #ff6b6b; animation: pulse-border 1s infinite; }
@keyframes pulse-border { 0%, 100% { border-color: #ff6b6b; } 50% { border-color: #ff9999; } }
.record-icon { font-size: 24px; }
.record-time { font-family: 'DM Serif Display', serif; font-size: 18px; }
.recording-preview { margin-top: 12px; padding: 14px; background: var(--surface); border-radius: var(--radius); display: flex; align-items: center; gap: 12px; }

/* Footer navigation */
.exam-footer { background: var(--surface); border-top: 1px solid var(--border); padding: 14px 20px; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
.question-nav { display: flex; gap: 6px; flex-wrap: wrap; max-width: 70%; }
.nav-dot { width: 28px; height: 28px; border-radius: 50%; border: 2px solid var(--border); background: var(--surface); font-size: 11px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.15s; font-family: 'DM Sans', sans-serif; }
.nav-dot:hover { border-color: var(--sage-mid); }
.nav-dot.answered { background: var(--sage); border-color: var(--sage-dark); }
.nav-dot.current { border-color: var(--sage-dark); border-width: 3px; }
.nav-dots-label { font-size: 11px; color: var(--muted); margin-left: 8px; align-self: center; }

.nav-buttons { display: flex; gap: 10px; }
.btn-nav { padding: 10px 20px; border-radius: 30px; font-weight: 600; font-size: 14px; cursor: pointer; border: none; font-family: 'DM Sans', sans-serif; display: flex; align-items: center; gap: 6px; transition: all 0.15s; }
.btn-prev { background: var(--sand-mid); color: var(--ink); }
.btn-prev:hover { background: var(--sand-dark); }
.btn-next { background: var(--ink); color: #fff; }
.btn-next:hover { opacity: 0.85; }
.btn-submit { background: var(--sage-dark); color: #fff; }
.btn-submit:hover { opacity: 0.85; }

/* Progress bar */
.progress-bar { height: 4px; background: rgba(255,255,255,0.1); }
.progress-fill { height: 100%; background: linear-gradient(90deg, var(--sage), var(--butter)); transition: width 0.3s ease; }

/* Auto-save indicator */
.autosave { position: fixed; bottom: 80px; right: 20px; padding: 8px 14px; background: var(--sage-dark); color: #fff; border-radius: 20px; font-size: 12px; opacity: 0; transition: opacity 0.3s; }
.autosave.show { opacity: 1; }

/* Question image modal */
.image-modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.9); z-index: 1000; align-items: center; justify-content: center; padding: 20px; }
.image-modal.open { display: flex; }
.image-modal img { max-width: 100%; max-height: 90vh; border-radius: var(--radius); }
.image-modal-close { position: absolute; top: 20px; right: 20px; width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,0.2); border: none; color: #fff; font-size: 24px; cursor: pointer; }

/* Responsive */
@media (max-width: 768px) {
    .exam-wrapper { height: 100vh; }
    .question-container { padding: 16px; }
    .question-text { font-size: 18px; }
    .timer { font-size: 18px; }
    .exam-title { font-size: 14px; }
    .question-nav { max-width: 100%; }
    .nav-buttons { flex: 1; justify-content: flex-end; }
}
</style>
</head>
<body>

<div class="exam-wrapper">
    <!-- Header -->
    <div class="exam-header">
        <div>
            <div class="exam-title"><?= htmlspecialchars($attempt['title']) ?></div>
            <div class="exam-progress"><?= strtoupper($attempt['exam_language']) ?> EXAM · <?= t('level') ?> <?= $attempt['level_number'] ?></div>
        </div>
        
        <div class="timer" id="timer">
            <span class="timer-icon">⏱️</span>
            <span id="timerDisplay">--:--</span>
        </div>
        
        <div class="header-actions">
            <button class="btn-exit" onclick="confirmExit()">Exit Exam</button>
        </div>
        
        <div class="progress-bar">
            <div class="progress-fill" id="progressFill" style="width: 0%"></div>
        </div>
    </div>
    
    <!-- Main question area -->
    <div class="exam-main">
        <div class="question-container" id="questionContainer">
            <!-- Question will be loaded here -->
        </div>
    </div>
    
    <!-- Footer navigation -->
    <div class="exam-footer">
        <div class="question-nav" id="questionNav">
            <!-- Nav dots will be generated here -->
        </div>
        <div class="nav-buttons">
            <button class="btn-nav btn-prev" id="prevBtn" onclick="prevQuestion()">← Prev</button>
            <button class="btn-nav btn-next" id="nextBtn" onclick="nextQuestion()">Next →</button>
            <button class="btn-nav btn-submit hidden" id="submitBtn" onclick="submitExam()">Submit Exam</button>
        </div>
    </div>
</div>

<!-- Auto-save indicator -->
<div class="autosave" id="autosave">✓ Saved</div>

<!-- Image modal -->
<div class="image-modal" id="imageModal" onclick="closeImageModal()">
    <button class="image-modal-close">×</button>
    <img id="modalImage" src="" alt="">
</div>

<script>
const attemptId = <?= $attempt_id ?>;
const questions = <?= $questions_json ?>;
const savedAnswers = <?= $saved_answers_json ?>;
const totalTime = <?= $attempt['time_remaining_seconds'] ?>; // seconds
let currentIndex = <?= $attempt['current_question_index'] ?? 0 ?>;
let timeRemaining = <?= $attempt['time_remaining_seconds'] ?>;
let audioPlaying = false;
let audioContext = null;
let isSubmittingQuestion = false;

// Translation strings
const translations = {
    en: {
        reading: '📖 Reading',
        listening: '🎧 Listening',
        translation: '🔤 Translation',
        speaking: '🎤 Speaking',
        writing: '✍️ Writing',
        select_answer: 'Select your answer',
        type_answer: 'Type your answer',
        record_speak: 'Record your answer',
        recording: 'Recording...',
        prev: '← Prev',
        next: 'Next →',
        submit: 'Submit Exam',
        question_of: 'of',
        hint: '💡 Hint',
        exit_confirm: 'Are you sure you want to exit? Your progress will be saved.',
        submit_confirm: 'Are you sure you want to submit? You cannot change your answers after submission.',
        time_warning: 'Time warning: Less than 5 minutes remaining!',
        time_critical: 'Time critical: Less than 1 minute!'
    },
    rw: {
        reading: '📖 Gusoma',
        listening: '🎧 Kumva',
        translation: '🔤 Guhindura',
        speaking: '🎤 Kuvuga',
        writing: '✍️ Kwandika',
        select_answer: 'Hitamo igisubizo',
        type_answer: 'Andika igisubizo',
        record_speak: 'Rekoda igisubizo',
        recording: 'Birakomeza...',
        prev: '← Yashe',
        next: 'Ikurikira →',
        submit: 'Tanga Ibizamini',
        question_of: 'bya',
        hint: '💡 Inyobolanire',
        exit_confirm: 'Wemeje ko ushaka gusohoka? Iterambere ryawe rizabikwa.',
        submit_confirm: 'Wemeje ko ushaka kuguha? Unashobora guhindura ibisubizo byawe inyuma y\'kugena.',
        time_warning: 'Igihe biri hasi: Munsi y\'im protokol nkeya!',
        time_critical: 'Igihe biri hanze: Munsi y\'umunsi umwe!'
    }
};

function t(key) {
    return translations['<?= $ui_lang ?>'][key] || translations.en[key] || key;
}

// Initialize
function init() {
    renderQuestionNav();
    loadQuestion(currentIndex);
    startTimer();
}

// Render navigation dots
function renderQuestionNav() {
    const nav = document.getElementById('questionNav');
    nav.innerHTML = '';
    
    questions.forEach((q, i) => {
        const dot = document.createElement('button');
        dot.className = 'nav-dot';
        dot.textContent = i + 1;
        if (savedAnswers[q.id]) dot.classList.add('answered');
        if (i === currentIndex) dot.classList.add('current');
        dot.onclick = () => loadQuestion(i);
        nav.appendChild(dot);
    });
    
    const label = document.createElement('span');
    label.className = 'nav-dots-label';
    label.textContent = `${currentIndex + 1} ${t('question_of')} ${questions.length}`;
    nav.appendChild(label);
}

// Load question
function loadQuestion(index) {
    if (index < 0 || index >= questions.length) return;
    
    currentIndex = index;
    renderQuestionNav();
    const q = questions[index];
    const container = document.getElementById('questionContainer');
    
    // Update progress
    const progress = ((index + 1) / questions.length) * 100;
    document.getElementById('progressFill').style.width = progress + '%';
    
    // Build question HTML
    let html = `
        <div class="question-type">${t(q.question_type)}</div>
        <div class="question-text">${escapeHtml(q.question_text)}</div>
    `;
    
    // Media
    if (q.question_image) {
        html += `
            <div class="question-media">
                <img src="${q.question_image}" alt="Question image" class="question-image" onclick="openImageModal('${q.question_image}')" style="cursor: pointer;">
            </div>
        `;
    }
    
    if (q.question_audio) {
        html += `
            <div class="question-media">
                <div class="audio-player">
                    <button class="audio-btn" id="audioBtn" onclick="toggleAudio('${q.question_audio}')">▶️</button>
                    <div class="audio-wave"></div>
                </div>
            </div>
        `;
    }
    
    // Hint
    if (q.hints) {
        html += `
            <div class="hint">
                <span class="hint-icon">${t('hint')}</span>
                <span>${escapeHtml(q.hints)}</span>
            </div>
        `;
    }
    
    // Answer section
    html += '<div class="answer-section">';
    html += `<div class="answer-label">${t(q.question_type === 'speaking' ? 'record_speak' : (q.options ? 'select_answer' : 'type_answer'))}</div>`;
    
    if (q.options) {
        // Multiple choice
        html += '<div class="options-grid">';
        const letters = ['A', 'B', 'C', 'D', 'E', 'F'];
        q.options.forEach((opt, i) => {
            const isSelected = savedAnswers[q.id] === opt;
            html += `
                <button class="option-btn ${isSelected ? 'selected' : ''}" onclick="selectOption(${q.id}, '${escapeHtml(opt).replace(/'/g, "\\'")}')">
                    <span class="option-letter">${letters[i]}</span>
                    <span class="option-text">${escapeHtml(opt)}</span>
                    <span class="option-check">${isSelected ? '✓' : ''}</span>
                </button>
            `;
        });
        html += '</div>';

    } else if (q.question_type === 'speaking') {
        // Speaking - recording
        html += `
            <button class="record-btn" id="recordBtn" onclick="toggleRecording()">
                <span class="record-icon">🎤</span>
                <span id="recordText">${t('record_speak')}</span>
                <span class="record-time" id="recordTime"></span>
            </button>
            <div class="recording-preview" id="recordingPreview" style="display: none;">
                <span>🎵</span>
                <span id="recordingStatus">Recording saved</span>
            </div>
        `;
    } else {
        // Text answer
        html += `
            <textarea class="text-answer" id="textAnswer" placeholder="${t('type_answer')}" 
                oninput="saveTextAnswer(${q.id}, this.value)">${savedAnswers[q.id] || ''}</textarea>
            <button type="button" class="btn-nav btn-submit" id="continueBtn">Continue</button>
        `;
    }
    
    html += '</div>';
    container.innerHTML = html;
    
    // Auto-submit / advance for text answers
    const answerInput = document.getElementById('textAnswer');
    if (answerInput) {
        answerInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                submitTextAnswer();
            }
        });
    }

    const continueBtn = document.getElementById('continueBtn');
    if (continueBtn) {
        continueBtn.onclick = (e) => {
            e.preventDefault();
            submitTextAnswer();
        };
    }
    
    // Update nav buttons
    document.getElementById('prevBtn').disabled = index === 0;
    
    if (index === questions.length - 1) {
        document.getElementById('nextBtn').classList.add('hidden');
        document.getElementById('submitBtn').classList.remove('hidden');
    } else {
        document.getElementById('nextBtn').classList.remove('hidden');
        document.getElementById('submitBtn').classList.add('hidden');
    }
}

// Navigation
function prevQuestion() {
    if (currentIndex > 0) {
        saveAnswer(); // Save current before moving
        loadQuestion(currentIndex - 1);
    }
}

function nextQuestion() {
    if (currentIndex < questions.length - 1) {
        saveAnswer(); // Save current before moving
        loadQuestion(currentIndex + 1);
    }
}

// Select option
async function selectOption(questionId, answer) {
    savedAnswers[questionId] = answer;
    
    // Update UI
    document.querySelectorAll('.option-btn').forEach(btn => {
        const isSelected = btn.querySelector('.option-text').textContent === answer;
        btn.classList.toggle('selected', isSelected);
        btn.querySelector('.option-check').textContent = isSelected ? '✓' : '';
    });
    
    // Auto-save
    await saveAnswer(questionId, answer);

}

// Text answer
function saveTextAnswer(questionId, value) {
    savedAnswers[questionId] = value;
    saveAnswer(questionId, value);
}

async function submitTextAnswer() {
    if (isSubmittingQuestion) return;
    const answerInput = document.getElementById('textAnswer');
    const continueBtn = document.getElementById('continueBtn');
    if (!answerInput) return;

    const value = (answerInput.value || '').trim();
    if (!value) {
        alert('Please type an answer before submitting.');
        return;
    }

    isSubmittingQuestion = true;
    answerInput.disabled = true;
    if (continueBtn) continueBtn.disabled = true;

    const questionId = questions[currentIndex].id;
    savedAnswers[questionId] = value;
    const savePromise = saveAnswer(questionId, value).catch(() => {});

    if (currentIndex < questions.length - 1) {
        loadQuestion(currentIndex + 1);
    } else {
        submitExam();
    }

    await savePromise;
    isSubmittingQuestion = false;
}

// Save answer to server
async function saveAnswer(questionId, answer) {
    questionId = questionId || questions[currentIndex].id;
    answer = answer || savedAnswers[questionId] || '';
    
    try {
        const formData = new FormData();
        formData.append('attempt_id', attemptId);
        formData.append('question_id', questionId);
        formData.append('answer_text', answer);
        formData.append('time_spent', 5); // Approximate
        
        await fetch('exam-api.php?action=save_answer', {
            method: 'POST',
            body: formData
        });
        
        // Update nav dot
        const index = questions.findIndex(q => q.id === questionId);
        if (index !== -1) {
            const dots = document.querySelectorAll('.nav-dot');
            if (dots[index] && answer) {
                dots[index].classList.add('answered');
            }
        }
        renderQuestionNav();
        showAutosave();
    } catch (error) {
        console.error('Error saving answer:', error);
    }
}

// Audio
function toggleAudio(url) {
    if (!audioContext) {
        audioContext = new (window.AudioContext || window.webkitAudioContext)();
    }
    
    if (audioPlaying) {
        // Stop
        audioPlaying = false;
        document.getElementById('audioBtn').textContent = '▶️';
        return;
    }
    
    // Simple TTS simulation using Web Speech API if available
    if ('speechSynthesis' in window) {
        const utterance = new SpeechSynthesisUtterance(questions[currentIndex].question_text);
        utterance.lang = '<?= $attempt['exam_language'] === 'en' ? 'en-US' : 'fr-FR' ?>';
        utterance.onend = () => {
            audioPlaying = false;
            document.getElementById('audioBtn').textContent = '▶️';
        };
        speechSynthesis.speak(utterance);
        audioPlaying = true;
        document.getElementById('audioBtn').textContent = '⏸️';
    }
}

// Recording
let mediaRecorder = null;
let recordingChunks = [];
let recordingStartTime = null;
let recordingInterval = null;

async function toggleRecording() {
    const btn = document.getElementById('recordBtn');
    const recordText = document.getElementById('recordText');
    const recordTime = document.getElementById('recordTime');
    const preview = document.getElementById('recordingPreview');
    
    if (btn.classList.contains('recording')) {
        // Stop recording
        mediaRecorder.stop();
        clearInterval(recordingInterval);
        btn.classList.remove('recording');
        recordText.textContent = t('record_speak');
        preview.style.display = 'flex';
        document.getElementById('recordingStatus').textContent = 'Recording saved';
    } else {
        // Start recording
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            mediaRecorder = new MediaRecorder(stream);
            recordingChunks = [];
            
            mediaRecorder.ondataavailable = (e) => {
                if (e.data.size > 0) {
                    recordingChunks.push(e.data);
                }
            };
            
            mediaRecorder.onstop = async () => {
                const blob = new Blob(recordingChunks, { type: 'audio/webm' });
                const reader = new FileReader();
                reader.onloadend = async () => {
                    const audioData = reader.result;
                    // Save audio answer
                    savedAnswers[questions[currentIndex].id] = '[AUDIO_RECORDING]';
                    await saveAnswer(questions[currentIndex].id, '[AUDIO_RECORDING]');
                    preview.style.display = 'flex';
                    if (currentIndex < questions.length - 1) {
                        loadQuestion(currentIndex + 1);
                    }
                };
                reader.readAsDataURL(blob);
                
                stream.getTracks().forEach(track => track.stop());
            };
            
            mediaRecorder.start();
            btn.classList.add('recording');
            recordText.textContent = t('recording');
            recordingStartTime = Date.now();
            
            recordingInterval = setInterval(() => {
                const elapsed = Math.floor((Date.now() - recordingStartTime) / 1000);
                const mins = Math.floor(elapsed / 60);
                const secs = elapsed % 60;
                recordTime.textContent = `${mins}:${secs.toString().padStart(2, '0')}`;
            }, 1000);
            
        } catch (error) {
            console.error('Recording error:', error);
            alert('Could not access microphone. Please check permissions.');
        }
    }
}

// Timer
function startTimer() {
    updateTimerDisplay();
    setInterval(() => {
        if (timeRemaining > 0) {
            timeRemaining--;
            updateTimerDisplay();
        } else {
            // Time's up - auto submit
            submitExam();
        }
    }, 1000);
}

function updateTimerDisplay() {
    const mins = Math.floor(timeRemaining / 60);
    const secs = timeRemaining % 60;
    const display = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    document.getElementById('timerDisplay').textContent = display;
    
    const timer = document.getElementById('timer');
    timer.classList.remove('warning', 'danger');
    
    if (timeRemaining <= 60) {
        timer.classList.add('danger');
    } else if (timeRemaining <= 300) {
        timer.classList.add('warning');
    }
}

// Image modal
function openImageModal(src) {
    document.getElementById('modalImage').src = src;
    document.getElementById('imageModal').classList.add('open');
}

function closeImageModal() {
    document.getElementById('imageModal').classList.remove('open');
}

// Submit exam
async function submitExam() {
    if (!confirm(t('submit_confirm'))) return;
    
    try {
        const formData = new FormData();
        formData.append('attempt_id', attemptId);
        
        const response = await fetch('exam-api.php?action=submit_exam', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.href = 'exam-results.php?attempt_id=' + attemptId + '&ui_lang=<?= $ui_lang ?>';
        } else {
            alert(data.message || 'Failed to submit exam');
        }
    } catch (error) {
        console.error('Error submitting exam:', error);
        alert('Failed to submit exam. Please try again.');
    }
}

// Exit confirmation
function confirmExit() {
    if (confirm(t('exit_confirm'))) {
        // Save progress and redirect
        window.location.href = 'exam-portal.php?ui_lang=<?= $ui_lang ?>';
    }
}

// Auto-save indicator
function showAutosave() {
    const el = document.getElementById('autosave');
    el.classList.add('show');
    setTimeout(() => el.classList.remove('show'), 1500);
}

// Escape HTML
function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, m => ({'&': '&amp;', '<': '&lt;', '>': '&gt;'}[m] || m));
}

// Initialize on load
init();

// Auto-save every 30 seconds
setInterval(() => {
    if (savedAnswers[questions[currentIndex]?.id]) {
        saveAnswer();
    }
}, 30000);
</script>

</body>
</html>
