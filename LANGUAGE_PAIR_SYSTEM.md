# Language Pair Configuration System

## Overview

The system manages language pairs through a combination of **database records**, **hardcoded dropdown options**, and **direction mapping logic**. There is **no centralized configuration file**—language pairs are scattered across multiple PHP files and the database schema.

Currently supported language pairs:
- **en-rw**: English ↔ Kinyarwanda
- **fr-rw**: French ↔ Kinyarwanda
- **rw-en**: Kinyarwanda → English (reverse)
- **rw-fr**: Kinyarwanda → French (reverse)

---

## 1. Where Language Pairs Are Registered

### A. Database Schema
**File**: [`database/exam-schema.sql`](database/exam-schema.sql)

The `exams` table has a `language_pair` column:
```sql
CREATE TABLE IF NOT EXISTS exams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    language_pair VARCHAR(20) NOT NULL, -- 'en-rw', 'fr-rw', 'rw-en', 'rw-fr'
    level_number INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    ...
);
```

**Key Points:**
- Language pair is stored as a string code (e.g., 'en-rw')
- Each exam belongs to ONE language pair and ONE level (1-6)
- No validation table—ANY string can theoretically be inserted
- Each certificate issued also records the `language_pair` it was earned in

### B. Admin UI Dropdowns
**File**: [`admin/manage-exams.php`](admin/manage-exams.php) — Lines 311-315

The Create Exam modal has hardcoded options:
```html
<select name="language_pair" class="form-select">
    <option value="en-rw">English → Kinyarwanda</option>
    <option value="rw-en">Kinyarwanda → English</option>
    <option value="fr-rw">French → Kinyarwanda</option>
    <option value="rw-fr">Kinyarwanda → French</option>
</select>
```

The same dropdown appears again for adding questions (Lines 374-378).

### C. Frontend User Options
**File**: [`frontend/dashboard.php`](frontend/dashboard.php) — Lines 255-280

The dashboard defines available language pairs in a PHP configuration:
```php
$lang_pairs = [
    'en-rw' => [
        'code' => 'en-rw',
        'name' => 'English ↔ Kinyarwanda',
        'languages' => ['en' => 'English', 'rw' => 'Kinyarwanda'],
        'directions' => [
            'en' => ['name' => 'Eng → Kiny', 'flag' => 'gb', 'folder' => 'EN-TO-RW'],
            'rw' => ['name' => 'Kiny → Eng', 'flag' => 'rw', 'folder' => 'RW-TO-EN']
        ]
    ],
    'fr-rw' => [
        'code' => 'fr-rw',
        'name' => 'French ↔ Kinyarwanda',
        'languages' => ['fr' => 'French', 'rw' => 'Kinyarwanda'],
        'directions' => [
            'fr' => ['name' => 'Fre → Kiny', 'flag' => 'fr', 'folder' => 'FR-TO-RW'],
            'rw' => ['name' => 'Kiny → Fre', 'flag' => 'rw', 'folder' => 'RW-TO-FR']
        ]
    ]
];
```

**Note**: This only exposes `en-rw` and `fr-rw` to users. The reverse pairs (`rw-en`, `rw-fr`) are NOT shown in the UI but exist in the database.

---

## 2. How Content Folders Are Loaded Based on Language Pair & Direction

The system maps language pairs → content folders through multiple direction mapping arrays.

### A. Primary Direction Map (Content Folder Selection)
**File**: [`backend/load-exercises.php`](backend/load-exercises.php) — Lines 222-225

```php
$directionMap = [
    'en-rw' => 'EN-TO-RW',
    'fr-rw' => 'FR-TO-RW',
    'rw-en' => 'RW-TO-EN',
    'rw-fr' => 'RW-TO-FR',
];

$folder = $directionMap[$direction]; // e.g., 'EN-TO-RW'
```

The folder is then used to load exercise files:
```php
$content_dir = "../content/{$folder}/level{$active_level}/";
// Example: ../content/EN-TO-RW/level1/
```

### B. Language Code Maps (Translation Naming)
Some files convert language pair + direction into a "content code" for internal use:

**File**: [`frontend/lessonexam.php`](frontend/lessonexam.php) — Lines 90-91

```php
$db_lang_map = [
    'en-rw:en' => 'en-to-rw',    // English-Kinyarwanda from English direction
    'en-rw:rw' => 'rw-to-en',    // English-Kinyarwanda from Kinyarwanda direction
    'fr-rw:fr' => 'fr-to-rw',    // French-Kinyarwanda from French direction
    'fr-rw:rw' => 'rw-to-fr',    // French-Kinyarwanda from Kinyarwanda direction
];
```

### C. Folder Language Codes
**File**: [`frontend/lessonexam.php`](frontend/lessonexam.php) — Lines 96-97

Another mapping for language code to folder:
```php
$lang_folder_map = [
    'en' => 'EN-TO-RW',
    'rw' => 'RW-TO-EN',
    'fr' => 'FR-TO-RW',
    'sw' => 'SW-TO-RW',    // Already prepared for Swahili!
    'es' => 'ES-TO-RW',    // Already prepared for Spanish!
    'de' => 'DE-TO-RW'     // Already prepared for German!
];
```

**Interesting Note**: The language folder map ALREADY includes `SW-TO-RW`, `ES-TO-RW`, and `DE-TO-RW`!

### D. Content Folder Location
**Path**: `/content/`

Current folders:
```
content/
├── EN-TO-RW/
│   └── level1/, level2/, ..., level6/ (each with .yaml files)
├── FR-TO-RW/
│   └── level1/, level2/, ..., level6/
├── RW-TO-EN/
│   └── level1/, level2/, ..., level6/
├── RW-TO-FR/
│   └── level1/, level2/, ..., level6/
└── EXAMS/
```

---

## 3. Database Records Defining Available Language Pairs

There is NO dedicated configuration table. Language pairs are implicitly defined by:

1. **Exam records in the `exams` table**
   - Query: `SELECT DISTINCT language_pair FROM exams`
   - This shows which language pairs have at least one exam

2. **Exam questions in the `exam_questions` table**
   - Stores `language_pair` for each question

3. **Certificates issued**
   - The `certificates` table records which language pair each cert was earned in

**Example Query to See All Language Pairs Used**:
```sql
SELECT DISTINCT language_pair FROM exams ORDER BY language_pair;
```

---

## 4. How to Add New Language Pairs (e.g., 'en-sw' and 'fr-sw')

To add Swahili pairs, follow these steps:

### Step 1: Create Content Folders
Create the necessary folder structure:
```
content/
├── EN-TO-SW/
│   ├── level1/
│   ├── level2/
│   ├── level3/
│   ├── level4/
│   ├── level5/
│   └── level6/
├── FR-TO-SW/
│   ├── level1/
│   ├── level2/
│   ├── level3/
│   ├── level4/
│   ├── level5/
│   └── level6/
├── SW-TO-EN/
│   ├── level1/
│   ├── level2/
│   ├── level3/
│   ├── level4/
│   ├── level5/
│   └── level6/
└── SW-TO-FR/
    ├── level1/
    ├── level2/
    ├── level3/
    ├── level4/
    ├── level5/
    └── level6/
```

Each level folder should contain `.yaml` files with lesson content.

### Step 2: Update Direction Mappings

#### File: `backend/load-exercises.php` (Lines 222-226)
```php
$directionMap = [
    'en-rw' => 'EN-TO-RW',
    'fr-rw' => 'FR-TO-RW',
    'rw-en' => 'RW-TO-EN',
    'rw-fr' => 'RW-TO-FR',
    'en-sw' => 'EN-TO-SW',  // ADD THIS
    'fr-sw' => 'FR-TO-SW',  // ADD THIS
    'sw-en' => 'SW-TO-EN',  // ADD THIS
    'sw-fr' => 'SW-TO-FR',  // ADD THIS
];
```

#### File: `frontend/lessonexam.php` (Lines 90-91)
```php
$db_lang_map = [
    'en-rw:en' => 'en-to-rw',
    'en-rw:rw' => 'rw-to-en',
    'fr-rw:fr' => 'fr-to-rw',
    'fr-rw:rw' => 'rw-to-fr',
    'en-sw:en' => 'en-to-sw',  // ADD THIS
    'en-sw:sw' => 'sw-to-en',  // ADD THIS
    'fr-sw:fr' => 'fr-to-sw',  // ADD THIS
    'fr-sw:sw' => 'sw-to-fr',  // ADD THIS
];
```

#### File: `backend/create-assessment.php` (Lines 62-69)
```php
$language_code_map = [
    'en-rw:en' => 'en-to-rw',
    'en-rw:rw' => 'rw-to-en',
    'fr-rw:fr' => 'fr-to-rw',
    'fr-rw:rw' => 'rw-to-fr',
    'rw-en:rw' => 'rw-to-en',
    'rw-en:en' => 'en-to-rw',
    'rw-fr:rw' => 'rw-to-fr',
    'rw-fr:fr' => 'fr-to-rw',
    'en-sw:en' => 'en-to-sw',  // ADD THESE 4
    'en-sw:sw' => 'sw-to-en',
    'fr-sw:fr' => 'fr-to-sw',
    'fr-sw:sw' => 'sw-to-fr',
];
```

#### File: `backend/exam-api.php` (Lines 270-272)
```php
$folder = ($active_pair == 'en-rw' || $active_pair == 'rw-en') ? 
    (strpos($active_pair, 'en') === 0 ? 'EN-TO-RW' : 'RW-TO-EN') :
    ($active_pair == 'en-sw' || $active_pair == 'sw-en') ?  // ADD THIS BLOCK
    (strpos($active_pair, 'en') === 0 ? 'EN-TO-SW' : 'SW-TO-EN') :
    (strpos($active_pair, 'fr') === 0 ? 'FR-TO-RW' : 'RW-TO-FR');  // KEEP EXISTING
```

**Better approach** - Replace lines 270-272 with:
```php
$folderMap = [
    'en-rw' => 'EN-TO-RW', 'rw-en' => 'RW-TO-EN',
    'en-sw' => 'EN-TO-SW', 'sw-en' => 'SW-TO-EN',
    'en-es' => 'EN-TO-ES', 'es-en' => 'ES-TO-EN',
    'fr-rw' => 'FR-TO-RW', 'rw-fr' => 'RW-TO-FR',
    'fr-sw' => 'FR-TO-SW', 'sw-fr' => 'SW-TO-FR',
    'fr-es' => 'FR-TO-ES', 'es-fr' => 'ES-TO-FR',
];
$folder = $folderMap[$active_pair] ?? 'EN-TO-RW';
```

### Step 3: Update Admin UI Dropdowns

#### File: `admin/manage-exams.php` (Lines 311-315 and 374-378)
Replace both dropdowns:
```html
<select name="language_pair" class="form-select">
    <option value="en-rw">English → Kinyarwanda</option>
    <option value="rw-en">Kinyarwanda → English</option>
    <option value="fr-rw">French → Kinyarwanda</option>
    <option value="rw-fr">Kinyarwanda → French</option>
    <option value="en-sw">English → Swahili</option>
    <option value="sw-en">Swahili → English</option>
    <option value="fr-sw">French → Swahili</option>
    <option value="sw-fr">Swahili → French</option>
</select>
```

### Step 4: Update Frontend Dashboard

#### File: `frontend/dashboard.php` (Lines 255-280)
Add to the `$lang_pairs` array:
```php
'en-sw' => [
    'code' => 'en-sw',
    'name' => 'English ↔ Swahili',
    'languages' => ['en' => 'English', 'sw' => 'Swahili'],
    'directions' => [
        'en' => ['name' => 'Eng → Sw', 'flag' => 'gb', 'folder' => 'EN-TO-SW'],
        'sw' => ['name' => 'Sw → Eng', 'flag' => 'tz', 'folder' => 'SW-TO-EN']
    ]
],
'fr-sw' => [
    'code' => 'fr-sw',
    'name' => 'French ↔ Swahili',
    'languages' => ['fr' => 'French', 'sw' => 'Swahili'],
    'directions' => [
        'fr' => ['name' => 'Fr → Sw', 'flag' => 'fr', 'folder' => 'FR-TO-SW'],
        'sw' => ['name' => 'Sw → Fr', 'flag' => 'tz', 'folder' => 'SW-TO-FR']
    ]
],
```

### Step 5: Create Initial Exam Records

Insert exam records for each new language pair:
```sql
INSERT INTO exams (language_pair, level_number, title, description, exam_language, duration_minutes, passing_score, total_questions)
VALUES 
('en-sw', 1, 'English-Swahili Level 1 Assessment', 'Beginner Swahili assessment', 'sw', 45, 60, 25),
('en-sw', 2, 'English-Swahili Level 2 Assessment', 'Elementary Swahili assessment', 'sw', 45, 60, 25),
-- ... repeat for levels 3-6
('sw-en', 1, 'Swahili-English Level 1 Assessment', 'Beginner English assessment for Swahili speakers', 'en', 45, 60, 25),
-- ... repeat for fr-sw and sw-fr
;
```

### Step 6: Add Exam Questions

Once exams are created, populate the `exam_questions` table for each exam. For now, you can use SQL to get the exam IDs:
```sql
SELECT id FROM exams WHERE language_pair = 'en-sw' ORDER BY level_number;
```

Then insert questions for each exam/level combination.

---

## Key Considerations

1. **No Centralized Configuration**
   - Language pairs are hardcoded in 5+ different files
   - A better approach would be to create a `config/language-pairs.php` file with a single source of truth

2. **Content Folders Are Checked**
   - The system scans the content folder to count available lessons
   - If you add a language pair but forget to create the folders, users will see "0 lessons"

3. **Database Constraints**
   - The `language_pair` field in `exams` table is VARCHAR(20) and can store any string
   - Consider adding a CHECK constraint or a reference table to enforce valid pairs

4. **Reverse Pairs**
   - `rw-en`, `rw-fr` are in the database but NOT exposed in the user-facing dashboard
   - They appear only in admin dropdowns and reverse logic

5. **Default Language Pair**
   - The system defaults to `'en-rw'` when no pair is specified
   - Appears in multiple files: lessonexam.php, exam-api.php, access-exam.php, etc.

---

## Recommendation: Create a Centralized Config

To make language pair management easier, create:

**File**: `backend/language-pairs-config.php`

```php
<?php
// Language pairs configuration - single source of truth

return [
    'pairs' => [
        'en-rw' => ['name' => 'English → Kinyarwanda', 'folders' => 'EN-TO-RW'],
        'rw-en' => ['name' => 'Kinyarwanda → English', 'folders' => 'RW-TO-EN'],
        'fr-rw' => ['name' => 'French → Kinyarwanda', 'folders' => 'FR-TO-RW'],
        'rw-fr' => ['name' => 'Kinyarwanda → French', 'folders' => 'RW-TO-FR'],
        'en-sw' => ['name' => 'English → Swahili', 'folders' => 'EN-TO-SW'],
        'sw-en' => ['name' => 'Swahili → English', 'folders' => 'SW-TO-EN'],
        'fr-sw' => ['name' => 'French → Swahili', 'folders' => 'FR-TO-SW'],
        'sw-fr' => ['name' => 'Swahili → French', 'folders' => 'SW-TO-FR'],
    ],
    'directions' => [
        'en-rw' => ['en' => 'EN-TO-RW', 'rw' => 'RW-TO-EN'],
        'fr-rw' => ['fr' => 'FR-TO-RW', 'rw' => 'RW-TO-FR'],
        'en-sw' => ['en' => 'EN-TO-SW', 'sw' => 'SW-TO-EN'],
        'fr-sw' => ['fr' => 'FR-TO-SW', 'sw' => 'SW-TO-FR'],
    ],
    'default' => 'en-rw'
];
```

Then replace hardcoded arrays with: `$langConfig = require '../backend/language-pairs-config.php';`
