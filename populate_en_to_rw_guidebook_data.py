from pathlib import Path
import re
import yaml

class NoAliasDumper(yaml.SafeDumper):
    def ignore_aliases(self, data):
        return True

root = Path('content/EN-TO-RW')


def extract_eng_answer(question: str, answer: str):
    if not question or not answer:
        return None
    match = re.search(r"'([^']+)'", question)
    if not match:
        return None
    eng = match.group(1).strip()
    if not eng:
        return None
    return [eng, answer]


for path in sorted(root.rglob('*.yaml')):
    text = path.read_text(encoding='utf-8')
    data = yaml.safe_load(text) or {}
    gd = data.setdefault('guidebook_data', {})

    if not isinstance(data, dict):
        continue

    exercises = data.get('exercises') or []
    all_pairs = []
    phrase_pairs = []
    examples = []

    for ex in exercises:
        if not isinstance(ex, dict):
            continue
        q = ex.get('question') or ''
        ans = ex.get('answer')
        if not ans:
            continue
        pair = extract_eng_answer(q, str(ans))
        if pair:
            all_pairs.append(pair)
            if len(pair[0].split()) <= 4:
                phrase_pairs.append(pair)

        if ex.get('type') == 'translation' and isinstance(ans, str) and 'How do you say' in q:
            if not examples:
                examples.append(f"{pair[0]} → {pair[1]}" if pair else q)
            elif len(examples) < 6:
                examples.append(f"{pair[0]} → {pair[1]}" if pair else q)

    if not all_pairs:
        all_pairs = [['Word 1', 'Example 1'], ['Word 2', 'Example 2'], ['Word 3', 'Example 3'], ['Word 4', 'Example 4']]
    if not phrase_pairs:
        phrase_pairs = all_pairs[:6]

    vocab_sections = gd.get('vocabulary') or [{"title": "📚 Vocabulary", "sections": [{"category": "Core words", "rows": []}]}]
    if isinstance(vocab_sections, list):
        for section in vocab_sections:
            if isinstance(section, dict):
                for inner in section.get('sections', []):
                    if isinstance(inner, dict) and not inner.get('rows'):
                        inner['rows'] = all_pairs[:10]
                if not section.get('sections'):
                    section['sections'] = [{"category": "Core words", "rows": all_pairs[:10]}]
    else:
        vocab_sections = [{"title": "📚 Vocabulary", "sections": [{"category": "Core words", "rows": all_pairs[:10]}]}]
    gd['vocabulary'] = vocab_sections

    if not gd.get('key_phrases'):
        gd['key_phrases'] = {'title': '💬 Key Phrases', 'rows': phrase_pairs[:6]}
    elif isinstance(gd.get('key_phrases'), dict):
        if not gd['key_phrases'].get('rows'):
            gd['key_phrases']['rows'] = phrase_pairs[:6]

    if not gd.get('examples'):
        gd['examples'] = {'title': '📝 Example Sentences', 'items': examples[:6]}
    elif isinstance(gd.get('examples'), dict):
        if not gd['examples'].get('items'):
            gd['examples']['items'] = examples[:6]

    if not gd.get('quick_reference'):
        gd['quick_reference'] = {'title': '🔍 Quick Reference', 'rows': all_pairs[:6]}
    elif isinstance(gd.get('quick_reference'), dict):
        if not gd['quick_reference'].get('rows'):
            gd['quick_reference']['rows'] = all_pairs[:6]

    if not gd.get('unit_goal'):
        lesson_name = data.get('lesson', {}).get('name', 'Lesson')
        gd['unit_goal'] = {
            'title': '🎯 Unit Goal',
            'intro': f'By the end of this lesson, you will be able to use the main vocabulary and simple sentence patterns for {lesson_name} with confidence.',
            'goals': [
                'Learn the main vocabulary of this lesson',
                'Use the new words in simple sentences',
                'Practice listening, speaking, and translation'
            ],
            'xp_available': 'Total XP Available: 500 XP'
        }

    if not gd.get('footer'):
        gd['footer'] = 'Good luck! Continue with the exercises to reinforce your learning. 🔥'

    # ensure generic placeholder values are replaced for empty rows/items anywhere
    for section in gd.get('vocabulary', []):
        if isinstance(section, dict):
            for inner in section.get('sections', []):
                if isinstance(inner, dict) and inner.get('rows') == []:
                    inner['rows'] = all_pairs[:10]

    if 'header' not in gd or not gd['header']:
        gd['header'] = f"SECTION {path.parent.name.replace('level', '')}, UNIT {path.parent.name.replace('level', '')} · {data.get('lesson', {}).get('name', path.stem.replace('-', ' ').title())}"

    if 'intro' not in gd or not gd['intro']:
        gd['intro'] = 'Welcome to this lesson! In this unit, you will build vocabulary, sentence patterns, and practical speaking skills.'

    text = yaml.dump(data, Dumper=NoAliasDumper, allow_unicode=True, sort_keys=False)
    # Normalize nested pair rows like:
    #   - - Cow
    #     - Inka
    # into inline arrays so the PHP fallback parser can handle them without a YAML extension.
    while True:
        replaced = re.sub(
            r'(?m)^(\s*-\s*)-\s*(.+)\n(\s*-\s*)(.+)$',
            lambda m: f"{m.group(1)}[{m.group(2)}, {m.group(4)}]",
            text,
        )
        if replaced == text:
            break
        text = replaced

    text = re.sub(r'(?m)^\s*(?:-\s*)?[&*][A-Za-z0-9_\-]+\s*$', '', text)
    text = re.sub(r'(?<![A-Za-z0-9_])[&*][A-Za-z0-9_\-]+', '', text)
    text = re.sub(r'\n{3,}', '\n\n', text)
    path.write_text(text, encoding='utf-8')
    print(f'updated {path}')
