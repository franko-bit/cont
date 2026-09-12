#!/usr/bin/env python3
"""
Translation Verification Script
Verifies that Kinyarwanda content is preserved and English is translated.

Usage:
    python3 verify_translations.py --pair ES-TO-RW
    python3 verify_translations.py --all
"""

import sys
import json
from pathlib import Path
from typing import Dict, List, Tuple
import yaml

# Kinyarwanda indicators
KINYARWANDA_INDICATORS = {
    'Muraho', 'Mwaramutse', 'mwana', 'Data', 'Mama', 'Amakuru',
    'Mwiriwe', 'Ijoro', 'ryiza', 'Tuzasubira', 'Murabeho',
    'Murakaza', 'neza', 'murakoze', 'Ni meza', 'Nishimiye',
    'kukubona', 'Wirinde', 'Hashize', 'igihe', 'tutabonanye',
    'nshuti', 'yanjye', 'ishuri', 'umunsi', 'mwiza', 'kazi',
    'rugendo', 'rwawe', 'mu', 'ku', 'uyu', 'munsi', 'ejo'
}

class TranslationVerifier:
    """Verifies translation quality and completeness."""
    
    def __init__(self):
        self.issues = []
        self.warnings = []
        self.stats = {
            'files_checked': 0,
            'exercises_checked': 0,
            'kinyarwanda_found': 0,
            'potential_english_found': 0,
            'issues': 0,
            'warnings': 0
        }
    
    def is_kinyarwanda(self, text: str) -> bool:
        """Check if text is Kinyarwanda."""
        if not isinstance(text, str):
            return False
        return any(indicator in text for indicator in KINYARWANDA_INDICATORS)
    
    def looks_like_english(self, text: str) -> bool:
        """Check if text looks like English (simple heuristic)."""
        if not isinstance(text, str) or len(text) < 3:
            return False
        
        # Skip if contains Kinyarwanda
        if self.is_kinyarwanda(text):
            return False
        
        # Skip if mostly non-ASCII (likely already translated)
        non_ascii = sum(1 for c in text if ord(c) > 127)
        if len(text) > 0 and non_ascii / len(text) > 0.5:
            return False
        
        # Common English words
        english_words = {
            'the', 'you', 'how', 'say', 'what', 'mean',
            'hello', 'good', 'morning', 'evening', 'night',
            'is', 'are', 'have', 'do', 'does', 'did'
        }
        
        words = text.lower().split()
        return any(word.strip('?!.,;:') in english_words for word in words)
    
    def check_exercise(self, exercise: Dict, file_path: str, exercise_id: int) -> Tuple[List[str], List[str]]:
        """Check a single exercise for issues."""
        issues = []
        warnings = []
        
        for key, value in exercise.items():
            if isinstance(value, str):
                self.check_field(key, value, file_path, exercise_id, issues, warnings)
            elif isinstance(value, list) and value and isinstance(value[0], str):
                for i, item in enumerate(value):
                    self.check_field(f"{key}[{i}]", item, file_path, exercise_id, issues, warnings)
        
        return issues, warnings
    
    def check_field(self, field: str, text: str, file_path: str, ex_id: int, issues: List, warnings: List):
        """Check a single field."""
        # Skip technical fields
        skip_fields = {'id', 'type', 'xp_reward', 'tts_text', 'name', 'answer', 'correct_answer'}
        if any(field.startswith(f) for f in skip_fields):
            return
        
        # Check if Kinyarwanda
        if self.is_kinyarwanda(text):
            self.stats['kinyarwanda_found'] += 1
            return
        
        # Check if potential English
        if self.looks_like_english(text):
            self.stats['potential_english_found'] += 1
            warning = f"{file_path}:ex{ex_id}:{field} - Potential English: {text[:60]}"
            warnings.append(warning)
            self.warnings.append(warning)
    
    def check_file(self, file_path: Path) -> bool:
        """Check a lesson file."""
        try:
            with open(file_path, 'r', encoding='utf-8') as f:
                lesson = yaml.safe_load(f)
            
            if not lesson or 'exercises' not in lesson:
                return False
            
            self.stats['files_checked'] += 1
            file_issues = []
            file_warnings = []
            
            for i, exercise in enumerate(lesson['exercises']):
                self.stats['exercises_checked'] += 1
                issues, warnings = self.check_exercise(exercise, str(file_path), i)
                file_issues.extend(issues)
                file_warnings.extend(warnings)
            
            if file_issues:
                self.stats['issues'] += len(file_issues)
                self.issues.extend(file_issues)
            
            if file_warnings:
                self.stats['warnings'] += len(file_warnings)
            
            return True
        
        except Exception as e:
            self.issues.append(f"Error checking {file_path}: {e}")
            return False
    
    def check_language_pair(self, pair_name: str, content_dir: str = 'content') -> bool:
        """Check all files in a language pair directory."""
        pair_dir = Path(content_dir) / pair_name
        
        if not pair_dir.exists():
            print(f"✗ Directory not found: {pair_dir}")
            return False
        
        print(f"\n🔍 Verifying: {pair_name}")
        
        checked = 0
        for level_dir in sorted(pair_dir.glob('level*')):
            if level_dir.is_dir():
                for yaml_file in sorted(level_dir.glob('*.yaml')):
                    if self.check_file(yaml_file):
                        checked += 1
        
        print(f"  Files verified: {checked}")
        return True
    
    def check_all_pairs(self, content_dir: str = 'content', pairs: List[str] = None) -> bool:
        """Check all language pairs."""
        if not pairs:
            content_path = Path(content_dir)
            pairs = sorted([
                d.name for d in content_path.glob('*-TO-*')
                if d.is_dir()
            ])
        
        if not pairs:
            print(f"✗ No language pairs found in {content_dir}")
            return False
        
        print(f"\n🚀 Verifying {len(pairs)} language pairs...\n")
        
        for pair in pairs:
            self.check_language_pair(pair, content_dir)
        
        return True
    
    def print_report(self):
        """Print verification report."""
        print("\n" + "="*60)
        print("📋 VERIFICATION REPORT")
        print("="*60)
        print(f"Files checked:           {self.stats['files_checked']}")
        print(f"Exercises checked:       {self.stats['exercises_checked']}")
        print(f"Kinyarwanda found:       {self.stats['kinyarwanda_found']}")
        print(f"Potential English found: {self.stats['potential_english_found']}")
        print(f"Issues:                  {self.stats['issues']}")
        print(f"Warnings:                {self.stats['warnings']}")
        print("="*60 + "\n")
        
        if self.issues:
            print("🚨 ISSUES:")
            for issue in self.issues[:20]:  # Limit to first 20
                print(f"  - {issue}")
            if len(self.issues) > 20:
                print(f"  ... and {len(self.issues) - 20} more")
        
        if self.warnings:
            print("⚠️  WARNINGS:")
            for warning in self.warnings[:20]:  # Limit to first 20
                print(f"  - {warning}")
            if len(self.warnings) > 20:
                print(f"  ... and {len(self.warnings) - 20} more")
        
        if not self.issues and not self.warnings:
            print("✓ All checks passed!")
        
        print()


if __name__ == '__main__':
    import argparse
    
    parser = argparse.ArgumentParser(description='Verify language pair translations')
    parser.add_argument('--pair', help='Specific language pair to check')
    parser.add_argument('--pairs', nargs='+', help='Multiple language pairs')
    parser.add_argument('--all', action='store_true', help='Check all pairs')
    parser.add_argument('--content-dir', default='content', help='Content directory')
    
    args = parser.parse_args()
    
    verifier = TranslationVerifier()
    
    if args.pair:
        pairs = [args.pair]
    elif args.pairs:
        pairs = args.pairs
    else:
        pairs = None
    
    verifier.check_all_pairs(args.content_dir, pairs)
    verifier.print_report()
