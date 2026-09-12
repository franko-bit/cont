# Language Pair Translation System

## Quick Start

This repository now includes automated translation tools to manage multi-language lesson content.

### What This Does

1. **Scans all language pair directories** (CS-TO-RW, ES-TO-RW, DE-TO-RW, etc.)
2. **Extracts English words** from lesson question/answer/option fields
3. **Translates to target language** automatically
4. **Preserves all Kinyarwanda text** exactly as-is
5. **Updates files** in the repository

### Directory Structure

```
content/
├── CS-TO-RW/          # Czech → Kinyarwanda
│   ├── level1/
│   │   ├── animals.yaml
│   │   ├── colors.yaml
│   │   └── ...
│   ├── level2/
│   └── ...
├── ES-TO-RW/          # Spanish → Kinyarwanda
├── RW-TO-ES/          # Kinyarwanda → Spanish
├── DE-TO-RW/          # German → Kinyarwanda
└── ... (50+ language pairs)
```

## Files in This System

### Scripts

#### `scripts/translate_language_pairs.py` (Main Script)
Automatically translates English content in all lesson files.

**Features:**
- Translates English to target language
- Preserves Kinyarwanda content
- Caches translations for efficiency
- Supports Google Translate API
- Handles 25+ languages

**Usage:**
```bash
# Process all language pairs
python3 scripts/translate_language_pairs.py --api-key YOUR_KEY --all

# Process specific pair
python3 scripts/translate_language_pairs.py --api-key YOUR_KEY --pair ES-TO-RW

# Process multiple pairs
python3 scripts/translate_language_pairs.py --api-key YOUR_KEY --pairs ES-TO-RW FR-TO-RW DE-TO-RW
```

**Output:**
- Prints processing status for each file
- Shows statistics (files processed, translations made, etc.)
- Saves translation cache to `translation_cache.json`
- Updates lesson files with translations

#### `scripts/verify_translations.py` (QA Script)
Verifies that translations were done correctly.

**Features:**
- Checks that Kinyarwanda content is preserved
- Detects potential missed English text
- Generates verification report
- Handles 25+ languages

**Usage:**
```bash
# Verify all pairs
python3 scripts/verify_translations.py --all

# Verify specific pair
python3 scripts/verify_translations.py --pair ES-TO-RW

# Verify with custom content directory
python3 scripts/verify_translations.py --all --content-dir ./content
```

**Output:**
- Statistics on files/exercises checked
- Any issues found
- Any warnings about potential English text
- Summary report

### Documentation

#### `scripts/translation_guide.md`
Detailed guide on the translation system:
- How it works
- What gets translated
- What doesn't get translated
- Language codes
- Examples
- Troubleshooting

## Supported Language Pairs

The system handles translation pairs in both directions:

- **XX-TO-RW**: Various language → Kinyarwanda
  - CS-TO-RW, DA-TO-RW, DE-TO-RW, EL-TO-RW, ES-TO-RW, FI-TO-RW, etc.
  
- **RW-TO-XX**: Kinyarwanda → Various language
  - RW-TO-AR, RW-TO-CS, RW-TO-DA, RW-TO-DE, RW-TO-ES, RW-TO-FI, etc.

**25+ supported languages:**
Arabic, Czech, Danish, German, Greek, English, Spanish, Finnish, French, Irish, Scottish Gaelic, Hebrew, Hindi, Indonesian, Italian, Japanese, Korean, Latin, Dutch, Norwegian, Polish, Portuguese, Russian, Swedish, Swahili, Turkish, Ukrainian, Vietnamese, Chinese, Zulu

## How It Works

### 1. Discovery
- Scans `content/` directory for `*-TO-*` directories
- Identifies target language from directory name

### 2. Processing
For each lesson file:
- Loads YAML content
- Iterates through exercises
- For each field:
  - Detects if it's Kinyarwanda (preserves it)
  - Detects if it's English (translates it)
  - Leaves other content unchanged
- Saves updated YAML file

### 3. Translation
- Uses Google Translate API (if key provided)
- Caches translations to avoid redundant API calls
- Preserves exact wording for Kinyarwanda

### 4. Verification
- Optional: Run verification script
- Checks for missed English content
- Confirms Kinyarwanda preservation
- Generates quality report

## Example Transformation

### Input (ES-TO-RW/level1/greetings.yaml)
```yaml
lessons:
  - id: 1
    type: translation
    question: "How do you say 'Hello' in Kinyarwanda?"
    answer: "Muraho"
    translation: "Hello"
```

### Output (After Translation)
```yaml
lessons:
  - id: 1
    type: translation
    question: "¿Cómo dices 'Hola' en kinyarwanda?"
    answer: "Muraho"  # PRESERVED - Kinyarwanda
    translation: "Hola"  # TRANSLATED - Spanish
```

## Getting Started

### Prerequisites
- Python 3.7+
- Required packages: `pyyaml`, `requests`

### Installation
```bash
pip install pyyaml requests
```

### Get Google Translate API Key
1. Go to [Google Cloud Console](https://console.cloud.google.com)
2. Create new project or select existing
3. Enable "Google Translate API"
4. Create API key (credentials → API keys)
5. Copy the key

### Run Translations
```bash
python3 scripts/translate_language_pairs.py --api-key YOUR_KEY --all
```

### Verify Results
```bash
python3 scripts/verify_translations.py --all
```

## Key Principles

1. **Always preserve Kinyarwanda**: No Kinyarwanda text is modified
2. **Translate English**: All English content → target language
3. **Maintain structure**: File formats and YAML structure unchanged
4. **Cache efficiently**: Avoid redundant translations
5. **Verify quality**: Always run verification after translation

## Troubleshooting

### API Key Issues
- Verify key is valid and active
- Check quotas in Google Cloud Console
- Ensure Translation API is enabled

### Missing Translations
- Check if text contains Kinyarwanda indicators
- Verify field is in the translation list
- Check cache for translation errors

### Encoding Issues
- Files use UTF-8 encoding
- Ensure terminal supports UTF-8
- Check file encodings if special characters garbled

### Script Errors
- Check Python version (3.7+)
- Verify all dependencies installed
- Check file paths are correct

## Statistics

After running, the script outputs:
```
📊 TRANSLATION STATISTICS
============================================================
Files processed:        45
Files skipped:          5
Translations made:      1,200
Kinyarwanda preserved:  3,500
Errors:                 0
============================================================
```

## Contributing

To add new language pairs:

1. Create directories:
   ```bash
   mkdir -p content/XX-TO-RW/level{1,2,3,4,5,6}
   ```

2. Add lesson files following the existing YAML structure

3. Run translation script:
   ```bash
   python3 scripts/translate_language_pairs.py --api-key YOUR_KEY --pair XX-TO-RW
   ```

## References

- [Google Translate API](https://cloud.google.com/translate/docs)
- [YAML Specification](https://yaml.org/)
- [ISO 639-1 Language Codes](https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes)

## Support

For issues or questions:
1. Check the [translation guide](scripts/translation_guide.md)
2. Review verification report from `verify_translations.py`
3. Check translation cache in `translation_cache.json`
4. Open an issue on GitHub

## License

Same as main project
