from pathlib import Path
import re

root = Path('content')
target_dirs = ['EN-TO-SW', 'FR-TO-RW', 'FR-TO-SW', 'RW-TO-EN', 'RW-TO-FR', 'SW-TO-EN', 'SW-TO-FR']

configs = {
    'EN-TO-SW': {
        'title': 'Guidebook',
        'target_lang': 'Kiswahili',
        'intro': 'Karibu kwenye somo hili! Katika somo hili utajifunza maneno muhimu, sentensi rahisi, na mifano ya matumizi ya mada hii kwa ujasiri.',
        'overview': 'Welcome to your first lesson on {lesson_name}. In this unit, you will learn the names of common words, basic actions, and useful phrases for this topic. By the end, you will be able to talk about the topic, answer simple questions, and use the new vocabulary with confidence.',
        'vocab_heading': 'VOCABULARY',
        'grammar_heading': 'GRAMMAR TIPS',
        'phrases_heading': 'KEY PHRASES',
        'pron_heading': 'PRONUNCIATION TIPS',
        'goal_heading': 'UNIT GOAL',
        'ref_heading': 'QUICK REFERENCE',
        'example_intro': 'Hapa kuna mifano ya sentensi unayoweza kujifunza:',
        'example_lang': 'Kiswahili',
        'column_left': 'English',
        'column_right': 'Kiswahili',
        'example_prefix': 'Mimi najifunza somo kuhusu',
        'example_topic': 'Mada hii ni muhimu.',
        'example_confidence': 'Naweza kuzungumza kuhusu hiyo kwa ujasiri.',
    },
    'FR-TO-RW': {
        'title': 'Guidebook',
        'target_lang': 'Kinyarwanda',
        'intro': 'Murakaza neza muri iki kigereranyo! Muri iki gice muziga amagambo, imvugo, n’imiterere ya interuro ikenewe yo kuvuga ku kibazo cyanyu neza.',
        'overview': 'Welcome to your first lesson on {lesson_name}. In this unit, you will learn the names of common words, basic actions, and useful phrases for this topic. By the end, you will be able to talk about the topic, answer simple questions, and use the new vocabulary with confidence.',
        'vocab_heading': 'VOCABULARY',
        'grammar_heading': 'GRAMMAR TIPS',
        'phrases_heading': 'KEY PHRASES',
        'pron_heading': 'PRONUNCIATION TIPS',
        'goal_heading': 'UNIT GOAL',
        'ref_heading': 'QUICK REFERENCE',
        'example_intro': 'Dore interuro zimwe mushobora kwigira:',
        'example_lang': 'Kinyarwanda',
        'column_left': 'English',
        'column_right': 'Kinyarwanda',
        'example_prefix': 'Ndiga icyigisho cyo',
        'example_topic': 'Iki kibazo ni ngombwa.',
        'example_confidence': 'Nshobora kuvuga ku kibazo cyanjye neza.',
    },
    'FR-TO-SW': {
        'title': 'Guidebook',
        'target_lang': 'Kiswahili',
        'intro': 'Karibu kwenye somo hili! Katika somo hili utajifunza maneno muhimu, sentensi rahisi, na mifano ya matumizi ya mada hii kwa ujasiri.',
        'overview': 'Welcome to your first lesson on {lesson_name}. In this unit, you will learn the names of common words, basic actions, and useful phrases for this topic. By the end, you will be able to talk about the topic, answer simple questions, and use the new vocabulary with confidence.',
        'vocab_heading': 'VOCABULARY',
        'grammar_heading': 'GRAMMAR TIPS',
        'phrases_heading': 'KEY PHRASES',
        'pron_heading': 'PRONUNCIATION TIPS',
        'goal_heading': 'UNIT GOAL',
        'ref_heading': 'QUICK REFERENCE',
        'example_intro': 'Hapa kuna mifano ya sentensi unayoweza kujifunza:',
        'example_lang': 'Kiswahili',
        'column_left': 'English',
        'column_right': 'Kiswahili',
        'example_prefix': 'Mimi najifunza somo kuhusu',
        'example_topic': 'Mada hii ni muhimu.',
        'example_confidence': 'Naweza kuzungumza kuhusu hiyo kwa ujasiri.',
    },
    'RW-TO-EN': {
        'title': 'Guidebook',
        'target_lang': 'English',
        'intro': 'Welcome to this lesson! In this unit, you will learn the core vocabulary, simple sentence patterns, and useful phrases for this topic.',
        'overview': 'Welcome to your first lesson on {lesson_name}. In this unit, you will learn the names of common words, basic actions, and useful phrases for this topic. By the end, you will be able to talk about the topic, answer simple questions, and use the new vocabulary with confidence.',
        'vocab_heading': 'VOCABULARY',
        'grammar_heading': 'GRAMMAR TIPS',
        'phrases_heading': 'KEY PHRASES',
        'pron_heading': 'PRONUNCIATION TIPS',
        'goal_heading': 'UNIT GOAL',
        'ref_heading': 'QUICK REFERENCE',
        'example_intro': 'Here are example sentences you will practise:',
        'example_lang': 'English',
        'column_left': 'English',
        'column_right': 'English',
        'example_prefix': 'I am learning about',
        'example_topic': 'This topic is useful.',
        'example_confidence': 'I can talk about it with confidence.',
    },
    'RW-TO-FR': {
        'title': 'Guide',
        'target_lang': 'Français',
        'intro': 'Bienvenue dans cette leçon ! Dans cette unité, vous apprendrez le vocabulaire essentiel, les structures de phrases simples et les expressions utiles pour ce sujet.',
        'overview': 'Bienvenue dans votre première leçon sur {lesson_name}. Dans cette unité, vous apprendrez les mots courants, les actions de base et les expressions utiles pour ce sujet. À la fin, vous pourrez parler de ce sujet, répondre à des questions simples et utiliser le nouveau vocabulaire avec confiance.',
        'vocab_heading': 'VOCABULAIRE',
        'grammar_heading': 'CONSEILS DE GRAMMAIRE',
        'phrases_heading': 'PHRASES CLÉS',
        'pron_heading': 'CONSEILS DE PRONONCIATION',
        'goal_heading': 'OBJECTIF DE L’UNITÉ',
        'ref_heading': 'RÉFÉRENCE RAPIDE',
        'example_intro': 'Voici des exemples de phrases que vous allez pratiquer :',
        'example_lang': 'Français',
        'column_left': 'English',
        'column_right': 'Français',
        'example_prefix': 'J’apprends la leçon sur',
        'example_topic': 'Ce sujet est utile.',
        'example_confidence': 'Je peux en parler avec confiance.',
    },
    'SW-TO-EN': {
        'title': 'Guidebook',
        'target_lang': 'English',
        'intro': 'Welcome to this lesson! In this unit, you will learn the core vocabulary, simple sentence patterns, and useful phrases for this topic.',
        'overview': 'Welcome to your first lesson on {lesson_name}. In this unit, you will learn the names of common words, basic actions, and useful phrases for this topic. By the end, you will be able to talk about the topic, answer simple questions, and use the new vocabulary with confidence.',
        'vocab_heading': 'VOCABULARY',
        'grammar_heading': 'GRAMMAR TIPS',
        'phrases_heading': 'KEY PHRASES',
        'pron_heading': 'PRONUNCIATION TIPS',
        'goal_heading': 'UNIT GOAL',
        'ref_heading': 'QUICK REFERENCE',
        'example_intro': 'Here are example sentences you will practise:',
        'example_lang': 'English',
        'column_left': 'English',
        'column_right': 'English',
        'example_prefix': 'I am learning about',
        'example_topic': 'This topic is useful.',
        'example_confidence': 'I can talk about it with confidence.',
    },
    'SW-TO-FR': {
        'title': 'Guide',
        'target_lang': 'Français',
        'intro': 'Bienvenue dans cette leçon ! Dans cette unité, vous apprendrez le vocabulaire essentiel, les structures de phrases simples et les expressions utiles pour ce sujet.',
        'overview': 'Bienvenue dans votre première leçon sur {lesson_name}. Dans cette unité, vous apprendrez les mots courants, les actions de base et les expressions utiles pour ce sujet. À la fin, vous pourrez parler de ce sujet, répondre à des questions simples et utiliser le nouveau vocabulaire avec confiance.',
        'vocab_heading': 'VOCABULAIRE',
        'grammar_heading': 'CONSEILS DE GRAMMAIRE',
        'phrases_heading': 'PHRASES CLÉS',
        'pron_heading': 'CONSEILS DE PRONONCIATION',
        'goal_heading': 'OBJECTIF DE L’UNITÉ',
        'ref_heading': 'RÉFÉRENCE RAPIDE',
        'example_intro': 'Voici des exemples de phrases que vous allez pratiquer :',
        'example_lang': 'Français',
        'column_left': 'English',
        'column_right': 'Français',
        'example_prefix': 'J’apprends la leçon sur',
        'example_topic': 'Ce sujet est utile.',
        'example_confidence': 'Je peux en parler avec confiance.',
    },
}

lesson_samples = {
    'animals': {
        'EN-TO-SW': [('Cow', 'Ngombe'), ('Dog', 'Mbwa'), ('Bird', 'Ndege'), ('Lion', 'Simba')],
        'FR-TO-RW': [('Cow', 'Inka'), ('Dog', 'Imbwa'), ('Bird', 'Inyoni'), ('Lion', 'Intare')],
        'FR-TO-SW': [('Cow', 'Ngombe'), ('Dog', 'Mbwa'), ('Bird', 'Ndege'), ('Lion', 'Simba')],
        'RW-TO-EN': [('Cow', 'Inka'), ('Dog', 'Imbwa'), ('Bird', 'Inyoni'), ('Lion', 'Intare')],
        'RW-TO-FR': [('Vache', 'Inka'), ('Chien', 'Imbwa'), ('Oiseau', 'Inyoni'), ('Lion', 'Intare')],
        'SW-TO-EN': [('Cow', 'Ngombe'), ('Dog', 'Mbwa'), ('Bird', 'Ndege'), ('Lion', 'Simba')],
        'SW-TO-FR': [('Vache', 'Ngombe'), ('Chien', 'Mbwa'), ('Oiseau', 'Ndege'), ('Lion', 'Simba')],
    },
    'colors': {
        'EN-TO-SW': [('Red', 'Nyekundu'), ('Blue', 'Buluu'), ('Green', 'Kijani'), ('Yellow', 'Manjano')],
        'FR-TO-RW': [('Red', 'Umutuku'), ('Blue', 'Ubururu'), ('Green', 'Icyatsi'), ('Yellow', 'Umuhondo')],
        'FR-TO-SW': [('Red', 'Nyekundu'), ('Blue', 'Buluu'), ('Green', 'Kijani'), ('Yellow', 'Manjano')],
        'RW-TO-EN': [('Red', 'Nyekundu'), ('Blue', 'Buluu'), ('Green', 'Kijani'), ('Yellow', 'Manjano')],
        'RW-TO-FR': [('Rouge', 'Nyekundu'), ('Bleu', 'Buluu'), ('Vert', 'Kijani'), ('Jaune', 'Manjano')],
        'SW-TO-EN': [('Red', 'Nyekundu'), ('Blue', 'Buluu'), ('Green', 'Kijani'), ('Yellow', 'Manjano')],
        'SW-TO-FR': [('Rouge', 'Nyekundu'), ('Bleu', 'Buluu'), ('Vert', 'Kijani'), ('Jaune', 'Manjano')],
    },
    'family': {
        'EN-TO-SW': [('Mother', 'Mama'), ('Father', 'Baba'), ('Child', 'Mtoto'), ('Sister', 'Dada')],
        'FR-TO-RW': [('Mother', 'Mama'), ('Father', 'Baba'), ('Child', 'Umwana'), ('Sister', 'Mushiki')],
        'FR-TO-SW': [('Mother', 'Mama'), ('Father', 'Baba'), ('Child', 'Mtoto'), ('Sister', 'Dada')],
        'RW-TO-EN': [('Mother', 'Mama'), ('Father', 'Baba'), ('Child', 'Mtoto'), ('Sister', 'Dada')],
        'RW-TO-FR': [('Mère', 'Mama'), ('Père', 'Baba'), ('Enfant', 'Mtoto'), ('Sœur', 'Dada')],
        'SW-TO-EN': [('Mother', 'Mama'), ('Father', 'Baba'), ('Child', 'Mtoto'), ('Sister', 'Dada')],
        'SW-TO-FR': [('Mère', 'Mama'), ('Père', 'Baba'), ('Enfant', 'Mtoto'), ('Sœur', 'Dada')],
    },
    'house': {
        'EN-TO-SW': [('House', 'Nyumba'), ('Window', 'Dirisha'), ('Door', 'Mlango'), ('Roof', 'Paa')],
        'FR-TO-RW': [('House', 'Inzu'), ('Window', 'Idirishya'), ('Door', 'Umuryango'), ('Roof', 'Igitanda')],
        'FR-TO-SW': [('House', 'Nyumba'), ('Window', 'Dirisha'), ('Door', 'Mlango'), ('Roof', 'Paa')],
        'RW-TO-EN': [('House', 'Nyumba'), ('Window', 'Dirisha'), ('Door', 'Mlango'), ('Roof', 'Paa')],
        'RW-TO-FR': [('Maison', 'Nyumba'), ('Fenêtre', 'Dirisha'), ('Porte', 'Mlango'), ('Toit', 'Paa')],
        'SW-TO-EN': [('House', 'Nyumba'), ('Window', 'Dirisha'), ('Door', 'Mlango'), ('Roof', 'Paa')],
        'SW-TO-FR': [('Maison', 'Nyumba'), ('Fenêtre', 'Dirisha'), ('Porte', 'Mlango'), ('Toit', 'Paa')],
    },
}


def build_block(path, cfg, folder):
    lesson_name = path.stem.replace('-', ' ').title()
    lesson_key = path.stem.lower()
    level = path.parts[-2]
    unit = int(level.replace('level', ''))

    rows = lesson_samples.get(lesson_key, {}).get(folder, [('Word 1', 'Example 1'), ('Word 2', 'Example 2'), ('Word 3', 'Example 3'), ('Word 4', 'Example 4')])
    rows_md = '\n'.join([f"  | {left} | {right} |" for left, right in rows])

    phrase_rows = [
        (f"A {lesson_name.lower()} example", f"Mfano wa {lesson_name.lower()}" if folder in {'EN-TO-SW', 'FR-TO-SW'} else f"An example of {lesson_name.lower()}" if folder in {'RW-TO-EN', 'SW-TO-EN'} else f"Un exemple de {lesson_name.lower()}"),
        (f"I can talk about {lesson_name.lower()}", f"Naweza kuzungumza kuhusu {lesson_name.lower()}" if folder in {'EN-TO-SW', 'FR-TO-SW'} else f"I can talk about {lesson_name.lower()}" if folder in {'RW-TO-EN', 'SW-TO-EN'} else f"Je peux parler de {lesson_name.lower()}"),
        (f"I understand the lesson", f"Naelewa somo" if folder in {'EN-TO-SW', 'FR-TO-SW'} else f"I understand the lesson" if folder in {'RW-TO-EN', 'SW-TO-EN'} else f"J’ai compris la leçon"),
    ]
    phrases_md = '\n'.join([f"  | {left} | {right} |" for left, right in phrase_rows])

    example_lines = [
        f"1. {cfg['example_prefix']} {lesson_name}. → {cfg['example_prefix']} {lesson_name}.",
        f"2. {cfg['example_topic']} → {cfg['example_topic']}",
        f"3. {cfg['example_confidence']} → {cfg['example_confidence']}",
    ]
    example_text = '\n  '.join(example_lines)

    return f'''guidebook: |
  #  {cfg['title']}

  ## SECTION 1, UNIT {unit}
  {lesson_name} in {cfg['target_lang']}

  {cfg['intro']}

  ## {cfg['vocab_heading']}

  {cfg['overview'].format(lesson_name=lesson_name)}

  ### Core words

  | {cfg['column_left']} | {cfg['column_right']} |
  |---|---|
{rows_md}

  ## {cfg['grammar_heading']}

  ### Word order
  In {cfg['target_lang']}, simple sentences often follow a clear pattern: Subject + Verb + Object.

  Examples:
  - Learn the main words first.
  - Build short sentences with the vocabulary from the lesson.
  - Repeat the example sentences out loud.

  ## {cfg['phrases_heading']}

  | {cfg['column_left']} | {cfg['column_right']} |
  |---|---|
{phrases_md}

  ## {cfg['pron_heading']}

  - Say each word slowly and clearly.
  - Repeat the pronunciation several times.
  - Listen for the rhythm of each phrase.

  ## {cfg['goal_heading']}

  - Learn the main vocabulary for this lesson.
  - Use simple words and phrases in short sentences.
  - Understand and respond to basic questions about the topic.

  ## EXAMPLE SENTENCES

  {cfg['example_intro']}
  {example_text}

  ## {cfg['ref_heading']}

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
