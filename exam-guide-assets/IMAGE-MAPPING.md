# Image Mapping for Exam User Guide

## Exam Taking Flow - Image Naming Guide

This document explains how to identify and name each screenshot for the exam user guide.

### Total Images Needed: 9 Screenshots

---

## Image 1: Homepage
**Filename:** `01-homepage.png`

**What to look for:**
- The main Playmates landing page before login
- Shows different game categories (Language, Development Game, Coding Games, Classroom Games)
- This is the entry point to the platform

**Where it's used:**
- Step 1: Access the Homepage section
- Shows users where to start their exam journey

---

## Image 2: Login Page
**Filename:** `02-login.png`

**What to look for:**
- The login form/screen
- Has email and password input fields
- Login button
- May show "Create account" or sign up option

**Where it's used:**
- Step 2: Log In section
- Shows users how to authenticate

---

## Image 3: Language Dashboard
**Filename:** `03-language-dashboard.png`

**What to look for:**
- User's personal dashboard after login
- Shows user profile (e.g., "Murahu, Cyusa Fils")
- Displays stats: XP, Accuracy, Lessons Done, Words Learned
- Has button or link to "Take Exam" or start an exam
- May show activity charts or progress bars

**Where it's used:**
- Step 3: Go to Language Games section
- Shows users their learning progress and where to find the exam button

---

## Image 4: Exam Type Selection
**Filename:** `04-choose-exam-type.png`

**What to look for:**
- Dialog or page showing exam type options
- Shows: Academic English and Business English as choices
- Each exam type has a description
- Shows number of questions (40) and time (60 minutes)
- This is BEFORE entering the exam

**Where it's used:**
- Step 4: Choose Your Exam Type section
- Shows students the two exam options available

---

## Image 5: Before You Begin / Rules Page
**Filename:** `05-before-begin.png`

**What to look for:**
- Screen showing exam rules and conduct guidelines
- Lists important requirements:
  - Each question answered once only
  - 70% passing score
  - Must be alone in room
  - No notes/books/phones
  - Face must be visible
- May show a checkbox to "I agree to these rules"
- Verify button/checkbox to acknowledge

**Where it's used:**
- Step 5: Review Exam Rules & Conduct section
- Shows students what conduct is expected

---

## Image 6: Exam Overview
**Filename:** `06-exam-overview.png`

**What to look for:**
- Information card showing exam details BEFORE starting
- Shows: 20 questions, 330 XP, question type distribution
- Lists: Fixed Answer (10), Constructed Response (6), Speaking (2), Reading (2)
- States "No repeats" or "Balanced categories"
- Has "Start Exam" button to begin

**Where it's used:**
- Step 6: Review Exam Overview section
- Shows exam structure and content breakdown

---

## Image 7: Exam in Progress
**Filename:** `07-exam-in-progress.png`

**What to look for:**
- The actual exam interface during test-taking
- Shows a question being answered (e.g., word bank writing question)
- Progress bar at top showing completion
- Timer in top right
- Question counter (e.g., "Question X of 20")
- Navigation buttons (Previous/Next)
- May show a warning modal about tab switching with violation count (e.g., "Warning 3/7: You switched tabs")

**Where it's used:**
- Step 7: Take the Exam section
- Shows students what the exam interface looks like

---

## Image 8: Your Certificates
**Filename:** `08-your-certificates.png`

**What to look for:**
- The certificates page/section showing certificate cards
- Multiple certificate cards displayed
- Each card shows:
  - Certificate preview/thumbnail
  - User name
  - Exam type (Academic/Business English)
  - Score percentage
  - Issued date
  - Status badge (Verified, Pending, etc.)
  - Buttons to View or Download

**Where it's used:**
- Step 8: View Your Certificate section
- Shows how to access certificates after passing an exam

---

## Image 9: Certificate Verified & Approved
**Filename:** `09-certificate-verified.png`

**What to look for:**
- Full certificate display page (when clicking on a certificate)
- Shows "VERIFIED & APPROVED" or similar status banner
- Displays the actual certificate design
- Shows: User name, exam type, score, date issued, certificate ID
- Has "Download", "Print", or "Share" buttons
- Clean, professional-looking certificate layout

**Where it's used:**
- Step 9: Download or Share Your Certificate section
- Shows the final certificate product that users can download

---

## Excluded Images

**Popcorn Application Form** - NOT INCLUDED
- This is for institutional/school-based access only
- User guide focuses on individual exam-taking flow only
- Removed from the exam taking guide

---

## Naming Convention

All filenames follow the pattern:
```
NN-descriptive-name.png
```

Where:
- `NN` = Two-digit number (01, 02, 03, etc.) indicating order in the guide
- `descriptive-name` = Lowercase, hyphens for spaces
- `.png` = File format (PNG recommended for screenshots)

---

## File Location

Save all 9 images in:
```
c:/xampp/htdocs/language-platform/exam-guide-assets/
```

---

## Usage in HTML

In the `exam-guide-with-images.html` file, replace each placeholder div with:

```html
<img src="exam-guide-assets/[filename].png" alt="[description]">
```

Example:
```html
<img src="exam-guide-assets/01-homepage.png" alt="Homepage with course categories">
```

---

## Quality Guidelines

- **Format:** PNG (lossless, good for UI screenshots)
- **Resolution:** 800-1200px width recommended
- **File Size:** Keep under 300KB per image
- **Content:** Full screen or relevant section
- **Private Data:** Remove any personal information before saving
- **Consistency:** Maintain consistent lighting and zoom level across images
