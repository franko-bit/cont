# Language Pair Translation Guide

## Overview

This guide explains how the language pair translation system works and what gets translated in each lesson file.

## Principles

1. **Preserve Kinyarwanda**: All Kinyarwanda content is kept exactly as-is
2. **Translate English**: All English content is translated to the target language
3. **Maintain Structure**: YAML structure and file format is preserved
4. **Cache Translations**: Translations are cached to avoid redundant API calls

## Language Pair Format

- **Source-TO-Target**: e.g., `ES-TO-RW` means Spanish → Kinyarwanda
- **Target Language** is the last part: `RW-TO-ES` → target is `ES` (Spanish)

## Fields That Get Translated

### In `lesson` section:
- `description`: Lesson description in English → target language

### In each `exercise`:
- `question`: The question or prompt in English
- `statement`: Statement for true/false exercises
- `context_sentence`: Context for meaning identification
- `text`: Reading comprehension text
- `story_intro`: Introduction for story exercises
- `translation`: English translation/hint (NOT Kinyarwanda answers)
- `options`: Choice options (excluding Kinyarwanda words)
- `word_bank`: Word bank items (excluding Kinyarwanda words)
- `prompt`: Text to pronounce or repeat
- Various other text fields

## Fields That Are NOT Translated

These fields are skipped:
- `id`: Exercise ID number
- `type`: Exercise type (translation, multiple_choice, etc.)
- `xp_reward`: Experience points
- `tts_text`: Text-to-speech input (Kinyarwanda)
- `answer`: Kinyarwanda answer
- `correct_answer`: Kinyarwanda correct answer
- `correct_response`: Kinyarwanda response
- `name`: Lesson name

## Kinyarwanda Detection

The system automatically detects Kinyarwanda content by looking for common words:
- Greetings: Muraho, Mwaramutse, Mwiriwe
- Family: Mama, Data, mwana
- Common phrases: Amakuru, Ni meza, Nishimiye kukubona
- And 20+ other indicators

Any field containing these words is NOT translated.

## Example Transformations

### ES-TO-RW (Spanish → Kinyarwanda)

**Before:**
```yaml
- id: 1
  type: translation
  question: "How do you say 'Hello' in Kinyarwanda?"
  answer: "Muraho"
  translation: "Hello"
```

**After:**
```yaml
- id: 1
  type: translation
  question: "¿Cómo dices 'Hola' en kinyarwanda?"
  answer: "Muraho"  # UNCHANGED - Kinyarwanda preserved
  translation: "Hola"
```

### RW-TO-ES (Kinyarwanda → Spanish)

**Before:**
```yaml
- id: 1
  type: multiple_choice
  question: "What does 'Muraho' mean?"
  options:
    - "Goodbye"
    - "Hello"
    - "Good morning"
  answer: "Hello"
```

**After:**
```yaml
- id: 1
  type: multiple_choice
  question: "¿Qué significa 'Muraho'?"
  options:
    - "Adiós"
    - "Hola"
    - "Buenos días"
  answer: "Hola"
```

## Supported Languages

| Code | Language | ISO 639-1 |
|------|----------|----------|
| AR | Arabic | ar |
| CS | Czech | cs |
| DA | Danish | da |
| DE | German | de |
| EL | Greek | el |
| EN | English | en |
| ES | Spanish | es |
| FI | Finnish | fi |
| FR | French | fr |
| GA | Irish | ga |
| GD | Scottish Gaelic | gd |
| HE | Hebrew | he |
| HI | Hindi | hi |
| ID | Indonesian | id |
| IT | Italian | it |
| JA | Japanese | ja |
| KO | Korean | ko |
| LA | Latin | la |
| NL | Dutch | nl |
| NO | Norwegian | no |
| PL | Polish | pl |
| PT | Portuguese | pt |
| RU | Russian | ru |
| RW | Kinyarwanda | rw |
| SV | Swedish | sv |
| SW | Swahili | sw |
| TR | Turkish | tr |
| UK | Ukrainian | uk |
| VI | Vietnamese | vi |
| ZH | Chinese | zh |
| ZU | Zulu | zu |

## Usage

### Process All Language Pairs
```bash
python3 scripts/translate_language_pairs.py --api-key YOUR_KEY --all
```

### Process Specific Pair
```bash
python3 scripts/translate_language_pairs.py --api-key YOUR_KEY --pair ES-TO-RW
```

### Process Multiple Pairs
```bash
python3 scripts/translate_language_pairs.py --api-key YOUR_KEY --pairs ES-TO-RW FR-TO-RW DE-TO-RW
```

### Custom Content Directory
```bash
python3 scripts/translate_language_pairs.py --api-key YOUR_KEY --content-dir /path/to/content --all
```

## Translation Cache

Translations are automatically cached in `translation_cache.json` to:
- Avoid redundant API calls
- Speed up processing
- Reduce API costs

The cache is loaded on startup and saved after processing.

## Statistics

After processing, the script outputs:
- Files processed: Number of files with translations made
- Files skipped: Files that didn't need updates
- Translations made: Total number of individual translations
- Kinyarwanda preserved: Number of Kinyarwanda texts found and preserved
- Errors: Number of processing errors

## Quality Assurance

To verify translations:

1. Check a few translated files manually
2. Search for English words that might have been missed
3. Verify Kinyarwanda content is unchanged
4. Review translation accuracy for domain-specific terms

## Troubleshooting

### API Key Issues
- Ensure API key is valid and has translation permissions
- Check quota limits on Google Cloud Console

### Missing Translations
- Some text may be technical and intentionally skipped
- Check if text contains Kinyarwanda indicators
- Verify field is in the translation list

### Encoding Issues
- Files use UTF-8 encoding
- Ensure terminal supports UTF-8
- Check file encodings if special characters appear garbled

## Contributing

To add new language pairs:
1. Create directories under `content/` following format: `XX-TO-YY`
2. Add level subdirectories: `level1/`, `level2/`, etc.
3. Add lesson YAML files following the existing structure
4. Run the translation script

## References

- [Google Translate API Docs](https://cloud.google.com/translate/docs)
- [YAML Format Guide](https://yaml.org/)
- [ISO 639-1 Language Codes](https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes)
