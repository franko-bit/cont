"""Translate lesson YAML with a local Ollama model.

Preview is the default. --apply is required before any generated lesson is
written. Kinyarwanda answer values are protected and no audio is generated.
"""

from __future__ import annotations

import argparse
import copy
import json
import sys
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

PROTECTED_KEYS = {
    "id", "type", "xp_reward", "difficulty", "time_limit_seconds", "topic",
    "audio", "audio_url", "audio_file", "tts_audio", "image", "image_url",
}


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--source", required=True, type=Path, help="Canonical EN-TO-RW YAML lesson")
    parser.add_argument("--pair", required=True, help="Pair code, for example es-rw")
    parser.add_argument("--model", default="mistral:latest", help="Installed Ollama model")
    parser.add_argument("--ollama-url", default="http://127.0.0.1:11434/api/generate")
    parser.add_argument("--output-root", type=Path, default=Path("content"))
    parser.add_argument("--apply", action="store_true", help="Write the generated lesson")
    return parser.parse_args()


def validate_pair(pair: str) -> str:
    parts = pair.lower().split("-")
    if len(parts) != 2 or "rw" not in parts or parts[0] == parts[1]:
        raise ValueError("pair must contain rw, such as es-rw")
    other = parts[0] if parts[1] == "rw" else parts[1]
    if other not in LANGUAGES:
        raise ValueError(f"unsupported language code: {other}")
    return other


def ollama_generate(url: str, model: str, prompt: str) -> str:
    payload = json.dumps({
        "model": model,
        "prompt": prompt,
        "stream": False,
        "options": {"temperature": 0.1},
    }).encode("utf-8")
    request = urllib.request.Request(url, data=payload, headers={"Content-Type": "application/json"})
    try:
        with urllib.request.urlopen(request, timeout=600) as response:
            result = json.loads(response.read().decode("utf-8"))
    except (urllib.error.URLError, TimeoutError) as exc:
        raise RuntimeError(f"Could not contact Ollama at {url}: {exc}") from exc
    return str(result.get("response", "")).strip()


def collect_kinyarwanda_values(value: Any, key: str = "") -> set[str]:
    protected: set[str] = set()
    if isinstance(value, dict):
        for child_key, child in value.items():
            protected.update(collect_kinyarwanda_values(child, child_key))
    elif isinstance(value, list):
        for index, child in enumerate(value):
            if key == "rows" and isinstance(child, list) and len(child) >= 2:
                if isinstance(child[1], str):
                    protected.add(child[1])
            protected.update(collect_kinyarwanda_values(child, key))
    elif isinstance(value, str) and key in {"answer", "correct_answer", "target", "kinyarwanda"}:
        protected.add(value)
    return {item for item in protected if item.strip()}


def protect_kinyarwanda(text: str, values: set[str]) -> tuple[str, dict[str, str]]:
    replacements: dict[str, str] = {}
    for index, original in enumerate(sorted(values, key=len, reverse=True)):
        marker = f"__KINYARWANDA_{index}__"
        if original in text:
            text = text.replace(original, marker)
            replacements[marker] = original
    return text, replacements


def restore_kinyarwanda(text: str, replacements: dict[str, str]) -> str:
    for marker, original in replacements.items():
        text = text.replace(marker, original)
    return text


def translate_texts(texts: list[str], language: str, values: set[str], args: argparse.Namespace) -> list[str]:
    masked_texts = []
    replacements = []
    for text in texts:
        masked, mapping = protect_kinyarwanda(text, values)
        masked_texts.append(masked)
        replacements.append(mapping)

    prompt = f"""Translate each learner-facing lesson string from English to {LANGUAGES[language]}.

Rules:
- Return a JSON array with exactly {len(texts)} translated strings, in the same order.
- Return JSON only, with no markdown or commentary.
- Keep markers such as __KINYARWANDA_0__ exactly unchanged.
- Never translate, remove, or alter protected Kinyarwanda markers.
- Preserve quoted words and punctuation unless grammar requires a change.
- Use natural, beginner-friendly wording.

Strings:
{json.dumps(masked_texts, ensure_ascii=False)}"""
    response = ollama_generate(args.ollama_url, args.model, prompt)
    try:
        translated = json.loads(response)
    except json.JSONDecodeError as exc:
        raise RuntimeError(f"Ollama returned invalid JSON: {response[:300]}") from exc
    if not isinstance(translated, list) or len(translated) != len(texts):
        raise RuntimeError("Ollama returned the wrong number of translated strings")
    return [restore_kinyarwanda(str(text), mapping) for text, mapping in zip(translated, replacements)]


def collect_translatable(value: Any, key: str, items: list[tuple[list[Any], str]], path: list[Any] | None = None) -> None:
    path = path or []
    if isinstance(value, dict):
        for child_key, child in value.items():
            if child_key not in PROTECTED_KEYS:
                collect_translatable(child, child_key, items, path + [child_key])
    elif isinstance(value, list):
        for index, child in enumerate(value):
            collect_translatable(child, key, items, path + [index])
    elif isinstance(value, str) and value.strip():
        items.append((path, value))


def set_path(value: Any, path: list[Any], replacement: str) -> None:
    target = value
    for part in path[:-1]:
        target = target[part]
    target[path[-1]] = replacement


def translate_tree(value: Any, key: str, language: str, protected: set[str], args: argparse.Namespace) -> Any:
    if isinstance(value, dict):
        return {
            child_key: (child if child_key in PROTECTED_KEYS else translate_tree(child, child_key, language, protected, args))
            for child_key, child in value.items()
        }
    if isinstance(value, list):
        return [translate_tree(child, key, language, protected, args) for child in value]
    return value


def main() -> int:
    args = parse_args()
    language = validate_pair(args.pair)
    if not args.source.exists():
        raise FileNotFoundError(args.source)

    source_data = yaml.safe_load(args.source.read_text(encoding="utf-8")) or {}
    protected = collect_kinyarwanda_values(source_data)
    translated = copy.deepcopy(source_data)
    items: list[tuple[list[Any], str]] = []
    collect_translatable(source_data, "", items)
    translations = translate_texts([text for _, text in items], language, protected, args)
    for (path, _), text in zip(items, translations):
        set_path(translated, path, text)
    output_path = args.output_root / f"{language.upper()}-TO-RW" / args.source.parent.name / args.source.name
    rendered = yaml.safe_dump(translated, allow_unicode=True, sort_keys=False, width=120)

    if args.apply:
        output_path.parent.mkdir(parents=True, exist_ok=True)
        output_path.write_text(rendered, encoding="utf-8")
        print(f"Wrote {output_path}")
    else:
        print(f"Preview only. Would write {output_path}")
        print(rendered)
    return 0


if __name__ == "__main__":
    try:
        raise SystemExit(main())
    except (ValueError, FileNotFoundError, RuntimeError, yaml.YAMLError) as exc:
        print(f"ERROR: {exc}", file=sys.stderr)
        raise SystemExit(1)