from pathlib import Path
import re

files = [
    ('content/EN-TO-RW/level1/family.yaml', 'Kinyarwanda', 'Murakaza neza muri iki gice! Muri iki gice muziga amagambo y\'umuryango, imvugo yoroheje, n\'imigani yo gusobanura abantu bo mu muryango mu buryo bworoshye.', ['Mama', 'Data', 'Musaza', 'Mushiki', 'Umwana', 'Nyogokuru', 'Sogokuru', 'Umugabo', 'Umugore'], 'Kinyarwanda'),
    ('content/EN-TO-SW/level1/family.yaml', 'Kiswahili', 'Karibu kwenye somo hili! Katika somo hili utajifunza majina ya wanafamilia, maneno ya uhusiano, na mifano ya kuzungumza kuhusu familia yako kwa ujasiri.', ['Mama', 'Baba', 'Kaka', 'Dada', 'Mtoto', 'Bibi', 'Babu', 'Mume', 'Mke'], 'Kiswahili'),
    ('content/FR-TO-RW/level1/family.yaml', 'Kinyarwanda', 'Murakaza neza muri iki gice! Muri iki gice muziga amagambo y\'umuryango, imvugo yoroheje, kandi mugashobora gusobanura abantu bo mu muryango neza.', ['Mama', 'Data', 'Musaza', 'Mushiki', 'Umwana', 'Nyogokuru', 'Sogokuru', 'Umugabo', 'Umugore'], 'Kinyarwanda'),
    ('content/FR-TO-SW/level1/family.yaml', 'Kiswahili', 'Karibu kwenye somo hili! Katika somo hili utajifunza majina ya wanafamilia, maneno ya uhusiano, na mifano ya kuzungumza kuhusu familia yako kwa ujasiri.', ['Mama', 'Baba', 'Kaka', 'Dada', 'Mtoto', 'Bibi', 'Babu', 'Mume', 'Mke'], 'Kiswahili'),
    ('content/RW-TO-EN/level1/family.yaml', 'English', 'Welcome to this lesson! In this unit, you will learn the main family words, simple description patterns, and useful phrases for talking about your family.', ['mother', 'father', 'brother', 'sister', 'child', 'grandmother', 'grandfather', 'husband', 'wife'], 'English'),
    ('content/RW-TO-FR/level1/family.yaml', 'French', 'Bienvenue dans cette leçon ! Dans cette unité, vous apprendrez les mots de base pour la famille, des structures simples et des phrases utiles pour parler de votre entourage.', ['mère', 'père', 'frère', 'sœur', 'enfant', 'grand-mère', 'grand-père', 'mari', 'femme'], 'Français'),
    ('content/SW-TO-EN/level1/family.yaml', 'English', 'Welcome to this lesson! In this unit, you will learn the main family words, simple description patterns, and useful phrases for talking about your family.', ['mother', 'father', 'brother', 'sister', 'child', 'grandmother', 'grandfather', 'husband', 'wife'], 'English'),
    ('content/SW-TO-FR/level1/family.yaml', 'French', 'Bienvenue dans cette leçon ! Dans cette unité, vous apprendrez les mots de base pour la famille, des structures simples et des phrases utiles pour parler de votre entourage.', ['mère', 'père', 'frère', 'sœur', 'enfant', 'grand-mère', 'grand-père', 'mari', 'femme'], 'Français'),
]

for path_str, target_name, intro, vocab_words, lang_label in files:
    path = Path(path_str)
    text = path.read_text(encoding='utf-8')
    pattern = re.compile(r'guidebook: \|.*?\nexercises:', re.S)
    closer = (
        'Karibu kwenye somo hili! Endelea kwa mazoezi ya kutafsiri, kusikiliza, kuzungumza na kuoanisha ili kuimarisha ujifunzaji wako. 🔥'
        if target_name == 'Kiswahili' else
        'Murakaza neza! Tangira ku masomo ya kwiga, kuganiriza, no kwitoza kuvuga kugira ngo ukomeze ubuhanga bwawe. 🔥'
        if target_name == 'Kinyarwanda' else
        'Bienvenue ! Continuez avec les exercices de traduction, d’écoute, de parole et d’association pour renforcer votre apprentissage. 🔥'
        if target_name == 'French' else
        'Good luck! Start with the translation exercises, then try the listening, speaking, and matching activities to reinforce your learning. 🔥'
    )
    block = f'''guidebook: |
  #  Guidebook

  ## SECTION 1, UNIT 1
  Family in {target_name}

  {intro}

  ## 👨‍👩‍👧‍👦 FAMILY VOCABULARY

  ### Core family words

  | English | {lang_label} |
  ||-|
  | Mother | {vocab_words[0]} |
  | Father | {vocab_words[1]} |
  | Brother | {vocab_words[2]} |
  | Sister | {vocab_words[3]} |
  | Child | {vocab_words[4]} |
  | Grandmother | {vocab_words[5]} |
  | Grandfather | {vocab_words[6]} |
  | Husband | {vocab_words[7]} |
  | Wife | {vocab_words[8]} |

  ## 🧠 GRAMMAR TIPS

  ### Talking about family
  In {target_name}, you can introduce family members with simple descriptive sentences.

  Examples:
  - My mother is kind.
  - My father is helpful.
  - My sister is happy.

  ### Simple sentence pattern
  A useful pattern is: My + family member + is + adjective.

  Example:
  - My mother is kind.
  - My father is at home.
  - My brother is tall.

  ## 🗣️ PRONUNCIATION TIPS

  - Say each family word slowly and clearly.
  - Pay attention to the rhythm of the phrase.
  - Repeat the words aloud until they feel natural.

  ## 💬 KEY PHRASES

  | English | {lang_label} |
  ||-|
  | My mother is kind | Ma mère est gentille |
  | My father is at home | Mon père est à la maison |
  | My brother is tall | Mon frère est grand |
  | My sister is happy | Ma sœur est heureuse |
  | I love my family | J’aime ma famille |
  | This is my child | C’est mon enfant |

  ## 🎯 UNIT GOAL

  By the end of this unit, you will be able to:

  - Recognize common family words in {target_name}.
  - Describe family relationships clearly.
  - Build simple sentences about family members.
  - Understand and answer basic questions about family.

  ### Total XP Available: 500 XP

  ## 📝 EXAMPLE SENTENCES

  1. My mother is kind.
  2. My father is at home.
  3. My brother is tall.
  4. My sister is happy.
  5. This is my child.
  6. My grandmother is wise.
  7. My grandfather is helpful.

  ## 🔍 QUICK REFERENCE

  | Category | Examples |
  |-|-|
  | Family members | mother, father, brother, sister, child |
  | Extended family | grandmother, grandfather, husband, wife |
  | Useful pattern | My + family member + is + adjective |

  {closer}
exercises:'''
    new_text, count = pattern.subn(block, text, count=1)
    if count != 1:
        raise SystemExit(f'guidebook block not replaced in {path}')
    path.write_text(new_text, encoding='utf-8')
    print(f'updated {path}')
