# Language Loading & Parameter Passing - Fix Summary

## Issues Identified & Fixed

### Issue 1: Game.php Loading from Wrong Folder
**Problem:** When accessing a lesson through the dashboard with a non-English-Kinyarwanda pair (e.g., `pair=fr-rw&direction=rw`), the game page tried to load from `SW-TO-RW` instead of the correct folder (`RW-TO-FR`).

**Root Cause:** `lesson.php` was linking to `game.php` with only the legacy `?lang=` parameter, which game.php would then resolve using the old direction-only fallback mapping. This caused the system to lose the pair information.

**Fix Applied:** Updated [lesson.php](frontend/lesson.php#L2013) to pass `pair` and `direction` parameters to game.php instead of just `lang`:
```php
// BEFORE:
<a href="game.php?lang=<?= $lang ?>&level=<?= $level ?>&topic=<?= urlencode($topic) ?>">

// AFTER:
<a href="game.php?pair=<?= htmlspecialchars($pair) ?>&direction=<?= htmlspecialchars($direction) ?>&level=<?= $level ?>&topic=<?= urlencode($topic) ?>">
```

### Issue 2: Game Close/Back Buttons Using Legacy Parameters
**Problem:** The game.php close button and finish screen back button were using the old `?lang=` parameter when returning to `choose-topic.php`.

**Fix Applied:** Updated [game.php](frontend/game.php#L287) and [game.php](frontend/game.php#L304) to use pair and direction:
```php
// Updated close button and back link to use:
?pair=<?= htmlspecialchars($pair) ?>&direction=<?= htmlspecialchars($direction) ?>&level=<?= $level ?>
```

### Issue 3: Bidirectional YAML Parsing
**Problem:** Game.php was only extracting the **back** words from flashcards, regardless of the learning direction.

**Fix Applied:** Updated all three YAML parsing methods in [game.php](frontend/game.php#L85-L180) to intelligently choose between front/back words:
- If `direction='en'` or `direction='fr'` → Extract **front** words (learning foreign language)
- If `direction='rw'` or `direction='sw'` → Extract **back** words (learning native language)

---

## Language Pair System Architecture

### Pair + Direction Mapping
Each language pair has TWO directions, creating 8 unique learning modes:

| Pair | Direction | Target Folder | Meaning |
|------|-----------|---------------|---------|
| en-rw | en | EN-TO-RW | Learn Kinyarwanda FROM English |
| en-rw | rw | RW-TO-EN | Learn English FROM Kinyarwanda |
| fr-rw | fr | FR-TO-RW | Learn Kinyarwanda FROM French |
| fr-rw | rw | RW-TO-FR | Learn French FROM Kinyarwanda |
| en-sw | en | EN-TO-SW | Learn Kiswahili FROM English |
| en-sw | sw | SW-TO-EN | Learn English FROM Kiswahili |
| fr-sw | fr | FR-TO-SW | Learn Kiswahili FROM French |
| fr-sw | sw | SW-TO-FR | Learn French FROM Kiswahili |

### Parameter Flow (CORRECT)
```
Dashboard (?pair=fr-rw&direction=rw)
    ↓
Lesson.php (reads pair, direction)
    ↓ [Fixed: now passes pair & direction, not just lang]
Game.php (looks up pair:direction in map → RW-TO-FR)
    ↓
Loads: ../content/RW-TO-FR/level1/animals.yaml
    ↓
Extracts BACK words (Kinyarwanda) for the voice game
```

---

## What Happens When URLs Are Accessed

### Scenario 1: `?pair=fr-rw&direction=rw&ui_lang=en`
- **Dashboard:** Loads lessons from `RW-TO-FR` folder
- **Content:** Lessons teach French (back words from Kinyarwanda flashcards)
- **UI Language:** English (menus, buttons, instructions in English)
- **Game:** Will correctly load words from RW-TO-FR folder

### Scenario 2: `?pair=fr-sw&direction=fr&ui_lang=en`
- **Dashboard:** Loads lessons from `FR-TO-SW` folder  
- **Content:** Lessons teach Kiswahili (back words from French flashcards)
- **UI Language:** English
- **Game:** Will correctly load words from FR-TO-SW folder

---

## Testing Your Fix

Access this debug page to verify all mappings are working:
```
https://www.playmates.games/frontend/debug-pair-direction.php
```

Or test directly by accessing a French lesson:
```
https://www.playmates.games/frontend/dashboard.php?pair=fr-rw&direction=rw&level=1&ui_lang=en#lessons
```

Then click on any lesson and verify:
1. Lesson loads the French content (RW-TO-FR folder)
2. Clicking "Play Voice Game" navigates to game.php with pair=fr-rw&direction=rw
3. Game loads vocabulary from the correct folder
4. Words match the expected language (French words when learning French)

---

## Remaining Notes

**UI Language vs. Lesson Content Language:**
- `ui_lang=en` sets menus/buttons/instructions to English
- `pair=fr-rw&direction=rw` sets lesson content to French
- These are independent - you can learn French with an English UI

**Session Storage:**
- `$_SESSION['active_pair']` persists the pair selection
- `$_SESSION['active_direction']` persists the direction selection
- These are updated whenever navigating between pages with URL parameters
