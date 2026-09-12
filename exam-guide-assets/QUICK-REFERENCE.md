# Quick Reference: Screenshot Identification & Renaming

## How to Rename Your 11 Screenshots to 9 Guide Images

Use this guide to identify which of your screenshots goes where.

---

## Your Original 11 Screenshots

Here's what you provided (numbered 1-11 based on description order):

1.   Your Certificates page (cert cards, scores, verified status)
2.   Dashboard view (profile, XP, accuracy, lessons)
3.   Choose your exam page (Academic vs Business, 40 questions, 60 min)
4. ❌ **Apply for Popcorn form** (NOT USED - institutional only)
5.   Before you begin page (rules & conduct, identity check)
6.   Academic English Final Exam overview card (20 questions, 330 XP)
7.   Exam in progress (word bank question, warning modal "3/7 tab switches")
8.   Certificate Verified page (VERIFIED & APPROVED banner, print button)
9.   Login page (email/password form)
10.   Homepage (4 course categories: Language, Development Game, etc.)
11.   Exam type selection (Academic vs Business with descriptions)

**Notes:**
- Screenshot #3 and #11 appear to show the same exam type selection
- Screenshot #4 (Popcorn) is excluded - use only the other 9

---

## Mapping: Which Screenshot → Which Filename

| Guide Step | New Filename | Your Screenshot | What It Shows |
|-----------|--------------|-----------------|---------------|
| Step 1 | `01-homepage.png` | **#10** | Homepage with 4 course categories |
| Step 2 | `02-login.png` | **#9** | Login form with email/password |
| Step 3 | `03-language-dashboard.png` | **#2** | Dashboard with profile, XP, accuracy |
| Step 4 | `04-choose-exam-type.png` | **#3 or #11** | Choose Academic vs Business English |
| Step 5 | `05-before-begin.png` | **#5** | Rules & conduct verification page |
| Step 6 | `06-exam-overview.png` | **#6** | Exam info (20 questions, 330 XP) |
| Step 7 | `07-exam-in-progress.png` | **#7** | Exam interface with timer & progress |
| Step 8 | `08-your-certificates.png` | **#1** | Certificate cards page |
| Step 9 | `09-certificate-verified.png` | **#8** | Full certificate with VERIFIED banner |

---

## Step-by-Step Renaming Process

### Step 1: Access the Homepage
**From:** Screenshot #10  
**Rename to:** `01-homepage.png`  
**Look for:** Playmates main page showing course categories

### Step 2: Log In to Your Account
**From:** Screenshot #9  
**Rename to:** `02-login.png`  
**Look for:** Email and password input fields

### Step 3: Go to Language Games
**From:** Screenshot #2  
**Rename to:** `03-language-dashboard.png`  
**Look for:** User dashboard with "Murahu, Cyusa Fils" name, XP, accuracy stats

### Step 4: Choose Your Exam Type
**From:** Screenshot #3 or #11  
**Rename to:** `04-choose-exam-type.png`  
**Look for:** Two options - Academic English and Business English

### Step 5: Review Exam Rules & Conduct
**From:** Screenshot #5  
**Rename to:** `05-before-begin.png`  
**Look for:** Rules verification screen with conduct requirements

### Step 6: Review Exam Overview
**From:** Screenshot #6  
**Rename to:** `06-exam-overview.png`  
**Look for:** 20 questions, 330 XP, question type distribution

### Step 7: Take the Exam
**From:** Screenshot #7  
**Rename to:** `07-exam-in-progress.png`  
**Look for:** Active exam interface with question, timer, warning modal showing "3/7"

### Step 8: View Your Certificate
**From:** Screenshot #1  
**Rename to:** `08-your-certificates.png`  
**Look for:** Multiple certificate cards with scores and status

### Step 9: Download or Share Your Certificate
**From:** Screenshot #8  
**Rename to:** `09-certificate-verified.png`  
**Look for:** Full certificate display with "VERIFIED & APPROVED" banner

### Not Used
**Screenshot #4** - Popcorn application form  
❌ Excluded from exam-taking guide

---

## Folder Structure After Renaming

Once you've renamed all 9 screenshots, your folder should look like:

```
c:/xampp/htdocs/language-platform/exam-guide-assets/
│
├── 01-homepage.png
├── 02-login.png
├── 03-language-dashboard.png
├── 04-choose-exam-type.png
├── 05-before-begin.png
├── 06-exam-overview.png
├── 07-exam-in-progress.png
├── 08-your-certificates.png
├── 09-certificate-verified.png
│
├── IMAGE-MAPPING.md (detailed guide)
├── IMAGE-SETUP-INSTRUCTIONS.txt
├── README.md
└── QUICK-REFERENCE.md (this file)
```

---

## Visual Flow Diagram

```
Homepage (01)
    ↓
Login (02)
    ↓
Dashboard (03)
    ↓
Choose Exam Type (04)
    ↓
Review Rules (05)
    ↓
Exam Overview (06)
    ↓
Take Exam (07)
    ↓
View Certificate (08)
    ↓
Download/Share (09)
```

---

## Quality Checklist

Before saving, verify each screenshot:

-   Shows the correct step in the exam flow
-   Text is readable (not too small or blurry)
-   Full relevant section is visible
-   No personal information (emails, names) exposed
-   Good contrast and lighting
-   Consistent with other screenshots (similar zoom level)
-   PNG format with reasonable file size (< 300KB)

---

## Next Steps

1. **Rename**: Use this table to rename each screenshot correctly
2. **Organize**: Save all 9 files to exam-guide-assets/ folder
3. **Verify**: Check that all 9 files are present
4. **Update HTML**: Replace placeholder divs with img tags
5. **Test**: Open guide in browser to confirm images load
6. **Export**: Print to PDF for distribution

---

## Need Help?

- See `IMAGE-MAPPING.md` for detailed description of each image
- Check `README.md` for setup instructions
- Review `exam-guide-with-images.html` for the HTML structure
- Each section has a placeholder showing exactly what filename to use

**Important:** Screenshot numbering in this guide follows your description order, not the actual file order. The key is to match the content to the step, not the screenshot number.
