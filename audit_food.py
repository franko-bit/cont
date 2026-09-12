from pathlib import Path
import yaml
source = Path('content/EN-TO-RW/EN-TO-RW/level2/food.yaml')
def load(path):
    return yaml.safe_load(path.read_text(encoding='utf-8').replace("\\'", "'")) or {}
source_count = len(load(source).get('exercises', []))
codes = 'ES DE IT PT JA KO ZH AR RU HI TR NL SV GA EL HE PL NO DA FI CS ID UK SW VI ZU GD LA'.split()
print('source_food_exercises=', source_count)
for code in codes:
    path = Path('content') / f'{code}-TO-RW' / 'level2' / 'food.yaml'
    data = yaml.safe_load(path.read_text(encoding='utf-8')) or {}
    print(code, len(data.get('exercises', [])))
