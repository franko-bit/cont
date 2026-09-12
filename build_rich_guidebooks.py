from pathlib import Path
import re

root = Path('content')
target_dirs = ['EN-TO-SW', 'FR-TO-RW', 'FR-TO-SW', 'RW-TO-EN', 'RW-TO-FR', 'SW-TO-EN', 'SW-TO-FR']

configs = {
    'EN-TO-SW': {
        'title': 'Mwongozo',
        'heading': 'Muhtasari',
        'vocab': 'Msamiati wa mada',
        'grammar': 'Dokezo la sarufi',
        'phrases': 'Vifungu muhimu',
        'pron': 'Dokezo la matamshi',
        'goal': 'Lengo la somo',
        'ref': 'Marejeo ya haraka',
        'intro': 'Karibu kwenye somo hili! Katika somo hili utajifunza maneno muhimu, sentensi rahisi, na mifano ya matumizi ya mada hii kwa ujasiri.',
        'lang': 'Kiswahili',
        'example_intro': 'Hapa kuna mifano ya sentensi unayoweza kujifunza:',
    },
    'FR-TO-RW': {
        'title': 'Guide',
        'heading': 'Inshamake',
        'vocab': 'Amagambo y’ingenzi',
        'grammar': 'Inyongera ku ivangura',
        'phrases': 'Imvugo y’ingenzi',
        'pron': 'Inyongera ku kuvuga',
        'goal': 'Intego y’igisomo',
        'ref': 'Reba vuba',
        'intro': 'Murakaza neza muri iki kigereranyo! Muri iki gice muziga amagambo, imvugo, n’imiterere ya interuro ikenewe yo kuvuga ku kibazo cyanyu neza.',
        'lang': 'Kinyarwanda',
        'example_intro': 'Dore interuro zimwe mushobora kwigira:',
    },
    'FR-TO-SW': {
        'title': 'Guide',
        'heading': 'Muhtasari',
        'vocab': 'Msamiati wa mada',
        'grammar': 'Dokezo la sarufi',
        'phrases': 'Vifungu muhimu',
        'pron': 'Dokezo la matamshi',
        'goal': 'Lengo la somo',
        'ref': 'Marejeo ya haraka',
        'intro': 'Karibu kwenye somo hili! Katika somo hili utajifunza maneno muhimu, sentensi rahisi, na mifano ya matumizi ya mada hii kwa ujasiri.',
        'lang': 'Kiswahili',
        'example_intro': 'Hapa kuna mifano ya sentensi unayoweza kujifunza:',
    },
    'RW-TO-EN': {
        'title': 'Guidebook',
        'heading': 'Overview',
        'vocab': 'Vocabulary',
        'grammar': 'Grammar tips',
        'phrases': 'Key phrases',
        'pron': 'Pronunciation tips',
        'goal': 'Unit goal',
        'ref': 'Quick reference',
        'intro': 'Welcome to this lesson! In this unit, you will learn the core vocabulary, simple sentence patterns, and useful phrases for this topic.',
        'lang': 'English',
        'example_intro': 'Here are example sentences you will practise:',
    },
    'RW-TO-FR': {
        'title': 'Guide',
        'heading': 'Vue d’ensemble',
        'vocab': 'Vocabulaire',
        'grammar': 'Conseils de grammaire',
        'phrases': 'Phrases clés',
        'pron': 'Conseils de prononciation',
        'goal': 'Objectif de l’unité',
        'ref': 'Référence rapide',
        'intro': 'Bienvenue dans cette leçon ! Dans cette unité, vous apprendrez le vocabulaire essentiel, les structures de phrases simples et les expressions utiles pour ce sujet.',
        'lang': 'Français',
        'example_intro': 'Voici des exemples de phrases que vous allez pratiquer :',
    },
    'SW-TO-EN': {
        'title': 'Guidebook',
        'heading': 'Overview',
        'vocab': 'Vocabulary',
        'grammar': 'Grammar tips',
        'phrases': 'Key phrases',
        'pron': 'Pronunciation tips',
        'goal': 'Unit goal',
        'ref': 'Quick reference',
        'intro': 'Welcome to this lesson! In this unit, you will learn the core vocabulary, simple sentence patterns, and useful phrases for this topic.',
        'lang': 'English',
        'example_intro': 'Here are example sentences you will practise:',
    },
    'SW-TO-FR': {
        'title': 'Guide',
        'heading': 'Vue d’ensemble',
        'vocab': 'Vocabulaire',
        'grammar': 'Conseils de grammaire',
        'phrases': 'Phrases clés',
        'pron': 'Conseils de prononciation',
        'goal': 'Objectif de l’unité',
        'ref': 'Référence rapide',
        'intro': 'Bienvenue dans cette leçon ! Dans cette unité, vous apprendrez le vocabulaire essentiel, les structures de phrases simples et les expressions utiles pour ce sujet.',
        'lang': 'Français',
        'example_intro': 'Voici des exemples de phrases que vous allez pratiquer :',
    },
}

lesson_samples = {
    'animals': {
        'EN-TO-SW': [
            ['Ngombe', 'cow'],
            ['Mbwa', 'dog'],
            ['Ndege', 'bird'],
            ['Simba', 'lion'],
        ],
        'FR-TO-SW': [
            ['Ngombe', 'cow'],
            ['Mbwa', 'dog'],
            ['Ndege', 'bird'],
            ['Simba', 'lion'],
        ],
        'RW-TO-EN': [
            ['Cow', 'Inka'],
            ['Dog', 'Imbwa'],
            ['Bird', 'Inyoni'],
            ['Lion', 'Intare'],
        ],
        'RW-TO-FR': [
            ['Vache', 'Inka'],
            ['Chien', 'Imbwa'],
            ['Oiseau', 'Inyoni'],
            ['Lion', 'Intare'],
        ],
        'SW-TO-EN': [
            ['Cow', 'Ngombe'],
            ['Dog', 'Mbwa'],
            ['Bird', 'Ndege'],
            ['Lion', 'Simba'],
        ],
        'SW-TO-FR': [
            ['Vache', 'Ngombe'],
            ['Chien', 'Mbwa'],
            ['Oiseau', 'Ndege'],
            ['Lion', 'Simba'],
        ],
        'FR-TO-RW': [
            ['Inka', 'cow'],
            ['Imbwa', 'dog'],
            ['Inyoni', 'bird'],
            ['Intare', 'lion'],
        ],
    },
    'colors': {
        'EN-TO-SW': [['Nyekundu', 'red'], ['Bluu', 'blue'], ['Kijani', 'green'], ['Njano', 'yellow']],
        'FR-TO-SW': [['Nyekundu', 'rouge'], ['Bluu', 'bleu'], ['Kijani', 'vert'], ['Njano', 'jaune']],
        'RW-TO-EN': [['Red', 'Nyekundu'], ['Blue', 'Buluu'], ['Green', 'Kijani'], ['Yellow', 'Manjano']],
        'RW-TO-FR': [['Rouge', 'Nyekundu'], ['Bleu', 'Buluu'], ['Vert', 'Kijani'], ['Jaune', 'Manjano']],
        'SW-TO-EN': [['Red', 'Nyekundu'], ['Blue', 'Buluu'], ['Green', 'Kijani'], ['Yellow', 'Manjano']],
        'SW-TO-FR': [['Rouge', 'Nyekundu'], ['Bleu', 'Buluu'], ['Vert', 'Kijani'], ['Jaune', 'Manjano']],
        'FR-TO-RW': [['Umutuku', 'red'], ['Ubururu', 'blue'], ['Icyatsi', 'green'], ['Umuhondo', 'yellow']],
    },
    'family': {
        'EN-TO-SW': [['Mama', 'mother'], ['Baba', 'father'], ['Mtoto', 'child'], ['Dada', 'sister']],
        'FR-TO-SW': [['Mama', 'mother'], ['Baba', 'father'], ['Mtoto', 'child'], ['Dada', 'sister']],
        'RW-TO-EN': [['Mother', 'Mama'], ['Father', 'Baba'], ['Child', 'Mtoto'], ['Sister', 'Dada']],
        'RW-TO-FR': [['Mère', 'Mama'], ['Père', 'Baba'], ['Enfant', 'Mtoto'], ['Sœur', 'Dada']],
        'SW-TO-EN': [['Mother', 'Mama'], ['Father', 'Baba'], ['Child', 'Mtoto'], ['Sister', 'Dada']],
        'SW-TO-FR': [['Mère', 'Mama'], ['Père', 'Baba'], ['Enfant', 'Mtoto'], ['Sœur', 'Dada']],
        'FR-TO-RW': [['Mama', 'mother'], ['Baba', 'father'], ['Umwana', 'child'], ['Mushiki', 'sister']],
    },
    'house': {
        'EN-TO-SW': [['Nyumba', 'house'], ['Dirisha', 'window'], ['Mlango', 'door'], ['Paa', 'roof']],
        'FR-TO-SW': [['Nyumba', 'maison'], ['Dirisha', 'fenêtre'], ['Mlango', 'porte'], ['Paa', 'toit']],
        'RW-TO-EN': [['House', 'Nyumba'], ['Window', 'Dirisha'], ['Door', 'Mlango'], ['Roof', 'Paa']],
        'RW-TO-FR': [['Maison', 'Nyumba'], ['Fenêtre', 'Dirisha'], ['Porte', 'Mlango'], ['Toit', 'Paa']],
        'SW-TO-EN': [['House', 'Nyumba'], ['Window', 'Dirisha'], ['Door', 'Mlango'], ['Roof', 'Paa']],
        'SW-TO-FR': [['Maison', 'Nyumba'], ['Fenêtre', 'Dirisha'], ['Porte', 'Mlango'], ['Toit', 'Paa']],
        'FR-TO-RW': [['Inzu', 'house'], ['Idirishya', 'window'], ['Umuryango', 'door'], ['Igitanda', 'roof']],
    },
}


def build_block(path, cfg, folder):
    lesson_name = path.stem.replace('-', ' ').title()
    lesson_key = path.stem.lower()
    level = path.parts[-2]
    unit = int(level.replace('level', ''))

    sample_rows = lesson_samples.get(lesson_key, {}).get(folder, [
        ['Word 1', 'Example'],
        ['Word 2', 'Example'],
        ['Word 3', 'Example'],
        ['Word 4', 'Example'],
    ])

    rows_md = '\n'.join([f"  | {left} | {right} |" for left, right in sample_rows])
    phrases_rows = [
        [f"A {lesson_name.lower()} example", f"Mfano wa {lesson_name.lower()}"],
        [f"I can talk about {lesson_name.lower()}", f"Naweza kuzungumza kuhusu {lesson_name.lower()}"],
        [f"I understand the lesson", f"Naelewa somo"],
    ]

    if folder in {'EN-TO-SW', 'FR-TO-SW'}:
        phrases_rows = [
            [f"A {lesson_name.lower()} example", f"Mfano wa {lesson_name.lower()}"],
            [f"I can talk about {lesson_name.lower()}", f"Naweza kuzungumza kuhusu {lesson_name.lower()}"],
            [f"I understand the lesson", f"Naelewa somo"],
        ]
    elif folder in {'RW-TO-EN', 'SW-TO-EN'}:
        phrases_rows = [
            [f"A {lesson_name.lower()} example", f"An example of {lesson_name.lower()}"],
            [f"I can talk about {lesson_name.lower()}", f"I can talk about {lesson_name.lower()}"],
            [f"I understand the lesson", f"I understand the lesson"],
        ]
    else:
        phrases_rows = [
            [f"Un exemple de {lesson_name.lower()}", f"An example of {lesson_name.lower()}"],
            [f"Je peux parler de {lesson_name.lower()}", f"I can talk about {lesson_name.lower()}"],
            [f"J’ai compris la leçon", f"I understand the lesson"],
        ]

    phrases_md = '\n'.join([f"  | {left} | {right} |" for left, right in phrases_rows])

    example_lines = [
        f"1. I am learning this lesson about {lesson_name}. → I am learning this lesson about {lesson_name}.",
        f"2. This topic is useful. → This topic is useful.",
        f"3. I can talk about it with confidence. → I can talk about it with confidence.",
    ]

    if folder in {'EN-TO-SW', 'FR-TO-SW'}:
        example_lines = [
            f"1. Mimi najifunza somo kuhusu {lesson_name}. → I am learning about {lesson_name}.",
            f"2. Mada hii ni muhimu. → This topic is useful.",
            f"3. Naweza kuzungumza kuhusu hiyo kwa ujasiri. → I can talk about it with confidence.",
        ]
    elif folder in {'RW-TO-FR', 'SW-TO-FR'}:
        example_lines = [
            f"1. J’apprends cette leçon sur {lesson_name}. → I am learning about {lesson_name}.",
            f"2. Ce sujet est utile. → This topic is useful.",
            f"3. Je peux en parler avec confiance. → I can talk about it with confidence.",
        ]

    return f'''guidebook: |
  #  {cfg['title']} {lesson_name}

  ## SECTION 1, UNIT {unit}
  {lesson_name} in {cfg['lang']}

  {cfg['intro']}

  ## {cfg['heading']}

  Welcome to your first lesson on {lesson_name}. In this unit, you will learn the core vocabulary, simple sentence patterns, and useful phrases for the topic. By the end, you will be able to understand the lesson, speak about it, and answer basic questions.

  ## {cfg['vocab']}

  ### Core words

  | English | {cfg['lang']} |
  |---|---|
{rows_md}

  ## {cfg['grammar']}

  ### Word order
  In {cfg['lang']}, simple sentences often follow a clear pattern: Subject + Verb + Object.

  Examples:
  - Learn the main words first.
  - Build short sentences with the vocabulary from the lesson.
  - Repeat the example sentences out loud.

  ## {cfg['phrases']}

  | English | {cfg['lang']} |
  |---|---|
{phrases_md}

  ## {cfg['pron']}

  - Say each word slowly and clearly.
  - Repeat the pronunciation several times.
  - Listen for the rhythm of each phrase.

  ## {cfg['goal']}

  - Learn the main vocabulary for this lesson.
  - Use simple words and phrases in short sentences.
  - Understand and respond to basic questions about the topic.

  ## EXAMPLE SENTENCES

  """\n  """.join(example_lines)

  ## {cfg['ref']}

  - Review the key words every day.
  - Practise the phrases out loud.
  - Use the example sentences as a guide.
'''


for folder in target_dirs:
    folder_path = root / folder
    cfg = configs[folder]
    for path in sorted(folder_path.rglob('*.yaml')):
        text = path.read_text(encoding='utf-8')
        if 'guidebook: |' not in text:
            continue
        block = build_block(path, cfg, folder)
        pattern = re.compile(r'guidebook:\s*\|.*?(?=^exercises:|\Z)', re.S | re.M)
        text, _ = pattern.subn(block, text, count=1)
        path.write_text(text, encoding='utf-8')
        print(f'updated {path}')
