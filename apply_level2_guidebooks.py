from pathlib import Path
import re

language_map = {
    'EN-TO-SW': ('English', 'Kiswahili', 'Kiswahili', 'Karibu kwenye somo hili! Katika somo hili utajifunza maneno muhimu, sentensi rahisi, na mifano ya matumizi ya mada hii kwa ujasiri.'),
    'EN-TO-RW': ('English', 'Kinyarwanda', 'Kinyarwanda', 'Murakaza neza muri iki gice! Muri iki gice uziga amagambo y\'ibanze, uburyo bwo kuvuga, n\'ibisobanuro by\'iyo mibereho mu buzima bwa buri munsi.'),
    'FR-TO-SW': ('Français', 'Kiswahili', 'Kiswahili', 'Karibu kwenye somo hili! Katika somo hili utajifunza maneno muhimu, sentensi rahisi, na jinsi ya kuyaelezea kwa Kiswahili kwa ujasiri.'),
    'FR-TO-RW': ('Français', 'Kinyarwanda', 'Kinyarwanda', 'Murakaza neza muri iki gice! Muri iki gice uziga amagambo y\'ibanze, ibisobanuro by\'intego, n\'uburyo bwo kubivuga mu Kinyarwanda.'),
    'RW-TO-EN': ('Kinyarwanda', 'English', 'English', 'Welcome to this lesson! In this unit, you will learn useful vocabulary, simple sentence patterns, and key expressions for the topic.'),
    'SW-TO-EN': ('Kiswahili', 'English', 'English', 'Welcome to this lesson! In this unit, you will learn useful vocabulary, simple sentence patterns, and key expressions for the topic.'),
    'RW-TO-FR': ('Kinyarwanda', 'Français', 'Français', 'Bienvenue dans cette leçon ! Dans cette unité, vous apprendrez le vocabulaire de base, des phrases simples et des expressions utiles pour ce sujet.'),
    'SW-TO-FR': ('Kiswahili', 'Français', 'Français', 'Bienvenue dans cette leçon ! Dans cette unité, vous apprendrez le vocabulaire de base, des structures simples et des phrases utiles pour ce sujet.'),
    'BUSINESS-ENGLISH': ('English', 'English', 'English', 'Welcome to this business English lesson! In this unit, you will learn professional vocabulary, polite phrases, and practical communication skills for the workplace.'),
}

topics = {
    'clothing': {
        'title': 'Clothing',
        'summary': 'Core clothing words, useful phrases, and sentence patterns for talking about outfits, accessories, and what people wear.',
        'rows': [
            ('English', 'Shirt', 'Pants', 'Dress', 'Hat', 'Shoes'),
            ('Français', 'Chemise', 'Pantalon', 'Robe', 'Chapeau', 'Chaussures'),
            ('Kinyarwanda', 'Ishati', 'Ipantaro', 'Ikanzu', 'Ingobyi', 'Inkweto'),
            ('Kiswahili', 'Shati', 'Suruali', 'Gauni', 'Kofia', 'Viatu'),
        ],
    },
    'food': {
        'title': 'Food',
        'summary': 'Common food and meal vocabulary, simple phrases for eating and drinking, and useful expressions for eating occasions.',
        'rows': [
            ('English', 'Food', 'Water', 'Meat', 'Rice', 'Bread'),
            ('Français', 'Nourriture', 'Eau', 'Viande', 'Riz', 'Pain'),
            ('Kinyarwanda', 'Ifunguro', 'Amazi', 'Inyama', 'Umuceri', 'Umugati'),
            ('Kiswahili', 'Chakula', 'Maji', 'Nyama', 'Mchele', 'Mkate'),
        ],
    },
    'house': {
        'title': 'House',
        'summary': 'Home vocabulary for rooms, doors, windows, and everyday house-related phrases.',
        'rows': [
            ('English', 'House', 'Room', 'Door', 'Window', 'Kitchen'),
            ('Français', 'Maison', 'Chambre', 'Porte', 'Fenêtre', 'Cuisine'),
            ('Kinyarwanda', 'Inzu', 'Icyumba', 'Urugi', 'Idirishya', 'Igikoni'),
            ('Kiswahili', 'Nyumba', 'Chumba', 'Mlango', 'Dirisha', 'Jikoni'),
        ],
    },
    'daily-routines': {
        'title': 'Daily routines',
        'summary': 'Everyday action words and phrases for describing simple routines, habits, and daily activities.',
        'rows': [
            ('English', 'Wake up', 'Eat breakfast', 'Go to work', 'Study', 'Sleep'),
            ('Français', 'Se réveiller', 'Prendre le petit-déjeuner', 'Aller au travail', 'Étudier', 'Dormir'),
            ('Kinyarwanda', 'Kuzuka', 'Kurya ifunguro rya mu gitondo', 'Gujya ku kazi', 'Kwiga', 'Kuryama'),
            ('Kiswahili', 'Kuamka', 'Kula kifungua kinywa', 'Kwenda kazini', 'Kusoma', 'Kulala'),
        ],
    },
    'weather': {
        'title': 'Weather',
        'summary': 'Weather vocabulary and expressions for talking about rain, sun, wind, temperature, and daily weather conditions.',
        'rows': [
            ('English', 'Rain', 'Sun', 'Wind', 'Hot', 'Cold'),
            ('Français', 'Pluie', 'Soleil', 'Vent', 'Chaud', 'Froid'),
            ('Kinyarwanda', 'Imvura', 'Izuba', 'Umuyaga', 'Hashyushye', 'Ubukonje'),
            ('Kiswahili', 'Mvua', 'Jua', 'Upepo', 'Moto', 'Baridi'),
        ],
    },
    'workplace-communication': {
        'title': 'Workplace communication',
        'summary': 'Professional communication vocabulary, polite phrases, and useful structures for business calls, meetings, and workplace conversations.',
        'rows': [
            ('English', 'Call', 'Meeting', 'Request', 'Email', 'Reply'),
            ('Français', 'Appel', 'Réunion', 'Demande', 'Courriel', 'Réponse'),
            ('Kinyarwanda', 'Telefoni', 'Inama', 'Gusaba', 'Imeli', 'Gusubiza'),
            ('Kiswahili', 'Simu', 'Mkutano', 'Ombi', 'Barua pepe', 'Jibu'),
        ],
    },
}

closers = {
    'Kiswahili': 'Karibu! Endelea na mazoezi ya kutafsiri, kusikiliza, kuzungumza na kuoanisha ili kuimarisha ujuzi wako. 🔥',
    'Kinyarwanda': 'Murakaza neza! Komeza ku mazoezi yo kuvuga, kumva, no guhuza amagambo mu buryo bwo gukomeza ubuhanga bwawe. 🔥',
    'Français': 'Bienvenue ! Continuez avec les exercices de traduction, d’écoute, de parole et d’association pour renforcer votre apprentissage. 🔥',
    'English': 'Good luck! Start with the translation exercises, then try the listening, speaking, and matching activities to reinforce your learning. 🔥',
}

pattern = re.compile(r'guidebook: \|.*?(?=^exercises:)', re.S | re.M)

for path in sorted(Path('content').glob('*/level2/*.yaml')):
    topic_key = path.stem
    if topic_key not in topics:
        continue

    container = path.parts[1]
    source_label, target_label, target_name, intro = language_map.get(container, ('English', 'English', 'English', 'Welcome to this lesson! In this unit, you will learn useful vocabulary, simple sentence patterns, and key expressions for the topic.'))
    topic = topics[topic_key]
    rows = {lang: values for lang, *values in topic['rows']}
    source_words = rows.get(source_label, rows['English'])
    target_words = rows.get(target_label, rows['English'])

    vocabulary_rows = '\n'.join(
        f'  | {source_words[i]} | {target_words[i]} |' for i in range(min(len(source_words), len(target_words)))
    )

    block = f'''guidebook: |
  #  Guidebook

  ## SECTION 2, UNIT 2
  {topic['title']} in {target_name}

  {intro}

  ## {topic['title'].upper()} VOCABULARY

  {topic['summary']}

  ### Core vocabulary

  | {source_label} | {target_label} |
  |---|---|
{vocabulary_rows}

  ## 🧠 GRAMMAR & PHRASE TIPS

  ### Simple sentence patterns
  Use the topic vocabulary in short, clear sentences.

  Examples:
  - {source_words[0]} + is + ...
  - {source_words[1]} + is + ...
  - I use {source_words[2]} when ...

  ## 💬 KEY PHRASES

  | {source_label} | {target_label} |
  |---|---|
  | {source_words[0]} is important | ... |
  | I like {source_words[1]} | ... |
  | {source_words[2]} is useful | ... |

  ## 🎯 UNIT GOAL

  By the end of this unit, you will be able to:

  - Recognize and use the main words for this topic in {target_name}.
  - Build simple sentences using the new vocabulary.
  - Understand and answer basic questions about the topic.

  ### Total XP Available: 500 XP

  ## 📝 EXAMPLE SENTENCES

  1. {source_words[0]} + ...
  2. {source_words[1]} + ...
  3. {source_words[2]} + ...
  4. {source_words[3]} + ...
  5. {source_words[4]} + ...

  ## 🔍 QUICK REFERENCE

  - Review the vocabulary words every day.
  - Practice saying the target words aloud.
  - Use the examples to build short sentences.

  {closers.get(target_label, closers['English'])}
'''

    text = path.read_text(encoding='utf-8')
    if pattern.search(text):
        new_text = pattern.sub(block, text, count=1)
    else:
        new_text = re.sub(r'(?m)^exercises:', block + '\nexercises:', text, count=1)

    path.write_text(new_text, encoding='utf-8')
    print(f'updated {path}')
