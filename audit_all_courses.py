from pathlib import Path
import yaml

source = Path('content/EN-TO-RW/EN-TO-RW')
codes = 'ES FR DE IT PT JA KO ZH AR RU HI TR NL SV GA EL HE PL NO DA FI CS ID UK SW VI ZU GD LA'.split()
source_files = sorted(source.rglob('*.yaml'))
mismatches = []

def load(path):
    return yaml.safe_load(path.read_text(encoding='utf-8').replace("\\'", "'")) or {}

for code in codes:
    output_files = sorted((Path('content') / f'{code}-TO-RW').rglob('*.yaml'))
    if len(output_files) != len(source_files):
        mismatches.append((code, 'file_count', len(output_files), len(source_files)))
    for source_file in source_files:
        output_file = Path('content') / f'{code}-TO-RW' / source_file.relative_to(source)
        if not output_file.exists():
            mismatches.append((code, str(source_file.relative_to(source)), 'MISSING'))
            continue
        source_data = load(source_file)
        output_data = yaml.safe_load(output_file.read_text(encoding='utf-8')) or {}
        source_ids = [item.get('id') for item in source_data.get('exercises', [])]
        output_ids = [item.get('id') for item in output_data.get('exercises', [])]
        if source_ids != output_ids:
            mismatches.append((code, str(source_file.relative_to(source)), len(source_ids), len(output_ids)))

food = load(source / 'level2' / 'food.yaml')
print('source_files=', len(source_files))
print('food_source_exercises=', len(food.get('exercises', [])))
print('mismatches=', len(mismatches))
for item in mismatches[:50]:
    print(item)
