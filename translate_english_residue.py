"""Create a read-only review list of English remnants in course YAML files.

The script intentionally works on quoted YAML scalar values instead of loading
and re-dumping YAML.  This preserves comments, key names, ordering and the
indentation style used by the existing lesson files.

It never edits lesson files and never calls a translation service.  Its output
is a compact, reviewable list with the exact file, line, language pair and YAML
key.  Use that list to make deliberate translations rather than bulk-changing
the course material.

Examples
--------
    # Report candidate English values across every requested course.
    python translate_english_residue.py

    # Inspect a single course.
    python translate_english_residue.py --folder DE-TO-RW

    # Save the complete review list for a careful translation pass.
    python translate_english_residue.py --output english-residue-report.json
"""

from __future__ import annotations

import argparse
import json
import re
import sys
from pathlib import Path


# These are the exact folders requested for this audit.  The value is the
# language into which an English remnant must be translated.
LANGUAGES = {
    "AR": "Arabic", "CS": "Czech", "DA": "Danish", "DE": "German",
    "EL": "Greek", "ES": "Spanish", "FI": "Finnish", "GA": "Irish",
    "GD": "Scottish Gaelic", "HE": "Hebrew", "HI": "Hindi",
    "ID": "Indonesian", "IT": "Italian", "JA": "Japanese", "KO": "Korean",
    "LA": "Latin", "NL": "Dutch", "NO": "Norwegian", "PL": "Polish",
    "PT": "Portuguese", "RU": "Russian", "SV": "Swedish", "SW": "Swahili",
    "TR": "Turkish", "UK": "Ukrainian", "VI": "Vietnamese", "ZH": "Chinese",
    "ZU": "Zulu",
}

# AR-TO-RW was intentionally omitted from the requested set (it was audited
# separately); RW-TO-AR remains included.
FOLDERS = tuple(
    f"{code}-TO-RW" for code in LANGUAGES if code != "AR"
) + tuple(
    f"RW-TO-{code}" for code in LANGUAGES
)

# YAML metadata, URLs and machine-readable values must never be sent to the
# translator.  All other quoted text is eligible only if it looks English.
SKIP_KEYS = {
    "id", "type", "xp_reward", "difficulty", "time_limit_seconds", "topic",
    "audio", "audio_url", "audio_file", "tts_audio", "image", "image_url",
    "url", "link", "filename", "file", "video", "slug",
}

# This deliberately requires recognisable English words rather than merely
# ASCII text.  It prevents names, Kinyarwanda vocabulary and code values from
# being treated as English.  Add course-specific words with --word if needed.
ENGLISH_WORDS = {
    "a", "an", "and", "are", "ask", "at", "be", "can", "choose", "complete",
    "correct", "day", "do", "does", "english", "find", "for", "from", "good",
    "hello", "how", "i", "in", "is", "it", "learn", "lesson", "match", "means",
    "my", "name", "next", "of", "on", "or", "please", "question", "read", "say",
    "select", "sentence", "the", "this", "to", "translate", "translation", "what",
    "which", "with", "word", "write", "you", "your", "yesterday", "today",
    "tomorrow", "week", "month", "year", "will", "would", "should", "answer",
    "listen", "repeat", "look", "fill", "blank", "true", "false", "practice",
    "kinyarwanda", "rwanda", "family", "food", "weather", "travel", "business",
    "future", "past", "conditional", "opinion", "story", "color", "number",
    "animal", "animals", "name", "names", "category", "categories", "one", "two",
    "three", "four", "five", "six", "seven", "eight", "nine", "ten", "yes",
    "woman", "mother", "father", "brother", "sister", "cow", "dog", "cat",
    "goat", "sheep", "bird", "fish", "red", "blue", "green", "yellow", "black", "white",
}

# Short function words occur coincidentally in many languages.  On their own,
# or in pairs, they are not sufficiently reliable evidence of English.
WEAK_ENGLISH_WORDS = {
    "a", "an", "and", "are", "at", "be", "can", "do", "does", "for", "from", "i",
    "in", "is", "it", "my", "of", "on", "or", "the", "this", "to", "with", "you", "your",
}

KEY_VALUE = re.compile(
    r"^(?P<prefix>\s*(?:-\s+)?(?P<key>[^:#][^:]*):\s*)"
    r"(?P<quote>[\"'])(?P<value>(?:\\.|(?!\3).)*)\3(?P<suffix>\s*(?:#.*)?)$"
)
WORD = re.compile(r"[A-Za-z]+(?:'[A-Za-z]+)?")


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    parser.add_argument("--content-root", type=Path, default=Path("content"))
    parser.add_argument("--folder", action="append", choices=FOLDERS,
                        help="Limit the run to one folder; may be repeated.")
    parser.add_argument("--output", type=Path,
                        help="Optional JSON report path. This writes a report only, never a lesson file.")
    parser.add_argument("--word", action="append", default=[],
                        help="Additional English word that marks a value as a candidate; may be repeated.")
    parser.add_argument("--show", type=int, default=12, help="Number of proposed changes to display per folder.")
    return parser.parse_args()


def target_language(folder: str) -> str:
    """Return the non-Kinyarwanda language represented by a pair folder."""
    parts = folder.split("-TO-")
    code = parts[1] if parts[0] == "RW" else parts[0]
    return LANGUAGES[code]


def is_candidate(value: str, extra_words: set[str]) -> bool:
    words = {word.casefold() for word in WORD.findall(value)}
    matches = words & (ENGLISH_WORDS | extra_words)
    # Kinyarwanda is intentionally named in many correctly translated prompts;
    # it is not proof that the surrounding value is English.
    matches -= {"kinyarwanda", "rwanda"}
    return bool(matches - WEAK_ENGLISH_WORDS) or len(matches & WEAK_ENGLISH_WORDS) >= 3


def unescape_yaml(value: str, quote: str) -> str:
    if quote == "'":
        return value.replace("''", "'")
    try:
        # JSON decoding correctly handles the double-quoted YAML subset used
        # in these lessons, including escaped quotation marks.
        return json.loads(f'"{value}"')
    except json.JSONDecodeError:
        return value


def find_candidates(path: Path, extra_words: set[str]) -> list[tuple[int, str, str, str]]:
    """Return (line number, key, quote, decoded value) for English candidates."""
    result = []
    for number, line in enumerate(path.read_text(encoding="utf-8").splitlines(), 1):
        match = KEY_VALUE.match(line)
        if not match or match.group("key").strip().casefold() in SKIP_KEYS:
            continue
        value = unescape_yaml(match.group("value"), match.group("quote"))
        if is_candidate(value, extra_words):
            result.append((number, match.group("key").strip(), match.group("quote"), value))
    return result


def main() -> int:
    args = parse_args()
    folders = args.folder or FOLDERS
    extra_words = {word.casefold() for word in args.word}
    total_candidates = 0
    report: list[dict[str, object]] = []

    for folder in folders:
        root = args.content_root / folder
        if not root.is_dir():
            print(f"SKIP {folder}: directory not found")
            continue
        per_file = {path: find_candidates(path, extra_words) for path in sorted(root.rglob("*.yaml"))}
        per_file = {path: items for path, items in per_file.items() if items}
        values = list(dict.fromkeys(item[3] for items in per_file.values() for item in items))
        if not values:
            print(f"OK   {folder}: no English-looking quoted values")
            continue
        total_candidates += sum(len(items) for items in per_file.values())
        language = target_language(folder)
        print(f"SCAN {folder}: {sum(len(items) for items in per_file.values())} values in {len(per_file)} files -> review for {language}")
        shown = 0
        for path, items in per_file.items():
            for line, key, _quote, source in items:
                report.append({"folder": folder, "target_language": language,
                               "file": path.as_posix(), "line": line, "key": key,
                               "english_candidate": source})
                if shown < args.show:
                    print(f"  {path}:{line} [{key}] {source!r}")
                    shown += 1
        if sum(len(items) for items in per_file.values()) > args.show:
            print(f"  ... plus {sum(len(items) for items in per_file.values()) - args.show} values")

    if args.output:
        args.output.write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
        print(f"\nWrote read-only review report: {args.output}")
    print(f"\nFound {total_candidates} English-looking values. No lesson files were changed.")
    return 0


if __name__ == "__main__":
    try:
        raise SystemExit(main())
    except (RuntimeError, ValueError, OSError) as exc:
        print(f"ERROR: {exc}", file=sys.stderr)
        raise SystemExit(1)
