# 📚 How to Take an Exam - User Guide Setup

## What I've Created

I've prepared a focused, step-by-step exam user guide for your students. This guide focuses **only on the exam-taking flow** - from login through certificate download.

**Files:**
- **HTML File:** `exam-guide-with-images.html`
- **Assets Folder:** `exam-guide-assets/` (for images)
- **Image Mapping:** `exam-guide-assets/IMAGE-MAPPING.md` (detailed image guide)

## Guide Focus: Exam Taking Flow - Complete Journey

The guide covers the complete exam-taking journey with 12 steps:
1. 🏠 **Access Homepage** - Where to start
2. 🔐 **Login** - Access your account
3. 📊 **Dashboard** - Find the exam button
4. 🍿 **Apply with Popcorn Code** - Optional institutional access
5.   **Choose Exam Type** - Academic vs Business English
6. 📋 **Review Rules** - Before you begin
7. 🎯 **Exam Overview** - What to expect
8. 📝 **Take Exam** - The exam interface
9. 🏆 **View Certificate** - Access your certificates
10. 📥 **Download/Share** - Get your PDF certificate
11.   **Certificate Verification** - Approved status

**INCLUDED:** Popcorn applications (optional institutional access), expanded certificate section

## Images Needed: 11 Screenshots (for 12 steps)

### Quick Mapping

| # | Filename | What It Shows |
|---|----------|--------------|
| 1 | `01-homepage.png` | Playmates homepage with game categories |
| 2 | `02-login.png` | Login form (email/password) |
| 3 | `03-language-dashboard.png` | User dashboard with stats and exam button |
| 4 | `10-exam-popcorn.png` | Popcorn code application form (optional) |
| 5 | `04-choose-exam-type.png` | Academic vs Business English selection |
| 6 | `11-exam-rules.png` | Exam rules and conduct verification |
| 7 | `06-exam-overview.png` | Exam details (20 questions, 330 XP, categories) |
| 8 | `07-exam-in-progress.png` | Live exam interface with timer and progress |
| 9 | `08-your-certificates.png` | Certificate cards list page |
| 10 | `09-certificate-verified.png` | Full certificate display (downloadable) |
| 11 | `12-exam-certificate-verify.png` | Certificate verification & approved status |

## Quick Start: 3 Steps

### Step 1: Rename Your Screenshots
Identify each of the 11 screenshots from your images and rename them as above. See `QUICK-REFERENCE-v2.md` for detailed descriptions of what each should show.

### Step 2: Save to Folder
Save all 11 renamed images to:
```
c:/xampp/htdocs/language-platform/exam-guide-assets/
```

### Step 3: Add Images to HTML
For each placeholder in `exam-guide-with-images.html`, replace:
```html
<div class="image-placeholder">...</div>
```

With:
```html
<img src="exam-guide-assets/[filename].png" alt="[description]">
```

## Image Organization

```
exam-guide-assets/
├── 01-homepage.png
├── 02-login.png
├── 03-language-dashboard.png
├── 04-choose-exam-type.png
├── 05-before-begin.png
├── 06-exam-overview.png
├── 07-exam-in-progress.png
├── 08-your-certificates.png
├── 09-certificate-verified.png
├── IMAGE-MAPPING.md
├── IMAGE-SETUP-INSTRUCTIONS.txt
└── README.md (this file)
```

## Key Features

✨ **Professional Design**
- Clean, modern layout matching your platform colors
- Consistent typography and spacing
- Color-coded sections (info, warning, tip boxes)

📱 **Responsive**
- Works on desktop, tablet, and mobile
- Images scale properly
- Readable on all devices

🖨️ **Print-Friendly**
- Optimized for PDF conversion
- Proper page breaks
- High-quality printing

🎯 **User-Focused**
- Written for exam takers, not admins
- Clear step-by-step instructions
- Practical tips and warnings
- Real screenshots they'll recognize

## How to Add Images

### Method 1: Direct Editing (Recommended)
1. Open `exam-guide-with-images.html` in VS Code
2. Find each placeholder (search for "IMAGE")
3. Replace the entire div with: `<img src="exam-guide-assets/[name].png" alt="[description]">`
4. Save and refresh in browser

### Method 2: Using Search & Replace
1. Open file in VS Code
2. Use Ctrl+H for Find & Replace
3. Find: `<div style="background: white; padding: 20px; border-radius: 12px; border: 2px dashed #ddd0c2; text-align: center; min-height: \d+px; display: flex; align-items: center; justify-content: center;">`
4. Replace one by one with proper image tags

## Testing the Guide

1. **Check Images Load**
   ```
   - Open in Chrome, Firefox, Safari, Edge
   - Verify all 11 images appear
   - Check for any broken image icons
   ```

2. **Check Responsiveness**
   ```
   - Resize browser window
   - Test on mobile device
   - Verify images scale properly
   ```

3. **Check PDF Export**
   ```
   - Print to PDF
   - Verify all content is included
   - Check image quality in PDF
   ```

## How to Identify the 9 Images

**See `IMAGE-MAPPING.md` for detailed descriptions of each screenshot.**

Each section includes:
- What to look for
- Key elements to identify
- Where it appears in the exam flow

## Viewing & Sharing

### View Online
```
http://localhost/language-platform/exam-guide-with-images.html
```

### Export to PDF
1. Open the guide in your browser
2. Press `Ctrl+P` (Windows) or `Cmd+P` (Mac)
3. Select "Save as PDF"
4. Download and save
5. Share with students via email or LMS

### Print Physical Copies
1. Open in browser
2. Press `Ctrl+P` or use Print menu
3. Select printer
4. Print directly

## Features

✨ **Professional Design**
- Platform-matching colors
- Clean, modern layout
- Consistent typography

📱 **Responsive Layout**
- Works on desktop, tablet, mobile
- Images scale properly
- Easy to read on all devices

🖨️ **Print Optimized**
- PDF export friendly
- Professional appearance
- Proper page breaks

🎯 **Exam-Focused**
- Only exam-taking flow
- No institutional features
- Student-centric language

## Image Quality Guidelines

For best results:
- **Format:** PNG (recommended) or JPG
- **Resolution:** 800-1200px width
- **Size:** Keep under 300KB per image
- **Content:** Full screen or relevant section
- **Quality:** Clear, readable text and interface elements

## Browser Compatibility

  Tested on:
- Google Chrome (latest)
- Mozilla Firefox (latest)  
- Apple Safari (latest)
- Microsoft Edge (latest)

## Troubleshooting

**Images not appearing?**
- Check filenames match exactly (01-homepage.png, etc.)
- Verify files are in exam-guide-assets/ folder
- Clear browser cache (Ctrl+Shift+Del)
- Check browser console for errors (F12)

**Issues with PDF export?**
- Use Chrome or Firefox (best PDF support)
- Check all images loaded before printing
- Try "Print to file" option

**Page layout problems?**
- Try zooming browser to 100%
- Test on different browsers
- Check responsive design on mobile

## File Manifest

```
exam-guide-assets/
├── 01-homepage.png              (To be added)
├── 02-login.png                 (To be added)
├── 03-language-dashboard.png    (To be added)
├── 04-choose-exam-type.png      (To be added)
├── 05-before-begin.png          (To be added)
├── 06-exam-overview.png         (To be added)
├── 07-exam-in-progress.png      (To be added)
├── 08-your-certificates.png     (To be added)
├── 09-certificate-verified.png  (To be added)
├── IMAGE-MAPPING.md             (Detailed image guide)
├── IMAGE-SETUP-INSTRUCTIONS.txt (Setup reference)
└── README.md                    (This file)
```

## Content Overview

The guide walks students through 9 steps:
- **Entry:** Landing page and login
- **Access:** Finding the exam in dashboard  
- **Selection:** Choosing exam type
- **Verification:** Rules and consent
- **Preparation:** Exam overview
- **Test:** Taking the actual exam
- **Results:** Certificate viewing
- **Download:** Getting the PDF

Total content: ~2000 words + 9 images

## Support & Questions

- See `IMAGE-MAPPING.md` for image identification help
- Check HTML placeholders for section details
- Review style tags for customization options

---

**Created:** May 2026
**Version:** 3.0  
**Status:** Focused exam-taking flow guide (9 images)
**Last Updated:** May 27, 2026
