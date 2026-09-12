from pathlib import Path
import re

lesson_map = {
    'content/EN-TO-SW/level1/numbers.yaml': ('English', 'Kiswahili', 'Kiswahili', 'Karibu kwenye somo hili! Katika somo hili utajifunza nambari za msingi, jinsi ya kuzitafsiri, na jinsi ya kuzitumia katika mifano ya kila siku.'),
    'content/EN-TO-RW/level1/numbers.yaml': ('English', 'Kinyarwanda', 'Kinyarwanda', 'Murakaza neza muri iki kigereranyo! Muri iki gice uziga imibare y\'ibanze, uburyo bwo kuvuga imibare, ndetse n\'ibisobanuro by\'iyo mibare mu buzima bwa buri munsi.'),
    'content/FR-TO-SW/level1/numbers.yaml': ('Français', 'Kiswahili', 'Kiswahili', 'Karibu kwenye somo hili! Katika somo hili utajifunza nambari za msingi kwa Kiswahili, na matumizi yao katika mazungumzo rahisi.'),
    'content/FR-TO-RW/level1/numbers.yaml': ('Français', 'Kinyarwanda', 'Kinyarwanda', 'Murakaza neza muri iki kigereranyo! Muri iki gice uziga imibare y\'ibanze mu Kinyarwanda, uko wabisoma, n\'uburyo wabikoresha mu buzima bwa buri munsi.'),
    'content/RW-TO-EN/level1/numbers.yaml': ('Kinyarwanda', 'English', 'English', 'Welcome to this lesson! In this unit, you will learn basic number words, how to form larger numbers, and how to use them in everyday phrases.'),
    'content/SW-TO-EN/level1/numbers.yaml': ('Kiswahili', 'English', 'English', 'Welcome to this lesson! In this unit, you will learn Kiswahili number words, how to say tens and hundreds, and how to use numbers in simple sentences.'),
    'content/RW-TO-FR/level1/numbers.yaml': ('Kinyarwanda', 'Français', 'Français', 'Bienvenue dans cette leçon ! Dans cette unité, vous apprendrez les nombres de base en Kinyarwanda et comment les utiliser dans des phrases courantes.'),
    'content/SW-TO-FR/level1/numbers.yaml': ('Kiswahili', 'Français', 'Français', 'Bienvenue dans cette leçon ! Dans cette unité, vous apprendrez les nombres de base en Kiswahili et comment les utiliser dans des phrases pratiques.'),
}

vocabularies = {
    'Kinyarwanda': '''
  | 0 | Zero | Zero |
  | 1 | One | Rimwe |
  | 2 | Two | Kabiri |
  | 3 | Three | Gatatu |
  | 4 | Four | Kane |
  | 5 | Five | Gatanu |
  | 6 | Six | Gatandatu |
  | 7 | Seven | Karindwi |
  | 8 | Eight | Umunani |
  | 9 | Nine | Icyenda |
  | 10 | Ten | Icumi |
''',
    'Français': '''
  | 0 | Zero | Zéro |
  | 1 | One | Un |
  | 2 | Two | Deux |
  | 3 | Three | Trois |
  | 4 | Four | Quatre |
  | 5 | Five | Cinq |
  | 6 | Six | Six |
  | 7 | Seven | Sept |
  | 8 | Eight | Huit |
  | 9 | Nine | Neuf |
  | 10 | Ten | Dix |
''',
    'English': '''
  | 0 | Zero | Zero |
  | 1 | One | One |
  | 2 | Two | Two |
  | 3 | Three | Three |
  | 4 | Four | Four |
  | 5 | Five | Five |
  | 6 | Six | Six |
  | 7 | Seven | Seven |
  | 8 | Eight | Eight |
  | 9 | Nine | Nine |
  | 10 | Ten | Ten |
''',
    'Kiswahili': '''
  | 0 | Zero | Sifuri |
  | 1 | One | Moja |
  | 2 | Two | Mbili |
  | 3 | Three | Tatu |
  | 4 | Four | Nne |
  | 5 | Five | Tano |
  | 6 | Six | Sita |
  | 7 | Seven | Saba |
  | 8 | Eight | Nane |
  | 9 | Nine | Tisa |
  | 10 | Ten | Kumi |
''',
}

pattern = re.compile(r'guidebook: \|.*?\n(?=exercises:)', re.S)

for path_str, (source_label, target_label, target_name, intro) in lesson_map.items():
    path = Path(path_str)
    if not path.exists():
        raise FileNotFoundError(f"Missing file: {path}")

    text = path.read_text(encoding='utf-8')
    vocabulary = vocabularies.get(target_label, vocabularies['Kiswahili'])

    key_phrases = '''
  | How much is this? | ? |
  | I have three apples | ? |
  | Twenty is a round number | ? |
  | One hundred is a hundred | ? |
'''

    block = f'''guidebook: |
  # Guidebook

  ## SECTION 1, UNIT 1
  Numbers in {target_name}

  {intro}

  ## NUMBER VOCABULARY

  Learn these core numbers by reading them, saying them aloud, and practicing the pattern.

  | Number | {source_label} | {target_label} |
  |---|---|---|
{vocabulary}
  ## GRAMMAR TIPS

  ### Number patterns
  Notice how larger numbers are built from smaller parts.
  - {target_label} often uses a base word plus a connector word for 11–19.
  - Tens are formed with a root and a repeated pattern for 20, 30, 40, etc.
  - Hundreds and thousands combine the word for 100 or 1,000 with the core number.

  ### Useful counting structure
  Use the pattern: number + noun.

  Examples:
  - One apple
  - Three books
  - Twenty students

  ## KEY PHRASES

  | English | {target_label} |
  |---|---|
{key_phrases}
  ## UNIT GOAL

  By the end of this unit, you will be able to:

  - Recognize and say the numbers from 0 to 10 in {target_name}.
  - Understand how tens and hundreds are formed.
  - Use numbers in short phrases and counting sentences.
  - Answer simple questions about quantity.

  ### Total XP Available: 500 XP

  ## EXAMPLE SENTENCES

  1. One apple → ?
  2. Three books → ?
  3. Twenty students → ?
  4. One hundred coins → ?
  5. I have five apples → ?

  ## QUICK REFERENCE

  | Category | Examples |
  |---|---|
  | Basic numbers | 0, 1, 2, 3, 4, 5 |
  | Tens | 10, 20, 30, 40, 50 |
  | Hundreds | 100, 200, 300 |
  | Counting pattern | number + noun |

  Good luck! Practice the number exercises, then try the matching and sentence activities to reinforce your learning. 🔥
'''

    new_text = pattern.sub(block, text, 1)
    if new_text == text:
        new_text = block + text

    path.write_text(new_text, encoding='utf-8')
    print(f'updated {path}')
