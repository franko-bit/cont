# Data Flow Analysis: When Is Data Being Saved?

## Timeline of Data Saves

### 1. **exam_selection.php** (User selects exam type)
```
✓ NO DATABASE WRITES
- Just displays Academic English vs Business English buttons
- When selected, passes exam type to next page
```

---

### 2. **POPCORN APPLICATION (apply-popcorn.php)** ⚠️ DATA SAVED HERE
**Location:** `backend/apply-popcorn.php` lines 87-152

**What gets saved immediately when user clicks "Apply":**

#### A. `popcorn_applications` table INSERT
```sql
INSERT INTO popcorn_applications (
    popcorn_id,
    popcorn_code,
    institution_id,
    applicant_name,           -- ← From user input
    school_name,              -- ← From user input
    user_id,
    attempt_id,
    status = 'applied',       -- INITIAL STATUS
    applied_at = NOW()
)
```
**Data saved:** Applicant name, school, popcorn code, institution ID

#### B. `exam_attempts` table INSERT/FIND
```sql
-- If user doesn't have an existing pending attempt for this exam:
INSERT INTO exam_attempts (
    user_id,
    exam_id,
    status = 'verification',   -- ← NOT 'in_progress' yet!
    verification_status = 'pending',
    ip_address,
    user_agent
)

-- OR if attempt already exists:
SELECT id FROM exam_attempts 
WHERE user_id = ? AND exam_id = ? 
AND status IN ('pending', 'verification', 'in_progress')
```
**Data saved:** Exam attempt record created with status='verification'

#### C. `institution_candidates` table INSERT ⚠️ THIS IS THE EARLY SAVE
```sql
INSERT INTO institution_candidates (
    institution_id,
    assessment_id,
    user_id,
    full_name = applicant_name,    -- ← Applicant's name
    email,                          -- ← From user record
    exam_attempt_id,                -- ← Links to exam_attempts
    status = 'started',             -- ← MARKED AS STARTED TOO EARLY!
    invited_at = NOW(),
    created_at = NOW(),
    updated_at = NOW()
)
```
**Data saved:** Candidate marked as "started" even though exam hasn't begun!

---

### 3. **onboard.php** (ID verification screen)
```
✓ NO PERMANENT DATABASE WRITES
- Captures selfie and ID photos
- Uploads to storage (temp files)
- Updates exam_verifications table (separate concern)
- Does NOT save exam scores or final results
```

---

### 4. **lessonexam.php** (During exam - OPTIONAL saves)
**Location:** `backend/exam-api.php` - `saveAnswer()` function

**Optional:** When user's answer is submitted:
```sql
-- For each question answered:
INSERT INTO exam_answers (
    session_id = attempt_id,
    question_id,
    user_answer = user_input
)
-- OR UPDATE if answer already exists
```
**Important:** This is just answer storage. NO SCORES are calculated yet.
**Note:** `exam_attempts.status` is still 'in_progress' or 'verification'

---

### 5. **FINAL EXAM SUBMISSION** (submitLessonExam) 🔴 CRITICAL - ONLY THIS SHOULD SAVE FINAL RESULTS
**Location:** `backend/exam-api.php` lines 555-630

**When user clicks "Submit Exam":**

#### Step 1: Validate attempt
```sql
SELECT * FROM exam_attempts 
WHERE id = ? AND user_id = ? 
AND status IN ('pending', 'verification', 'in_progress')
```
**Check:** Attempt must exist and be in valid status

#### Step 2: Update exam_attempts with FINAL RESULTS
```sql
UPDATE exam_attempts SET
    status = 'submitted',      -- ← EXAM NOW COMPLETE
    submitted_at = NOW(),
    graded_at = NOW(),
    score = ?,                 -- ← FINAL SCORE SAVED HERE
    passed = ?,                -- ← PASS/FAIL DETERMINED
    certificate_id = ?         -- ← Certificate ID generated if passed
WHERE id = ?
```

#### Step 3: Link to institution_candidates
```sql
UPDATE institution_candidates SET
    exam_attempt_id = ?,       -- Links to exam_attempts
    status = 'completed',      -- ← STATUS UPDATED FROM 'started'
    score = ?,                 -- ← FINAL SCORE SAVED
    exam_status = 'submitted',
    exam_score = ?,
    time_taken_seconds = ?,
    exam_completed_at = NOW(),
    updated_at = NOW()
WHERE id = ?
```

#### Step 4: Create certificate record
```sql
INSERT INTO certificates (
    certificate_id,
    student_name,              -- ← From institution_candidates.full_name
    user_id,
    exam_attempt_id,
    exam_id,
    score,                     -- ← FINAL SCORE
    percentage,
    passed,
    status = 'pending',        -- Awaiting approval
    created_at = NOW()
)
```

---

## 🚨 THE PROBLEM

### Data is being saved TOO EARLY:
1. **When `apply-popcorn.php` runs** (user applies popcorn code):
   - `institution_candidates` record is created with `status='started'`
   - This happens BEFORE the exam begins
   
2. **What happens if user abandons exam:**
   - The `institution_candidates` record remains in database
   - Shows as "started" in the dashboard
   - No exam score is saved (because exam wasn't completed)
   - BUT the candidate entry exists with incomplete data

### Current state:
- ✓ GOOD: Scores are NOT saved until final submission
- ✗ BAD: Candidate record is created too early (at popcorn application time)
- ✗ BAD: Dashboard shows "started" candidates who may never complete

---

## Database Schema Impact

### `institution_candidates` table
```
id | institution_id | user_id | full_name | status       | score | exam_attempt_id
   |        1        |    5    | John Doe  | 'started'    | NULL  |       42
   |        1        |    6    | Jane Doe  | 'completed'  | 95.5  |       43
   |        1        |    7    | Bob Smith | 'started'    | NULL  |       44   ← Problem: No exam completed yet
```

### `exam_attempts` table
```
id | user_id | exam_id | status          | score | passed | submitted_at
42 |    5    |    1    | 'in_progress'   | NULL  |   0    | NULL          ← In progress
43 |    6    |    1    | 'submitted'     | 95.5  |   1    | 2026-05-22    ← Completed
44 |    7    |    1    | 'verification'  | NULL  |   0    | NULL          ← Started but not in progress
```

---

## Recommended Fix

### Option 1: Delay institution_candidates creation until exam completion
```php
// In submitLessonExam(), instead of creating in apply-popcorn.php:
// Only create institution_candidates when exam is actually submitted

// Remove: INSERT from apply-popcorn.php
// Move: INSERT to submitLessonExam() when score is finalized
```

### Option 2: Track status more accurately
```php
// Keep early creation but change status logic:
// - status = 'invited' when first added (not 'started')
// - status = 'started' when exam actually begins (lessonexam.php loads)
// - status = 'completed' when exam submitted with score
// - status = 'abandoned' after X time with no submission
```

### Option 3: Use different table for applications vs completed candidates
```php
// popcorn_applications → for application tracking
// institution_candidates → ONLY for actual completed/graded exams
```

---

## Current Code Locations

| File | Action | When | Data Saved |
|------|--------|------|-----------|
| `backend/apply-popcorn.php` | Apply popcorn code | User clicks apply | popcorn_applications, exam_attempts, institution_candidates |
| `backend/exam-api.php` saveAnswer() | Answer submission | During exam | exam_answers |
| `backend/exam-api.php` submitLessonExam() | **SUBMIT EXAM** | **User completes exam** | **exam_attempts (final), institution_candidates (final), certificates** |

---

## Summary: When Data Is Actually Safe

✓ **SAFE to see in dashboard:**
- Only records with `institution_candidates.status = 'completed'` and `score IS NOT NULL`

✗ **NOT SAFE to count as completed:**
- `institution_candidates.status = 'started'` with `score IS NULL`
- These are "in progress" applicants who may abandon

✓ **TRULY FINAL & IMMUTABLE:**
- Only when `exam_attempts.status = 'submitted'` AND `certificates` record exists

