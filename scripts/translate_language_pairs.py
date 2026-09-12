#!/usr/bin/env python3
"""
Language Pair Translation Automation Script
Scans all language pair directories, extracts English content, and translates to target language.
Preserves all Kinyarwanda text exactly as-is.

Usage:
    python3 translate_language_pairs.py --api-key YOUR_KEY
    python3 translate_language_pairs.py --pair ES-TO-RW
    python3 translate_language_pairs.py --all
"""

import os
import sys
import json
import re
import yaml
import argparse
from pathlib import Path
from typing import Dict, List, Any, Tuple, Optional
import requests

# Language code mappings for ISO 639-1 and extended codes
LANGUAGE_CODES = {
    'AR': 'ar',  # Arabic
    'CS': 'cs',  # Czech
    'DA': 'da',  # Danish
    'DE': 'de',  # German
    'EL': 'el',  # Greek
    'EN': 'en',  # English
    'ES': 'es',  # Spanish
    'FI': 'fi',  # Finnish
    'FR': 'fr',  # French
    'GA': 'ga',  # Irish
    'GD': 'gd',  # Scottish Gaelic
    'HE': 'he',  # Hebrew
    'HI': 'hi',  # Hindi
    'ID': 'id',  # Indonesian
    'IT': 'it',  # Italian
    'JA': 'ja',  # Japanese
    'KO': 'ko',  # Korean
    'LA': 'la',  # Latin
    'NL': 'nl',  # Dutch
    'NO': 'no',  # Norwegian
    'PL': 'pl',  # Polish
    'PT': 'pt',  # Portuguese
    'RU': 'ru',  # Russian
    'RW': 'rw',  # Kinyarwanda
    'SV': 'sv',  # Swedish
    'SW': 'sw',  # Swahili
    'TR': 'tr',  # Turkish
    'UK': 'uk',  # Ukrainian
    'VI': 'vi',  # Vietnamese
    'ZH': 'zh',  # Chinese
    'ZU': 'zu',  # Zulu
}

# Kinyarwanda indicators - words that indicate Kinyarwanda content
KINYARWANDA_INDICATORS = {
    'Muraho', 'Mwaramutse', 'mwana', 'Data', 'Mama', 'Amakuru',
    'Mwiriwe', 'Ijoro', 'ryiza', 'Tuzasubira', 'Murabeho',
    'Murakaza', 'neza', 'murakoze', 'Ni meza', 'Nishimiye',
    'kukubona', 'Wirinde', 'Hashize', 'igihe', 'tutabonanye',
    'nshuti', 'yanjye', 'ishuri', 'umunsi', 'mwiza', 'kazi',
    'rugendo', 'rwawe', 'mu', 'ku', 'uyu', 'munsi', 'ejo'
}

class LanguagePairTranslator:
    """Translates English content in language pair lesson files."""
    
    def __init__(self, api_key: Optional[str] = None, use_cache: bool = True):
        """Initialize translator.
        
        Args:
            api_key: Google Translate API key
            use_cache: Whether to cache translations locally
        """
        self.api_key = api_key
        self.use_cache = use_cache
        self.cache = {}
        self.stats = {
            'files_processed': 0,
            'files_skipped': 0,
            'translations_made': 0,
            'kinyarwanda_preserved': 0,
            'errors': 0
        }
        self.load_cache()
    
    def load_cache(self):
        """Load translation cache from disk if available."""
        cache_file = Path('translation_cache.json')
        if cache_file.exists():
            try:
                with open(cache_file, 'r', encoding='utf-8') as f:
                    self.cache = json.load(f)
                print(f"✓ Loaded {len(self.cache)} cached translations")
            except Exception as e:
                print(f"Warning: Could not load cache: {e}")
    
    def save_cache(self):
        """Save translation cache to disk."""
        if self.use_cache:
            try:
                with open('translation_cache.json', 'w', encoding='utf-8') as f:
                    json.dump(self.cache, f, ensure_ascii=False, indent=2)
            except Exception as e:
                print(f"Warning: Could not save cache: {e}")
    
    def is_kinyarwanda(self, text: str) -> bool:
        """Check if text contains Kinyarwanda content."""
        if not isinstance(text, str):
            return False
        
        # Check for Kinyarwanda indicators
        for indicator in KINYARWANDA_INDICATORS:
            if indicator in text:
                return True
        
        return False
    
    def is_english(self, text: str) -> bool:
        """Check if text is English (not Kinyarwanda or other language)."""
        if not isinstance(text, str) or not text.strip():
            return False
        
        # Skip if it's Kinyarwanda
        if self.is_kinyarwanda(text):
            return False
        
        # Skip if it's mostly non-ASCII (likely other language already translated)
        non_ascii = sum(1 for c in text if ord(c) > 127)
        if len(text) > 0 and non_ascii / len(text) > 0.3:
            return False
        
        return True
    
    def translate_google(self, text: str, target_lang: str) -> str:
        """Translate using Google Translate API."""
        if not self.api_key:
            return text
        
        cache_key = f"{text}|{target_lang}"
        if cache_key in self.cache:
            return self.cache[cache_key]
        
        try:
            url = "https://translation.googleapis.com/language/translate/v2"
            params = {
                'key': self.api_key,
                'source_language': 'en',
                'target_language': target_lang,
                'q': text
            }
            response = requests.post(url, params=params)
            
            if response.status_code == 200:
                result = response.json()['data']['translations'][0]['translatedText']
                self.cache[cache_key] = result
                return result
            else:
                print(f"API Error: {response.status_code} - {response.text}")
                return text
        except Exception as e:
            print(f"Translation error: {e}")
            return text
    
    def translate_text(self, text: str, target_lang_code: str) -> str:
        """Translate text to target language."""
        # Don't translate if already Kinyarwanda
        if self.is_kinyarwanda(text):
            self.stats['kinyarwanda_preserved'] += 1
            return text
        
        # Don't translate if not English
        if not self.is_english(text):
            return text
        
        # Skip translation if target is English
        if target_lang_code == 'en':
            return text
        
        # Translate
        translated = self.translate_google(text, target_lang_code)
        if translated != text:
            self.stats['translations_made'] += 1
        
        return translated
    
    def should_translate_key(self, key: str) -> bool:
        """Check if a key's value should be translated."""
        skip_keys = {
            'id', 'type', 'xp_reward', 'tts_text', 'name',
            'missing_word_position', 'correct_answer',
            'answer',  # This contains Kinyarwanda
        }
        return key not in skip_keys
    
    def process_value(self, value: Any, target_lang_code: str) -> Any:
        """Process a value and translate if needed."""
        if isinstance(value, str):
            return self.translate_text(value, target_lang_code)
        
        elif isinstance(value, list):
            return [self.process_value(item, target_lang_code) for item in value]
        
        elif isinstance(value, dict):
            return {k: self.process_exercise(v, target_lang_code, k) 
                    for k, v in value.items()}
        
        return value
    
    def process_exercise(self, exercise: Any, target_lang_code: str, key: Optional[str] = None) -> Any:
        """Process a single exercise and translate English content."""
        if not isinstance(exercise, dict):
            return exercise
        
        if key and not self.should_translate_key(key):
            return exercise
        
        translated_exercise = {}
        
        for k, v in exercise.items():
            if not self.should_translate_key(k):
                translated_exercise[k] = v
                continue
            
            if isinstance(v, str):
                translated_exercise[k] = self.translate_text(v, target_lang_code)
            
            elif isinstance(v, list):
                # Handle lists of strings (options, word_bank, etc.)
                if v and isinstance(v[0], str):
                    translated_exercise[k] = [
                        self.translate_text(item, target_lang_code) for item in v
                    ]
                else:
                    # Handle lists of objects
                    translated_exercise[k] = [
                        self.process_exercise(item, target_lang_code) if isinstance(item, dict) else item
                        for item in v
                    ]
            
            elif isinstance(v, dict):
                translated_exercise[k] = self.process_exercise(v, target_lang_code)
            
            else:
                translated_exercise[k] = v
        
        return translated_exercise
    
    def process_lesson_file(self, file_path: Path, target_lang_code: str) -> bool:
        """Process a lesson file and translate English content."""
        try:
            with open(file_path, 'r', encoding='utf-8') as f:
                lesson = yaml.safe_load(f)
            
            if not lesson or not isinstance(lesson, dict):
                return False
            
            original_content = json.dumps(lesson, ensure_ascii=False)
            
            # Translate lesson metadata
            if 'lesson' in lesson and isinstance(lesson['lesson'], dict):
                lesson['lesson'] = self.process_exercise(lesson['lesson'], target_lang_code)
            
            # Translate exercises
            if 'exercises' in lesson and isinstance(lesson['exercises'], list):
                lesson['exercises'] = [
                    self.process_exercise(ex, target_lang_code) 
                    for ex in lesson['exercises']
                ]
            
            # Check if content changed
            new_content = json.dumps(lesson, ensure_ascii=False)
            if original_content == new_content:
                self.stats['files_skipped'] += 1
                return False
            
            # Write back with nice formatting
            with open(file_path, 'w', encoding='utf-8') as f:
                yaml.dump(
                    lesson, f,
                    allow_unicode=True,
                    default_flow_style=False,
                    sort_keys=False,
                    width=120
                )
            
            self.stats['files_processed'] += 1
            return True
        
        except Exception as e:
            print(f"  ✗ Error processing {file_path.name}: {e}")
            self.stats['errors'] += 1
            return False
    
    def extract_target_language(self, pair_name: str) -> Optional[str]:
        """Extract target language from pair name.
        
        Examples:
            'ES-TO-RW' -> 'RW'
            'RW-TO-ES' -> 'ES'
        """
        parts = pair_name.split('-')
        if len(parts) >= 3 and parts[-2] == 'TO':
            return parts[-1]
        return None
    
    def process_language_pair(self, pair_name: str, content_dir: str = 'content') -> int:
        """Process all files in a language pair directory."""
        pair_dir = Path(content_dir) / pair_name
        target_lang = self.extract_target_language(pair_name)
        
        if not target_lang:
            print(f"✗ Could not extract target language from '{pair_name}'")
            return 0
        
        target_lang_code = LANGUAGE_CODES.get(target_lang)
        if not target_lang_code:
            print(f"✗ Unknown language code: {target_lang}")
            return 0
        
        if not pair_dir.exists():
            print(f"✗ Directory not found: {pair_dir}")
            return 0
        
        print(f"\n📁 Processing: {pair_name} → {target_lang} ({target_lang_code})")
        
        processed = 0
        for level_dir in sorted(pair_dir.glob('level*')):
            if level_dir.is_dir():
                for yaml_file in sorted(level_dir.glob('*.yaml')):
                    if self.process_lesson_file(yaml_file, target_lang_code):
                        print(f"  ✓ Updated: {yaml_file.name}")
                        processed += 1
                    else:
                        print(f"  - Skipped: {yaml_file.name}")
        
        return processed
    
    def discover_language_pairs(self, content_dir: str = 'content') -> List[str]:
        """Discover all language pair directories."""
        content_path = Path(content_dir)
        if not content_path.exists():
            return []
        
        pairs = sorted([
            d.name for d in content_path.glob('*-TO-*') 
            if d.is_dir() and '-' in d.name
        ])
        return pairs
    
    def process_all_pairs(self, content_dir: str = 'content', pairs: Optional[List[str]] = None) -> int:
        """Process all language pair directories."""
        if not pairs:
            pairs = self.discover_language_pairs(content_dir)
        
        if not pairs:
            print(f"✗ No language pair directories found in '{content_dir}'")
            return 0
        
        print(f"\n🚀 Starting translation for {len(pairs)} language pairs...")
        print(f"Target directory: {content_dir}\n")
        
        total_processed = 0
        for pair in pairs:
            processed = self.process_language_pair(pair, content_dir)
            total_processed += processed
        
        # Save cache
        self.save_cache()
        
        # Print statistics
        self.print_statistics()
        
        return total_processed
    
    def print_statistics(self):
        """Print processing statistics."""
        print("\n" + "="*60)
        print("📊 TRANSLATION STATISTICS")
        print("="*60)
        print(f"Files processed:        {self.stats['files_processed']}")
        print(f"Files skipped:          {self.stats['files_skipped']}")
        print(f"Translations made:      {self.stats['translations_made']}")
        print(f"Kinyarwanda preserved:  {self.stats['kinyarwanda_preserved']}")
        print(f"Errors:                 {self.stats['errors']}")
        print("="*60 + "\n")


def main():
    """Main entry point."""
    parser = argparse.ArgumentParser(
        description='Translate language pair files',
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
Examples:
  python3 translate_language_pairs.py --api-key YOUR_KEY --all
  python3 translate_language_pairs.py --pair ES-TO-RW
  python3 translate_language_pairs.py --content-dir ./content
        """
    )
    
    parser.add_argument('--api-key', help='Google Translate API key')
    parser.add_argument('--pair', help='Specific language pair (e.g., ES-TO-RW)')
    parser.add_argument('--pairs', nargs='+', help='Multiple language pairs')
    parser.add_argument('--all', action='store_true', help='Process all language pairs')
    parser.add_argument('--content-dir', default='content', help='Content directory path')
    parser.add_argument('--no-cache', action='store_true', help='Disable translation caching')
    
    args = parser.parse_args()
    
    # Initialize translator
    translator = LanguagePairTranslator(
        api_key=args.api_key,
        use_cache=not args.no_cache
    )
    
    # Determine what to process
    if args.pair:
        pairs = [args.pair]
    elif args.pairs:
        pairs = args.pairs
    elif args.all:
        pairs = translator.discover_language_pairs(args.content_dir)
    else:
        # Default: discover and process all
        pairs = translator.discover_language_pairs(args.content_dir)
    
    if not pairs:
        print("✗ No language pairs specified or discovered")
        sys.exit(1)
    
    # Process
    total = translator.process_all_pairs(args.content_dir, pairs)
    
    if total > 0:
        print(f"\n✓ Translation complete! {total} files updated.")
    else:
        print("\n✓ No files needed updating.")


if __name__ == '__main__':
    main()
