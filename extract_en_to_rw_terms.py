from pathlib import Path
import re
import json

root = Path('content/EN-TO-RW')
output = {
    'files': [],
    'vocab_pairs': [],
    'key_phrase_pairs': [],
    'example_sentences': [],
    'grammar_items': [],
    'unit_goals': [],
    'footer_lines': [],
}

for path in sorted(root.rglob('*.yaml')):
    text = path.read_text(encoding='utf-8')
    match = re.search(r'guidebook_data:\s*\n(?P<body>.*?)(?=^exercises:|\Z)', text, re.S | re.M)
    if not match:
        continue
    body = match.group('body')
    pairs = re.findall(r'- \["([^\"]+)",\s*"([^\"]+)"\]', body)
    if pairs:
        output['vocab_pairs'].extend(pairs)
        output['key_phrase_pairs'].extend(pairs)
    for line in body.splitlines():
        line = line.strip()
        if line.startswith('- "') and line.endswith('"'):
            value = line[3:-1]
            if not value:
                continue
            if value.startswith(('Can', 'Could', 'Please', 'How', 'I ', 'Let', 'A ', 'The ', 'Where', 'Ushobora', 'Nyamuneka', 'Ni gute', 'Sinasobanukiwe', 'Ndashaka', 'Subiramo', 'Yego', 'Tuganire', 'Ngerageza', 'Nshobora', 'Yego,', 'Muraho')):
                output['example_sentences'].append(value)
            elif value.startswith(('Use', 'Ask', 'By', 'Practice', 'Learn', 'Repeat', 'In ', 'Notice', 'Use a ', 'Use an ', 'Muri', 'Ushobora', 'Nyamuneka', 'Ni gute', 'Sinasobanukiwe', 'Yego', 'Tuganire', 'Subiramo', 'Komeza')):
                output['grammar_items'].append(value)
            elif value.startswith(('•', 'A ', 'I ', "I'", 'Je ', 'Ce ', 'Voici', 'Dore', 'Murakaza', 'Karibu', 'Bienvenue', 'By the end', 'Practice these phrases', 'Keep practicing')):
                output['unit_goals'].append(value)
            else:
                output['example_sentences'].append(value)
    footer_match = re.findall(r'footer:\s*"([^"]+)"', body)
    output['footer_lines'].extend(footer_match)
    output['files'].append(str(path))

for key in ['vocab_pairs', 'key_phrase_pairs', 'example_sentences', 'grammar_items', 'unit_goals', 'footer_lines']:
    output[key] = sorted(set(output[key]))

Path('en_to_rw_translation_terms.json').write_text(json.dumps(output, ensure_ascii=False, indent=2), encoding='utf-8')
print('files_scanned=', len(output['files']))
print('vocab_pairs=', len(output['vocab_pairs']))
print('example_sentences=', len(output['example_sentences']))
print('grammar_items=', len(output['grammar_items']))
print('unit_goals=', len(output['unit_goals']))
print('footer_lines=', len(output['footer_lines']))
