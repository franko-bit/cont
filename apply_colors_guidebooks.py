from pathlib import Path
import re

files = [
    ('content/EN-TO-RW/level1/colors.yaml', 'Kinyarwanda', 'Murakaza neza muri iki gice! Muri iki gice muziga amagambo y\'amabara, imvugo yoroheje, n\'imigani yo gusobanura ibintu mu buryo bworoshye.', ['Umutuku', 'Ubururu', 'Icyatsi', 'Umuhondo', 'Umukara', 'Umweru', 'Orange'], 'Kinyarwanda'),
    ('content/EN-TO-SW/level1/colors.yaml', 'Kiswahili', 'Karibu kwenye somo hili! Katika somo hili utajifunza maneno ya rangi, sentensi rahisi, na mifano ya kutumia rangi katika maisha ya kila siku.', ['Nyekundu', 'Samawati', 'Kijani', 'Njano', 'Nyeusi', 'Nyeupe', 'Machungwa'], 'Kiswahili'),
    ('content/FR-TO-RW/level1/colors.yaml', 'Kinyarwanda', 'Murakaza neza muri iki gice! Muri iki gice muziga amagambo y\'amabara, imvugo yoroheje, kandi mugashobora gusobanura ibintu byoroheje.', ['Umutuku', 'Ubururu', 'Icyatsi', 'Umuhondo', 'Umukara', 'Umweru', 'Orange'], 'Kinyarwanda'),
    ('content/FR-TO-SW/level1/colors.yaml', 'Kiswahili', 'Karibu kwenye somo hili! Katika somo hili utajifunza maneno ya rangi, sentensi rahisi, na mifano ya kutaja rangi kwa usahihi.', ['Nyekundu', 'Samawati', 'Kijani', 'Njano', 'Nyeusi', 'Nyeupe', 'Machungwa'], 'Kiswahili'),
    ('content/RW-TO-EN/level1/colors.yaml', 'English', 'Welcome to this lesson! In this unit, you will learn the main color words, simple description patterns, and useful phrases for everyday objects.', ['red', 'blue', 'green', 'yellow', 'black', 'white', 'orange'], 'English'),
    ('content/RW-TO-FR/level1/colors.yaml', 'French', 'Bienvenue dans cette leçon ! Dans cette unité, vous apprendrez les mots de base pour les couleurs, des structures simples et des phrases utiles pour décrire votre environnement.', ['rouge', 'bleu', 'vert', 'jaune', 'noir', 'blanc', 'orange'], 'Français'),
    ('content/SW-TO-EN/level1/colors.yaml', 'English', 'Welcome to this lesson! In this unit, you will learn the main color words, simple description patterns, and useful phrases for everyday objects.', ['red', 'blue', 'green', 'yellow', 'black', 'white', 'orange'], 'English'),
    ('content/SW-TO-FR/level1/colors.yaml', 'French', 'Bienvenue dans cette leçon ! Dans cette unité, vous apprendrez les mots de base pour les couleurs, des structures simples et des phrases utiles pour décrire votre environnement.', ['rouge', 'bleu', 'vert', 'jaune', 'noir', 'blanc', 'orange'], 'Français'),
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
  Colors in {target_name}

  {intro}

  ## 🎨 COLOR VOCABULARY

  ### Core colors

  | English | {lang_label} |
  ||-|
  | Red | {vocab_words[0]} |
  | Blue | {vocab_words[1]} |
  | Green | {vocab_words[2]} |
  | Yellow | {vocab_words[3]} |
  | Black | {vocab_words[4]} |
  | White | {vocab_words[5]} |
  | Orange | {vocab_words[6]} |

  ## 🧠 GRAMMAR TIPS

  ### Colors as adjectives
  In {target_name}, colors are usually placed close to the noun they describe.

  Examples:
  - A red apple → an apple that is red.
  - A blue sky → a sky that is blue.
  - A yellow sun → a sun that is yellow.

  ### Simple description pattern
  A very common pattern is: Noun + is + color.

  Example:
  - The apple is red.
  - The sky is blue.
  - The grass is green.

  ## 🗣️ PRONUNCIATION TIPS

  - Say each color clearly and slowly.
  - Repeat the word several times to build confidence.
  - Listen for the rhythm of each phrase and try to copy it.

  ## 💬 KEY PHRASES

  | English | {lang_label} |
  ||-|
  | The apple is red | La pomme est rouge |
  | The sky is blue | Le ciel est bleu |
  | The grass is green | L’herbe est verte |
  | The sun is yellow | Le soleil est jaune |
  | The cat is black | Le chat est noir |
  | The cow is white | La vache est blanche |

  ## 🎯 UNIT GOAL

  By the end of this unit, you will be able to:

  - Recognize the main color words in {target_name}.
  - Describe simple objects using color words.
  - Build short descriptive sentences.
  - Understand and answer basic questions about color.

  ### Total XP Available: 500 XP

  ## 📝 EXAMPLE SENTENCES

  1. The apple is red.
  2. The sky is blue.
  3. The grass is green.
  4. The sun is yellow.
  5. The cat is black.
  6. The cow is white.
  7. The carrot is orange.

  ## 🔍 QUICK REFERENCE

  | Category | Examples |
  |-|-|
  | Basic colors | Red, blue, green, yellow, black, white, orange |
  | Everyday objects | apple, sky, grass, sun, cat, cow |
  | Useful pattern | Noun + is + color |

  {closer}
exercises:'''
    new_text, count = pattern.subn(block, text, count=1)
    if count != 1:
        raise SystemExit(f'guidebook block not replaced in {path}')
    path.write_text(new_text, encoding='utf-8')
    print(f'updated {path}')
