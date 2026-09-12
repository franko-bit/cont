"""Translate EN-TO-RW lesson YAML with the local Phi-2 GGUF model.

This tool is preview-only unless --apply is supplied. It creates LANG-TO-RW
lessons, keeps Kinyarwanda values and audio references unchanged, and does not
generate audio for the new language.
"""

from __future__ import annotations

import argparse
import copy
import json
import re
import urllib.error
import urllib.request
from pathlib import Path
from typing import Any

import yaml


LANGUAGES = {
    "es": "Spanish", "fr": "French", "de": "German", "it": "Italian",
    "pt": "Portuguese", "ja": "Japanese", "ko": "Korean", "zh": "Mandarin Chinese",
    "ar": "Arabic", "ru": "Russian", "hi": "Hindi", "tr": "Turkish",
    "nl": "Dutch", "sv": "Swedish", "ga": "Irish", "el": "Greek",
    "he": "Hebrew", "pl": "Polish", "no": "Norwegian", "da": "Danish",
    "fi": "Finnish", "cs": "Czech", "id": "Indonesian", "uk": "Ukrainian",
    "sw": "Kiswahili", "vi": "Vietnamese", "zu": "Zulu", "gd": "Scottish Gaelic",
    "la": "Latin",
}

IMMUTABLE_KEYS = {
    "id", "type", "xp_reward", "difficulty", "time_limit_seconds", "topic",
    "audio", "audio_url", "audio_file", "tts_audio", "image", "image_url",
    "answer", "correct_answer", "correct", "pairs", "scrambled_words",
}


def arguments() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--source", required=True, type=Path)
    parser.add_argument("--pair", required=True, help="Language pair, for example es-rw")
    parser.add_argument("--model", default="qwen2.5:3b", help="Installed Ollama model")
    parser.add_argument("--ollama-url", default="http://127.0.0.1:11434/api/generate")
    parser.add_argument("--output-root", type=Path, default=Path("content"))
    parser.add_argument("--apply", action="store_true")
    return parser.parse_args()


def target_language(pair: str) -> str:
    parts = pair.lower().split("-")
    if len(parts) != 2 or parts[1] != "rw" or parts[0] not in LANGUAGES:
        raise ValueError("only LANG-RW pairs are supported, for example es-rw")
    return parts[0]


def collect_kinyarwanda(value: Any, key: str = "") -> set[str]:
    values: set[str] = set()
    if isinstance(value, dict):
        for child_key, child in value.items():
            values.update(collect_kinyarwanda(child, child_key))
    elif isinstance(value, list):
        if key == "rows":
            for row in value:
                if isinstance(row, list) and len(row) > 1 and isinstance(row[1], str):
                    values.add(row[1])
        for child in value:
            values.update(collect_kinyarwanda(child, key))
    elif isinstance(value, str) and key in {"answer", "correct_answer", "correct", "kinyarwanda"}:
        values.add(value)
    return {item for item in values if item.strip()}


def protect(text: str, values: set[str]) -> tuple[str, dict[str, str]]:
    replacements: dict[str, str] = {}
    for index, value in enumerate(sorted(values, key=len, reverse=True)):
        marker = f"__RW_{index}__"
        if value in text:
            text = text.replace(value, marker)
            replacements[marker] = value
    return text, replacements


def source_strings(value: Any, key: str = "") -> list[str]:
    if isinstance(value, dict):
        result: list[str] = []
        for child_key, child in value.items():
            if child_key not in IMMUTABLE_KEYS:
                result.extend(source_strings(child, child_key))
        return result
    if isinstance(value, list):
        if key == "rows":
            return [row[0] for row in value if isinstance(row, list) and row and isinstance(row[0], str)]
        return [item for child in value for item in source_strings(child, key)]
    if isinstance(value, str) and value.strip():
        return [value]
    return []


def translate_batch(args: argparse.Namespace, texts: list[str], language: str, protected: set[str]) -> dict[str, str]:
    lines = []
    replacements_by_index: dict[int, dict[str, str]] = {}
    for index, text in enumerate(texts, 1):
        masked, replacements_by_index[index - 1] = protect(text, protected)
        lines.append(f"{index}. {masked}")
    prompt = (
        f"Translate each numbered English lesson text into {LANGUAGES[language]}.\n"
        "Return exactly one numbered translation per line, using the same numbers. "
        "Keep __RW_ markers unchanged. Use short beginner-friendly wording.\n\n"
        + "\n".join(lines)
        + "\n\nTranslations:\n"
    )
    payload = json.dumps({
        "model": args.model,
        "prompt": prompt,
        "stream": False,
        "options": {
            "temperature": 0.1,
            "num_predict": min(900, max(120, sum(len(item) for item in texts) * 2)),
        },
    }).encode("utf-8")
    request = urllib.request.Request(
        args.ollama_url,
        data=payload,
        headers={"Content-Type": "application/json"},
    )
    try:
        with urllib.request.urlopen(request, timeout=600) as response:
            output = json.loads(response.read().decode("utf-8")).get("response", "")
    except (urllib.error.URLError, TimeoutError) as exc:
        raise RuntimeError(f"Could not contact Ollama at {args.ollama_url}: {exc}") from exc
    translated: dict[str, str] = {}
    for line in output.splitlines():
        match = re.match(r"^\s*(\d+)\.\s*(.+?)\s*$", line)
        if match:
            index = int(match.group(1)) - 1
            if 0 <= index < len(texts):
                value = match.group(2)
                for marker, original in replacements_by_index[index].items():
                    value = value.replace(marker, original)
                translated[texts[index]] = value
    return translated


def translate_tree(value: Any, key: str, translations: dict[str, str]) -> Any:
    if isinstance(value, dict):
        return {
            child_key: (
                child
                if child_key in IMMUTABLE_KEYS
                else translate_tree(child, child_key, translations)
            )
            for child_key, child in value.items()
        }

    if isinstance(value, list):
        # Vocabulary and phrase rows are [English, Kinyarwanda]. Only translate
        # the first column; the Kinyarwanda column is copied byte-for-byte.
        if key == "rows":
            return [
                [translations.get(row[0], row[0]), *row[1:]]
                if isinstance(row, list) and row
                else translate_tree(row, key, translations)
                for row in value
            ]
        return [translate_tree(child, key, translations) for child in value]

    if isinstance(value, str) and value.strip():
        return translations.get(value, value)

    return value


def main() -> None:
    args = arguments()
    language = target_language(args.pair)
    if not args.source.exists():
        raise FileNotFoundError(args.source)
    data = yaml.safe_load(args.source.read_text(encoding="utf-8")) or {}
    protected = collect_kinyarwanda(data)
    texts = list(dict.fromkeys(source_strings(data)))
    translations: dict[str, str] = {}
    for start in range(0, len(texts), 1):
        batch = texts[start:start + 1]
        translations.update(translate_batch(args, batch, language, protected))
    translated = translate_tree(copy.deepcopy(data), "", translations)

    output = args.output_root / f"{language.upper()}-TO-RW" / args.source.parent.name / args.source.name
    rendered = yaml.safe_dump(translated, allow_unicode=True, sort_keys=False, width=120)
    if args.apply:
        output.parent.mkdir(parents=True, exist_ok=True)
        output.write_text(rendered, encoding="utf-8")
        print(f"Wrote {output}")
    else:
        print(f"Preview only. Would write {output}")
        print(rendered)


if __name__ == "__main__":
    main()