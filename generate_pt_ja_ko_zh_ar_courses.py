from __future__ import annotations

import copy
import json
import re
import time
from pathlib import Path

import requests
import yaml
from transformers import AutoModelForSeq2SeqLM, AutoTokenizer

SOURCE_ROOT = Path('content/EN-TO-RW')
CACHE_FILE = Path('.translation_cache_pt_ja_ko_zh_ar.json')

LANGUAGES = {
    'pt': {'folder': 'PT-TO-RW', 'model_code': 'por_Latn'},
    'ja': {'folder': 'JA-TO-RW', 'model_code': 'jpn_Jpan'},
    'ko': {'folder': 'KO-TO-RW', 'model_code': 'kor_Hang'},
    'zh': {'folder': 'ZH-TO-RW', 'model_code': 'zho_Hans'},
    'ar': {'folder': 'AR-TO-RW', 'model_code': 'arb_Arab'},
}

PROTECTED_KEYS = {
    'id', 'type', 'xp_reward', 'difficulty', 'time_limit_seconds', 'topic',
    'audio', 'audio_url', 'audio_file', 'tts_audio', 'image', 'image_url', 'link',
}

# These fields contain Kinyarwanda source material or machine-checked values.
PROTECTED_VALUE_KEYS = {
    'answer', 'correct_answer', 'correct_sentence', 'correct_response', 'tts_text',
    'prompt', 'statement', 'context_sentence', 'bot_message', 'text_with_blank',
    'sentence', 'word', 'front', 'back', 'left', 'right',
}

MODEL_NAME = 'facebook/nllb-200-distilled-600M'
tokenizer = AutoTokenizer.from_pretrained(MODEL_NAME)
model = AutoModelForSeq2SeqLM.from_pretrained(MODEL_NAME)
cache = json.loads(CACHE_FILE.read_text(encoding='utf-8')) if CACHE_FILE.exists() else {}


def translate_remote(text: str, lang: str) -> str:
    if not text or text.startswith(('http://', 'https://')) or not re.search(r'[A-Za-z]', text):
        return text
    cache_key = f'{lang}|{text}'
    if cache_key in cache:
        return cache[cache_key]

    tokenizer.src_lang = 'eng_Latn'
    inputs = tokenizer(text, return_tensors='pt', truncation=True, max_length=512)
    output = model.generate(
        **inputs,
        forced_bos_token_id=tokenizer.convert_tokens_to_ids(LANGUAGES[lang]['model_code']),
        max_length=512,
    )
    translated = tokenizer.batch_decode(output, skip_special_tokens=True)[0].strip() or text
    cache[cache_key] = translated
    CACHE_FILE.write_text(json.dumps(cache, ensure_ascii=False, indent=2), encoding='utf-8')
    time.sleep(0.12)
    return translated


def collect_translatable(value, key='', output=None):
    output = output if output is not None else set()
    if isinstance(value, dict):
        for child_key, child in value.items():
            if child_key not in PROTECTED_KEYS | PROTECTED_VALUE_KEYS:
                collect_translatable(child, child_key, output)
    elif isinstance(value, list):
        for child in value:
            collect_translatable(child, key, output)
    elif isinstance(value, str) and not value.startswith(('http://', 'https://')) and re.search(r'[A-Za-z]', value):
        output.add(value)
    return output


def batch_translate(texts, lang):
    missing = [text for text in texts if f'{lang}|{text}' not in cache]
    for start in range(0, len(missing), 128):
        batch = missing[start:start + 128]
        tokenizer.src_lang = 'eng_Latn'
        inputs = tokenizer(batch, return_tensors='pt', padding=True, truncation=True, max_length=96)
        output = model.generate(
            **inputs,
            forced_bos_token_id=tokenizer.convert_tokens_to_ids(LANGUAGES[lang]['model_code']),
            max_length=64,
            num_beams=1,
            do_sample=False,
        )
        translations = tokenizer.batch_decode(output, skip_special_tokens=True)
        for original, translated in zip(batch, translations):
            cache[f'{lang}|{original}'] = translated.strip() or original
        CACHE_FILE.write_text(json.dumps(cache, ensure_ascii=False, indent=2), encoding='utf-8')
        print(f'Translated {min(start + len(batch), len(missing))}/{len(missing)} strings for {lang}')


def translate_value(value, key='', lang='pt'):
    if isinstance(value, dict):
        return {
            child_key: copy.deepcopy(child) if child_key in PROTECTED_KEYS or child_key in PROTECTED_VALUE_KEYS
            else translate_value(child, child_key, lang)
            for child_key, child in value.items()
        }
    if isinstance(value, list):
        return [translate_value(item, key, lang) for item in value]
    if isinstance(value, str):
        if key in PROTECTED_KEYS or key in PROTECTED_VALUE_KEYS:
            return value
        return translate_remote(value, lang)
    return value


def second_layer_cleanup(value, key='', lang='pt'):
    if isinstance(value, dict):
        return {child_key: second_layer_cleanup(child, child_key, lang) for child_key, child in value.items()}
    if isinstance(value, list):
        return [second_layer_cleanup(item, key, lang) for item in value]
    if isinstance(value, str) and key not in PROTECTED_KEYS | PROTECTED_VALUE_KEYS:
        english_marker = re.search(r"\b(How do you say|What is|What does|Build|Listen|Choose|Translate|The|This|That|I|We|You|He|She|They|A|An)\b", value)
        if english_marker:
            return translate_remote(value, lang)
    return value


def generate(lang: str):
    folder = Path('content') / LANGUAGES[lang]['folder']
    source_data = []
    strings = set()
    for source_file in sorted(SOURCE_ROOT.rglob('*.yaml')):
        data = yaml.safe_load(source_file.read_text(encoding='utf-8')) or {}
        source_data.append((source_file, data))
        collect_translatable(data, output=strings)
    batch_translate(strings, lang)
    for source_file, source in source_data:
        output_file = folder / source_file.relative_to(SOURCE_ROOT)
        output_file.parent.mkdir(parents=True, exist_ok=True)
        translated = translate_value(source, lang=lang)
        translated = second_layer_cleanup(translated, lang=lang)
        output_file.write_text(yaml.safe_dump(translated, allow_unicode=True, sort_keys=False, width=120), encoding='utf-8')
        print(f'Wrote {output_file}')


def validate(lang: str):
    source_files = sorted(SOURCE_ROOT.rglob('*.yaml'))
    output_files = sorted((Path('content') / LANGUAGES[lang]['folder']).rglob('*.yaml'))
    assert len(source_files) == len(output_files), (lang, len(source_files), len(output_files))
    for source_file, output_file in zip(source_files, output_files):
        source = yaml.safe_load(source_file.read_text(encoding='utf-8')) or {}
        output = yaml.safe_load(output_file.read_text(encoding='utf-8')) or {}
        assert [item.get('id') for item in source.get('exercises', [])] == [item.get('id') for item in output.get('exercises', [])]
        assert [item.get('xp_reward') for item in source.get('exercises', [])] == [item.get('xp_reward') for item in output.get('exercises', [])]
    print(f'VALID: {lang.upper()} course generated with matching exercise IDs and XP values')


if __name__ == '__main__':
    for language in LANGUAGES:
        generate(language)
        validate(language)
