from pathlib import Path

paths = [
    Path(r'c:\xampp\htdocs\language-platform\content\EN-TO-RW\level2\daily-routines.yaml'),
    Path(r'c:\xampp\htdocs\language-platform\content\EN-TO-RW\level2\food.yaml'),
]

for path in paths:
    text = path.read_text(encoding='utf-8')
    lines = text.splitlines(True)
    start = next((i for i, line in enumerate(lines) if line.startswith('  guidebook_data:')), None)
    end = next((i for i, line in enumerate(lines) if line.startswith('  guidebook: |')), None)
    if start is None or end is None or end <= start:
        raise RuntimeError(f'Could not find guidebook_data or guidebook markers in {path}')
    for i in range(start + 1, end):
        if lines[i].strip() == '':
            continue
        if not lines[i].startswith('    '):
            lines[i] = '  ' + lines[i]
    path.write_text(''.join(lines), encoding='utf-8')
    print(f'Fixed indentation in {path}')
