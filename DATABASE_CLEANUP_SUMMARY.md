# Database Cleanup & Simplification - Complete

## Changes Made (May 22, 2026)

### Backend Code Changes
**File: `backend/exam-api.php`**

1. **Removed case handler** for `get_questions` action (line 30)
   - This endpoint was calling `getExamQuestions()` which queried `exam_questions` table
   - No longer needed; questions now loaded from YAML files

2. **Removed functions** (lines 58-72, 236-256, 492-609)
   - `examResultsHasColumn()` - checked if exam_results table had certain columns
   - `getExamResultsRow()` - fallback query to exam_results table
   - `getExamQuestions()` - loaded questions from database (now uses YAML)

3. **Simplified getResults()** function
   - Removed fallback query to `exam_results` table
   - Now only reads from `certificates` table

### Database Cleanup Script

**File: `database/cleanup-unused-tables.sql`** - Ready to run

Drops these unused tables:
- `exam_results` - Never populated; all scoring data goes to certificates instead
- `exam_sessions` - Legacy; replaced by exam_attempts
- `institution_users` - Role-based permissions designed but never implemented

Keeps these tables:
- `exam_questions` - Conditionally kept (see below)

### Current Database Architecture (After Cleanup)

```
CORE EXAM TABLES (5):
├── exams - Exam definitions
├── exam_attempts - Exam sessions/attempts
├── exam_answers - User responses to questions
├── exam_questions - Question bank (kept for take-exam.php legacy path)
└── certificates - Issued certificates

VERIFICATION (1):
└── exam_verifications - ID checks, selfies, device verification

INSTITUTION MANAGEMENT (4):
├── institutions - Organization records
├── institution_assessments - Curated assessments per institution
├── institution_candidates - Applicants registered for assessment
└── institution_popcorns - Popcorn code inventory

APPLICATION (1):
└── popcorn_applications - Records of popcorn code usage

TOTAL: 11 active tables (or 10 if exam_questions is removed)
```

---

## About exam_questions Table

### Current Status: CONDITIONAL

The `exam_questions` table is still being used by the legacy `take-exam.php` interface:

```
take-exam.php → backend/exam-api.php (action='submit_exam')
              → gradeExam() function
              → queries exam_questions table
```

However, the **active exam interface** is `lessonexam.php`:
```
lessonexam.php → loads questions from YAML files
              → backend/exam-api.php (action='submit_lessonexam')
              → calculates scores directly, no DB query needed
```

### Recommendation

**Option A: Keep exam_questions (current)**
- Pro: Maintains backward compatibility with `take-exam.php`
- Con: Unused table takes up space; requires maintenance

**Option B: Remove exam_questions**
- Pro: Cleaner database; YAML is single source of truth
- Con: Must delete `take-exam.php` and `gradeExam()` function
- Steps needed:
  1. Delete `/frontend/take-exam.php`
  2. Delete `gradeExam()` function from `backend/exam-api.php`
  3. Delete `submitExam()` function from `backend/exam-api.php`
  4. Remove `case 'submit_exam'` from action switch
  5. Uncomment `DROP TABLE exam_questions` in cleanup script
  6. Run cleanup script

---

## Data Flow After Cleanup

### Current Active Path (Production)
```
User → exam_selection.php (choose Academic/Business)
    → apply-popcorn.php (enter name, apply code)
    → Creates: exam_attempts + institution_candidates + popcorn_applications
    → lessonexam.php (takes exam, loads questions from YAML)
    → Submits exam
    → Updates: exam_attempts (score, status)
    → Creates: certificates (student_name, score, status)
    → Dashboard shows results in try.php
```

### Removed Paths
- `take-exam.php` path (legacy) - can be safely removed
- `getExamQuestions()` endpoint - no longer used
- Fallback queries to `exam_results` - removed

---

## Migration Checklist

- [x] Removed `getExamQuestions()` function from exam-api.php
- [x] Removed `examResultsHasColumn()` helper function
- [x] Removed `getExamResultsRow()` function
- [x] Removed case handler for 'get_questions' action
- [x] Removed fallback queries to exam_results table
- [x] Created cleanup SQL script
- [ ] **Next Step: Run cleanup-unused-tables.sql** (when ready)
- [ ] Optional: Remove take-exam.php and gradeExam() if moving to YAML-only approach

---

## Files Modified

1. **backend/exam-api.php**
   - Removed 3 functions (~120 lines)
   - Removed 1 case handler
   - Simplified 1 function (getResults)
   - Total lines removed: ~140

2. **database/cleanup-unused-tables.sql** (NEW)
   - SQL script to drop unused tables
   - Conditional removal for exam_questions
   - Verification queries

---

## Impact Analysis

### What Still Works
-   Exam taking via lessonexam.php (YAML-based)
-   Answer recording
-   Certificate generation
-   Institution dashboard (try.php)
-   Verification/proctoring
-   Popcorn application flow

### What's Affected
- ❌ `/frontend/take-exam.php` path (legacy - was not actively used)
- ❌ API endpoint: `exam-api.php?action=get_questions` (not called by current UI)
- ❌ `exam_results` table queries (fallback removed, not used)

### No Breaking Changes
The current production exam flow (lessonexam.php) is **completely unaffected** by these cleanup changes.

---

## How to Execute Cleanup

When you're ready to clean up the database:

```bash
# From MySQL CLI or phpMyAdmin
source /path/to/language-platform/database/cleanup-unused-tables.sql;

# Or using PHP CLI
mysql -u root -p playmates < database/cleanup-unused-tables.sql
```

**Note:** Backup your database first!

---

## Questions?

Refer to the code comments:
- Line 212, 631: Error log messages mentioning "exam_results" (safe to keep)
- Line 663: `FROM exam_questions q` in gradeExam (only used by take-exam.php)

The system is now simpler with only 10-11 active tables instead of 13.
