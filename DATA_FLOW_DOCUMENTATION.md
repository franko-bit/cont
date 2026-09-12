# Exam Data Flow - Complete Implementation

## Data Flow Summary

The exam taking process now properly links all data across multiple tables:

### 1. **Start Exam Flow**
- User clicks "Start Exam" on dashboard
- User directed to `exam_selection.php` 
- **Displays:** Academic English, Business English (only 2 exam types)
- User selects exam type and clicks "Continue"

### 2. **Popcorn Application & Institution Candidate Creation**
- Redirects to `apply-popcorn.php`
- **Collects:**
  - `applicant_name` (full name of person taking exam)
  - `school_name` (institution name)
  - `popcorn_code` (optional)
  - Exam type (Academic/Business)
- **Creates records in:**
  - `exam_attempts` table with `user_id`, `exam_id`, `status='verification'`
  - `popcorn_applications` table with `applicant_name`, `school_name`
  - `institution_candidates` table with `full_name` = `applicant_name`, `email`, `exam_attempt_id` (link to exam_attempts)

### 3. **Exam Taking**
- User completes verification and starts exam
- Takes exam in `frontend/lessonexam.php`
- Answers recorded in `exam_answers` table
- Exam timer runs for 60 minutes

### 4. **Exam Submission**
- User completes all questions or time expires
- Frontend calls `backend/exam-api.php?action=submit_lessonexam`
- **Backend processes:**
  - Validates `exam_attempts` record exists
  - Calculates final score
  - Updates `exam_attempts`: status='submitted', score, certificate_id
  - Creates `certificates` record with:
    - `student_name` retrieved from `institution_candidates.full_name` (applicant_name)
    - `score`, `language_pair`, `level_number`
    - `status='pending'` (awaiting approval)
  - Updates `institution_candidates`: score, status='completed', exam_status='submitted'

### 5. **Dashboard Display**

**Completed Work Section (try.php):**
- Shows all students with completed exams
- Data source: `institution_candidates` with score values
- Displays: Student name (applicant_name), school, exam, score, time taken

**Certificates Section (try.php):**
- **Awaiting Approval:** Shows pending certificates with applicant names
- **Issued:** Shows approved certificates
- Data source: `certificates` table

---

## Table Relationships

```
exam_selection.php (choose exam type)
        ↓
apply-popcorn.php
        ↓
    Creates/Updates:
    ├─ exam_attempts (user_id, exam_id, status)
    ├─ popcorn_applications (applicant_name, school_name, attempt_id)
    └─ institution_candidates (full_name, email, exam_attempt_id) ← LINKS TO exam_attempts
        ↓
frontend/lessonexam.php (takes exam)
        ↓
exam_answers (question responses recorded)
        ↓
backend/exam-api.php - submitLessonExam()
        ↓
    Updates:
    ├─ exam_attempts (status='submitted', score, certificate_id)
    ├─ certificates (student_name FROM institution_candidates.full_name, score, status='pending')
    └─ institution_candidates (score, status='completed', exam_status='submitted')
        ↓
try.php Dashboard
        ├─ Completed Work (reads institution_candidates with score)
        └─ Certificates (reads certificates with status='pending' or 'approved')
```

---

## Key Improvements Made

1. **Applicant Name Source:** Uses `applicant_name` from popcorn application as the primary student identifier
2. **Institution Candidates Linking:** Properly links `institution_candidates` to `exam_attempts` via `exam_attempt_id`
3. **Score Synchronization:** Ensures scores appear consistently in:
   - `institution_candidates.score` (for "Completed Work")
   - `certificates.score` (for "Certificates")
4. **Certificate Names:** Uses applicant name from `institution_candidates.full_name` instead of user profile
5. **Exam Type Filtering:** Only shows Academic English and Business English exam types

---

## Data Consistency Guarantees

✓ Same applicant name appears in Completed Work and Certificates  
✓ Scores sync across institution_candidates and certificates tables  
✓ Exam type is properly tracked (Academic/Business)  
✓ Institution association maintained via institution_id in institution_candidates  
✓ All records linked via exam_attempt_id for traceability  

---

## Testing the Flow

1. Go to dashboard
2. Click "Start Exam"
3. Select exam type (Academic or Business)
4. Enter applicant name and school
5. Complete exam
6. Check try.php:
   - "Completed Work" shows the applicant with score
   - "Certificates" shows certificate awaiting approval with same name
