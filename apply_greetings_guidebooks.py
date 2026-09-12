from pathlib import Path
import re

lessons = {
    'content/EN-TO-SW/level1/greetings.yaml': {
        'source_label': 'English',
        'target_label': 'Kiswahili',
        'target_name': 'Kiswahili',
        'intro': 'Karibu kwenye somo hili! Katika somo hili utajifunza salamu za kawaida, maneno ya kumpongeza, na maelezo rahisi ya jinsi ya kuzungumza na watu kwa heshima.',
        'pairs': [
            ('Hello', 'Hujambo'),
            ('Good morning', 'Habari za asubuhi'),
            ('Good afternoon / Good evening', 'Habari za mchana / Habari za jioni'),
            ('Good night', 'Usiku mwema'),
            ('How are you?', 'Habari yako'),
            ('I\'m fine', 'Nzuri'),
            ('Welcome', 'Karibu'),
            ('Goodbye', 'Kwa heri'),
            ('See you later', 'Tutaonana baadaye'),
            ('Take care', 'Jiangalie'),
            ('Nice to meet you', 'Nafurahi kukutana nawe'),
        ],
        'key_phrases': [
            ('Hello', 'Hujambo'),
            ('Good morning', 'Habari za asubuhi'),
            ('How are you?', 'Habari yako'),
            ('I\'m fine', 'Nzuri'),
            ('Welcome', 'Karibu'),
            ('Goodbye', 'Kwa heri'),
        ],
        'closing': 'Karibu kwenye somo hili! Endelea kwa mazoezi ya kuzungumza, kusikiliza, na kutafsiri ili kuimarisha ujuzi wako. 🔥',
    },
    'content/EN-TO-RW/level1/greetings.yaml': {
        'source_label': 'English',
        'target_label': 'Kinyarwanda',
        'target_name': 'Kinyarwanda',
        'intro': 'Murakaza neza muri iki kigereranyo! Muri iki gice uziga indamukanyo zisanzwe, uko wabaza amakuru, n\'uburyo bwo kuvugana n\'abantu mu buryo bwiza.',
        'pairs': [
            ('Hello', 'Muraho'),
            ('Good morning', 'Mwaramutse'),
            ('Good afternoon / Good evening', 'Mwiriwe'),
            ('Good night', 'Ijoro ryiza'),
            ('How are you?', 'Amakuru'),
            ('I\'m fine', 'Ni meza'),
            ('Welcome', 'Murakaza neza'),
            ('Goodbye', 'Murabeho'),
            ('See you later', 'Tuzasubira'),
            ('Take care', 'Wirinde'),
            ('Nice to meet you', 'Nishimiye kukubona'),
        ],
        'key_phrases': [
            ('Hello', 'Muraho'),
            ('Good morning', 'Mwaramutse'),
            ('How are you?', 'Amakuru'),
            ('I\'m fine', 'Ni meza'),
            ('Welcome', 'Murakaza neza'),
            ('Goodbye', 'Murabeho'),
        ],
        'closing': 'Murakaza neza! Komeza ku mazoezi yo kuvuga, kumva, no guhuza amagambo mu rwego rwo gukomeza umwuga wawe. 🔥',
    },
    'content/FR-TO-SW/level1/greetings.yaml': {
        'source_label': 'Français',
        'target_label': 'Kiswahili',
        'target_name': 'Kiswahili',
        'intro': 'Karibu kwenye somo hili! Katika somo hili utajifunza salamu za kawaida, maana za maneno, na jinsi ya kujibu kwa heshima.',
        'pairs': [
            ('Bonjour', 'Habari'),
            ('Bonjour (matin)', 'Habari za asubuhi'),
            ('Bonsoir', 'Habari za jioni'),
            ('Bonne nuit', 'Usiku mwema'),
            ('Comment allez-vous ?', 'Habari yako'),
            ('Ça va bien', 'Nzuri'),
            ('Bienvenue', 'Karibu'),
            ('Au revoir', 'Kwa heri'),
            ('À plus tard', 'Tutaonana baadaye'),
            ('Prends soin de toi', 'Jiangalie'),
            ('Enchanté', 'Nafurahi kukutana nawe'),
        ],
        'key_phrases': [
            ('Bonjour', 'Habari'),
            ('Bonsoir', 'Habari za jioni'),
            ('Comment allez-vous ?', 'Habari yako'),
            ('Ça va bien', 'Nzuri'),
            ('Bienvenue', 'Karibu'),
            ('Au revoir', 'Kwa heri'),
        ],
        'closing': 'Karibu kwenye somo hili! Endelea kwa mazoezi ya kuzungumza, kusikiliza, na kutafsiri ili kuimarisha ujuzi wako. 🔥',
    },
    'content/FR-TO-RW/level1/greetings.yaml': {
        'source_label': 'Français',
        'target_label': 'Kinyarwanda',
        'target_name': 'Kinyarwanda',
        'intro': 'Murakaza neza muri iki kigereranyo! Muri iki gice uziga indamukanyo zisanzwe, uko wabaza amakuru, n\'uburyo bwo kuvugana n\'abantu mu mwuka mwiza.',
        'pairs': [
            ('Bonjour', 'Muraho'),
            ('Bonjour (matin)', 'Mwaramutse'),
            ('Bonsoir', 'Mwiriwe'),
            ('Bonne nuit', 'Ijoro ryiza'),
            ('Comment allez-vous ?', 'Amakuru'),
            ('Ça va bien', 'Ni meza'),
            ('Bienvenue', 'Murakaza neza'),
            ('Au revoir', 'Murabeho'),
            ('À plus tard', 'Tuzasubira'),
            ('Prends soin de toi', 'Wirinde'),
            ('Enchanté', 'Nishimiye kukubona'),
        ],
        'key_phrases': [
            ('Bonjour', 'Muraho'),
            ('Bonsoir', 'Mwiriwe'),
            ('Comment allez-vous ?', 'Amakuru'),
            ('Ça va bien', 'Ni meza'),
            ('Bienvenue', 'Murakaza neza'),
            ('Au revoir', 'Murabeho'),
        ],
        'closing': 'Murakaza neza! Komeza ku mazoezi yo kuvuga, kumva, no gukoresha amagambo y\'indamukanyo mu buzima bwa buri munsi. 🔥',
    },
    'content/RW-TO-EN/level1/greetings.yaml': {
        'source_label': 'Kinyarwanda',
        'target_label': 'English',
        'target_name': 'English',
        'intro': 'Welcome to this lesson! In this unit, you will learn basic greetings, polite responses, and how to start simple conversations with confidence.',
        'pairs': [
            ('Muraho', 'Hello'),
            ('Mwaramutse', 'Good morning'),
            ('Mwiriwe', 'Good afternoon / Good evening'),
            ('Ijoro ryiza', 'Good night'),
            ('Amakuru', 'How are you?'),
            ('Ni meza', 'I\'m fine'),
            ('Murakaza neza', 'Welcome'),
            ('Murabeho', 'Goodbye'),
            ('Tuzasubira', 'See you later'),
            ('Wirinde', 'Take care'),
            ('Nishimiye kukubona', 'Nice to meet you'),
        ],
        'key_phrases': [
            ('Muraho', 'Hello'),
            ('Amakuru', 'How are you?'),
            ('Ni meza', 'I\'m fine'),
            ('Murakaza neza', 'Welcome'),
            ('Murabeho', 'Goodbye'),
            ('Tuzasubira', 'See you later'),
        ],
        'closing': 'Good luck! Start with the translation exercises, then try the listening, speaking, and matching activities to reinforce your learning. 🔥',
    },
    'content/SW-TO-EN/level1/greetings.yaml': {
        'source_label': 'Kiswahili',
        'target_label': 'English',
        'target_name': 'English',
        'intro': 'Welcome to this lesson! In this unit, you will learn common Kiswahili greetings, polite replies, and phrases for everyday conversation.',
        'pairs': [
            ('Hujambo', 'Hello'),
            ('Habari za asubuhi', 'Good morning'),
            ('Habari za mchana', 'Good afternoon'),
            ('Habari za jioni', 'Good evening'),
            ('Usiku mwema', 'Good night'),
            ('Habari yako', 'How are you?'),
            ('Nzuri', 'I\'m fine'),
            ('Karibu', 'Welcome'),
            ('Kwa heri', 'Goodbye'),
            ('Tutaonana baadaye', 'See you later'),
            ('Jiangalie', 'Take care'),
        ],
        'key_phrases': [
            ('Hujambo', 'Hello'),
            ('Habari yako', 'How are you?'),
            ('Nzuri', 'I\'m fine'),
            ('Karibu', 'Welcome'),
            ('Kwa heri', 'Goodbye'),
            ('Tutaonana baadaye', 'See you later'),
        ],
        'closing': 'Good luck! Start with the translation exercises, then try the listening, speaking, and matching activities to reinforce your learning. 🔥',
    },
    'content/RW-TO-FR/level1/greetings.yaml': {
        'source_label': 'Kinyarwanda',
        'target_label': 'Français',
        'target_name': 'Français',
        'intro': 'Bienvenue dans cette leçon ! Dans cette unité, vous apprendrez les salutations de base, les réponses polies et les expressions pour commencer une conversation simple.',
        'pairs': [
            ('Muraho', 'Bonjour'),
            ('Mwaramutse', 'Bonjour'),
            ('Mwiriwe', 'Bonsoir'),
            ('Ijoro ryiza', 'Bonne nuit'),
            ('Amakuru', 'Comment allez-vous ?'),
            ('Ni meza', 'Ça va bien'),
            ('Murakaza neza', 'Bienvenue'),
            ('Murabeho', 'Au revoir'),
            ('Tuzasubira', 'À plus tard'),
            ('Wirinde', 'Prends soin de toi'),
            ('Nishimiye kukubona', 'Enchanté de faire votre connaissance'),
        ],
        'key_phrases': [
            ('Muraho', 'Bonjour'),
            ('Amakuru', 'Comment allez-vous ?'),
            ('Ni meza', 'Ça va bien'),
            ('Murakaza neza', 'Bienvenue'),
            ('Murabeho', 'Au revoir'),
            ('Tuzasubira', 'À plus tard'),
        ],
        'closing': 'Bienvenue ! Commencez par les exercices de traduction, puis essayez l\'écoute, la parole et l\'association pour renforcer votre apprentissage. 🔥',
    },
    'content/SW-TO-FR/level1/greetings.yaml': {
        'source_label': 'Kiswahili',
        'target_label': 'Français',
        'target_name': 'Français',
        'intro': 'Bienvenue dans cette leçon ! Dans cette unité, vous apprendrez les salutations courantes en kiswahili et les phrases utiles pour dire bonjour et au revoir.',
        'pairs': [
            ('Hujambo', 'Bonjour'),
            ('Habari za asubuhi', 'Bonjour'),
            ('Habari za mchana', 'Bonsoir'),
            ('Habari za jioni', 'Bonsoir'),
            ('Usiku mwema', 'Bonne nuit'),
            ('Habari yako', 'Comment allez-vous ?'),
            ('Nzuri', 'Ça va bien'),
            ('Karibu', 'Bienvenue'),
            ('Kwa heri', 'Au revoir'),
            ('Tutaonana baadaye', 'À plus tard'),
            ('Jiangalie', 'Prends soin de toi'),
        ],
        'key_phrases': [
            ('Hujambo', 'Bonjour'),
            ('Habari yako', 'Comment allez-vous ?'),
            ('Nzuri', 'Ça va bien'),
            ('Karibu', 'Bienvenue'),
            ('Kwa heri', 'Au revoir'),
            ('Tutaonana baadaye', 'À plus tard'),
        ],
        'closing': 'Bienvenue ! Commencez par les exercices de traduction, puis essayez l\'écoute, la parole et l\'association pour renforcer votre apprentissage. 🔥',
    },
}

pattern = re.compile(r'guidebook: \|.*?\n(?=exercises:)', re.S)

for path_str, data in lessons.items():
    path = Path(path_str)
    text = path.read_text(encoding='utf-8')

    vocabulary = '\n'.join(f'  | {src} | {tgt} |' for src, tgt in data['pairs'])
    key_phrases = '\n'.join(f'  | {src} | {tgt} |' for src, tgt in data['key_phrases'])
    examples = '\n'.join(
        f'  {idx + 1}. {src} → {tgt}'
        for idx, (src, tgt) in enumerate(data['key_phrases'])
    )

    block = f'''guidebook: |
  #  Guidebook

  ## SECTION 1, UNIT 1
  Greetings in {data['target_name']}

  {data['intro']}

  ## 🗣️ GREETING VOCABULARY

  ### Common greetings

  | {data['source_label']} | {data['target_label']} |
  |---|---|
{vocabulary}

  ## 🧠 GRAMMAR TIPS

  ### Polite greeting phrases
  Use these greetings to start conversations respectfully and clearly.

  Examples:
  - Say hello to begin a conversation.
  - Ask how the other person is doing.
  - Reply with a polite, positive response.

  ### Simple sentence pattern
  A useful pattern is: Greeting + Name/Question.

  Example:
  - Hello, how are you?
  - Good morning, I\'m fine.
  - See you later, take care.

  ## 💬 KEY PHRASES

  | {data['source_label']} | {data['target_label']} |
  |---|---|
{key_phrases}

  ## 🗣️ PRONUNCIATION TIPS

  - Say each phrase clearly and with a friendly tone.
  - Practice the rhythm of greetings and responses.
  - Repeat the target-language expressions until they feel natural.

  ## 🎯 UNIT GOAL

  By the end of this unit, you will be able to:

  - Recognize common greetings in {data['target_name']}.
  - Use polite responses and small talk phrases.
  - Start and end conversations with confidence.
  - Answer simple questions about how someone is doing.

  ### Total XP Available: 500 XP

  ## 📝 EXAMPLE SENTENCES

{examples}

  ## 🔍 QUICK REFERENCE

  | Category | Examples |
  |---|---|
  | Greetings | Hello, Good morning, Good night |
  | Questions | How are you?, See you later |
  | Responses | I\'m fine, Welcome, Goodbye |

  {data['closing']}
'''

    new_text = pattern.sub(block + 'exercises:', text, 1)
    if new_text == text:
        new_text = re.sub(r'^(?=exercises:)', block, text, count=1, flags=re.M)
    path.write_text(new_text, encoding='utf-8')
    print(f'updated {path}')
