# Quick Reference: Screenshot Identification & Renaming

## How to Rename Your 11 Screenshots to 12 Guide Images

Use this guide to identify which of your screenshots goes where.

---

## Your Original 11 Screenshots

Here's what you provided (numbered 1-11 based on description order):

1.   Your Certificates page (cert cards, scores, verified status)
2.   Dashboard view (profile, XP, accuracy, lessons)
3.   Choose your exam page (Academic vs Business, 40 questions, 60 min)
4.   **Apply for Popcorn form** (NOW INCLUDED - step 4)
5.   Before you begin page (rules & conduct, identity check)
6.   Academic English Final Exam overview card (20 questions, 330 XP)
7.   Exam in progress (word bank question, warning modal "3/7 tab switches")
8.   Certificate Verified page (VERIFIED & APPROVED banner, print button)
9.   Login page (email/password form)
10.   Homepage (4 course categories: Language, Development Game, etc.)
11.   Exam type selection (Academic vs Business with descriptions)

**Status:**
- All 11 screenshots are now used in the expanded 12-step guide
- Screenshots #3 and #11 show the same exam type selection (choose one)
- Screenshot #8 is used twice (steps 10 and 11) or provide alternate view

---

## Mapping: Which Screenshot → Which Filename

| Guide Step | New Filename | Your Screenshot | What It Shows |
|-----------|--------------|-----------------|---------------|
| Step 1 | `01-homepage.png` | **#10** | Homepage with 4 course categories |
| Step 2 | `02-login.png` | **#9** | Login form with email/password |
| Step 3 | `03-language-dashboard.png` | **#2** | Dashboard with profile, XP, accuracy |
| Step 4 | `10-exam-popcorn.png` | **#4** | Popcorn application form (optional) |
| Step 5 | `04-choose-exam-type.png` | **#3 or #11** | Choose Academic vs Business English |
| Step 6 | `11-exam-rules.png` | **#5** | Rules & conduct verification page |
| Step 7 | `06-exam-overview.png` | **#6** | Exam info (20 questions, 330 XP) |
| Step 8 | `07-exam-in-progress.png` | **#7** | Exam interface with timer & progress |
| Step 9 | `08-your-certificates.png` | **#1** | Certificate cards page |
| Step 10 | `09-certificate-verified.png` | **#8** | Full certificate with VERIFIED banner |
| Step 11 | `12-exam-certificate-verify.png` | **#8** | Certificate verification & approval |

---

## File Organization

Folder structure after renaming:

```
c:/xampp/htdocs/language-platform/exam-guide-assets/
│
├── 01-homepage.png
├── 02-login.png
├── 03-language-dashboard.png
├── 04-choose-exam-type.png
├── 06-exam-overview.png
├── 07-exam-in-progress.png
├── 08-your-certificates.png
├── 09-certificate-verified.png
├── 10-exam-popcorn.png
├── 11-exam-rules.png
├── 12-exam-certificate-verify.png
│
├── IMAGE-MAPPING.md
├── IMAGE-SETUP-INSTRUCTIONS.txt
├── README.md
└── QUICK-REFERENCE.md
```

---

## Visual Flow

```
01-Homepage → 02-Login → 03-Dashboard → 10-Popcorn (optional)
    ↓
04-Choose Exam Type → 11-Rules → 06-Overview → 07-Take Exam
    ↓
08-Certificates → 09-Download/Share → 12-Verification
```

---

## Filename Corrections

The original filenames you provided had typos:
- `10-exam-pocorn.png` → **`10-exam-popcorn.png`** (corrected spelling)
- `11-exam-ruls.png` → **`11-exam-rules.png`** (corrected spelling)
- `11-exam-certficate-verifypag.png` → **`12-exam-certificate-verify.png`** (corrected spelling and renumbered)

---

## Next Steps

1. Identify each screenshot from the table above
2. Rename with the correct filename (corrected spelling)
3. Save all 11 files to `exam-guide-assets/` folder
4. Replace image placeholders in the HTML with img tags
5. Test in browser at http://localhost/language-platform/exam-guide-with-images.html
6. Export to PDF for student distribution
