from pathlib import Path
import re

root = Path('content')
target_dirs = ['EN-TO-SW', 'FR-TO-RW', 'FR-TO-SW', 'RW-TO-EN', 'RW-TO-FR', 'SW-TO-EN', 'SW-TO-FR']

configs = {
    'EN-TO-SW': {
        'title_prefix': 'Mwongozo wa',
        'overview_heading': 'Muhtasari',
        'vocab_heading': 'Msamiati wa msingi',
        'grammar_heading': 'Dokezo la sarufi',
        'phrases_heading': 'Vifungu muhimu',
        'pron_heading': 'Dokezo la matamshi',
        'goal_heading': 'Lengo la somo',
        'ref_heading': 'Marejeo ya haraka',
        'overview': 'Karibu kwenye somo hili! Utajifunza maneno muhimu, vifungu, na muundo wa sentensi unaohitajika kuzungumza kuhusu mada hii kwa ujasiri.',
        'vocab': 'Jifunze msamiati wa msingi na urudie kila neno au kifungu mara kadhaa.',
        'grammar': 'Zingatia muundo wa sentensi unaotumika katika somo hili na ujifunze kwa mifano mifupi.',
        'phrases': 'Sema kila kifungu kwa sauti na ujaribu kukitumia katika sentensi fupi.',
        'pron': 'Zungumza polepole na kwa uwazi. Rudia kila neno mara kadhaa ili matamshi yawe ya asili.',
        'goal': 'Mwishoni mwa somo hili, utaweza kuelewa na kutumia lugha kuu ya sehemu hii vizuri.',
        'ref_lines': ['Jifunze maneno ya msingi kwanza.', 'Rudia vifungu muhimu kwa sauti.', 'Jizoeze kwa mifano fupi kila siku.']
    },
    'FR-TO-RW': {
        'title_prefix': 'Ubufasha bwa',
        'overview_heading': 'Inshamake',
        'vocab_heading': 'Amagambo y’ingenzi',
        'grammar_heading': 'Inyongera ku ivangura',
        'phrases_heading': 'Imvugo y’ingenzi',
        'pron_heading': 'Inyongera ku kuvuga',
        'goal_heading': 'Intego y’igisomo',
        'ref_heading': 'Reba vuba',
        'overview': 'Murakaza neza muri iki kigereranyo! Muziga amagambo y’ingenzi, imvugo, n’imiterere ya interuro ikenewe yo kuvuga ku kibazo cyanyu neza.',
        'vocab': 'Mwigire amagambo y’ingenzi kandi musubiremo buri jambo cyangwa imvugo inshuro nyinshi.',
        'grammar': 'Shyira umutwe ku buryo bw’interuro bukoreshwa muri iki kigereranyo kandi mugerageze interuro ntoya.',
        'phrases': 'Muvuge buri mvugo mu ijwi kandi mugerageze kuyikoresha mu nteruro ntoya.',
        'pron': 'Muvuge buhoro kandi neza. Subiramo buri jambo inshuro nyinshi kugira ngo amajwi abe yuzuye.',
        'goal': 'Mu mpera z’iki kigereranyo, muzashobora gusobanukirwa no gukoresha ikiganiro cya mbere cy’iki gice.',
        'ref_lines': ['Mwigire amagambo y’ingenzi mbere.', 'Subiramo imvugo y’ingenzi mu ijwi.', 'Kora imyitozo mito ya buri munsi.']
    },
    'FR-TO-SW': {
        'title_prefix': 'Mwongozo wa',
        'overview_heading': 'Muhtasari',
        'vocab_heading': 'Msamiati wa msingi',
        'grammar_heading': 'Dokezo la sarufi',
        'phrases_heading': 'Vifungu muhimu',
        'pron_heading': 'Dokezo la matamshi',
        'goal_heading': 'Lengo la somo',
        'ref_heading': 'Marejeo ya haraka',
        'overview': 'Karibu kwenye somo hili! Utajifunza maneno muhimu, vifungu, na muundo wa sentensi unaohitajika kuzungumza kuhusu mada hii kwa ujasiri.',
        'vocab': 'Jifunze msamiati wa msingi na urudie kila neno au kifungu mara kadhaa.',
        'grammar': 'Zingatia muundo wa sentensi unaotumika katika somo hili na ujifunze kwa mifano mifupi.',
        'phrases': 'Sema kila kifungu kwa sauti na ujaribu kukitumia katika sentensi fupi.',
        'pron': 'Zungumza polepole na kwa uwazi. Rudia kila neno mara kadhaa ili matamshi yawe ya asili.',
        'goal': 'Mwishoni mwa somo hili, utaweza kuelewa na kutumia lugha kuu ya sehemu hii vizuri.',
        'ref_lines': ['Jifunze maneno ya msingi kwanza.', 'Rudia vifungu muhimu kwa sauti.', 'Jizoeze kwa mifano fupi kila siku.']
    },
    'RW-TO-EN': {
        'title_prefix': 'Guidebook for',
        'overview_heading': 'Overview',
        'vocab_heading': 'Core vocabulary',
        'grammar_heading': 'Grammar tip',
        'phrases_heading': 'Key phrases',
        'pron_heading': 'Pronunciation tip',
        'goal_heading': 'Unit goal',
        'ref_heading': 'Quick reference',
        'overview': 'Welcome to this lesson! You will learn the key words, phrases, and sentence patterns needed to talk about this topic with confidence.',
        'vocab': 'Learn the core vocabulary and repeat each word or phrase several times.',
        'grammar': 'Focus on the sentence pattern used in this lesson and practise it with short examples.',
        'phrases': 'Say each phrase aloud and try to use it in a simple sentence.',
        'pron': 'Practice slowly and clearly. Repeat each word several times so the sounds become natural.',
        'goal': 'By the end of this lesson, you will be able to understand and use the main language from this unit.',
        'ref_lines': ['Learn the core words first.', 'Repeat the main phrases aloud.', 'Practice with short examples each day.']
    },
    'RW-TO-FR': {
        'title_prefix': 'Guide pour',
        'overview_heading': 'Vue d’ensemble',
        'vocab_heading': 'Vocabulaire de base',
        'grammar_heading': 'Astuce de grammaire',
        'phrases_heading': 'Phrases clés',
        'pron_heading': 'Astuce de prononciation',
        'goal_heading': 'Objectif de l’unité',
        'ref_heading': 'Référence rapide',
        'overview': 'Bienvenue dans cette leçon ! Vous allez apprendre les mots, les expressions et les structures de base nécessaires pour parler de ce sujet avec confiance.',
        'vocab': 'Apprenez le vocabulaire principal et répétez chaque mot ou expression plusieurs fois.',
        'grammar': 'Concentrez-vous sur le modèle de phrase utilisé dans cette leçon et pratiquez-le avec de courts exemples.',
        'phrases': 'Dites chaque expression à voix haute et essayez de l’utiliser dans une phrase simple.',
        'pron': 'Pratiquez lentement et clairement. Répétez chaque mot plusieurs fois pour mieux mémoriser les sons.',
        'goal': 'À la fin de cette leçon, vous serez capable de comprendre et d’utiliser le langage principal de cette unité.',
        'ref_lines': ['Apprenez les mots clés d’abord.', 'Répétez les phrases principales à voix haute.', 'Pratiquez avec de courts exemples chaque jour.']
    },
    'SW-TO-EN': {
        'title_prefix': 'Guidebook for',
        'overview_heading': 'Overview',
        'vocab_heading': 'Core vocabulary',
        'grammar_heading': 'Grammar tip',
        'phrases_heading': 'Key phrases',
        'pron_heading': 'Pronunciation tip',
        'goal_heading': 'Unit goal',
        'ref_heading': 'Quick reference',
        'overview': 'Welcome to this lesson! You will learn the key words, phrases, and sentence patterns needed to talk about this topic with confidence.',
        'vocab': 'Learn the core vocabulary and repeat each word or phrase several times.',
        'grammar': 'Focus on the sentence pattern used in this lesson and practise it with short examples.',
        'phrases': 'Say each phrase aloud and try to use it in a simple sentence.',
        'pron': 'Practice slowly and clearly. Repeat each word several times so the sounds become natural.',
        'goal': 'By the end of this lesson, you will be able to understand and use the main language from this unit.',
        'ref_lines': ['Learn the core words first.', 'Repeat the main phrases aloud.', 'Practice with short examples each day.']
    },
    'SW-TO-FR': {
        'title_prefix': 'Guide pour',
        'overview_heading': 'Vue d’ensemble',
        'vocab_heading': 'Vocabulaire de base',
        'grammar_heading': 'Astuce de grammaire',
        'phrases_heading': 'Phrases clés',
        'pron_heading': 'Astuce de prononciation',
        'goal_heading': 'Objectif de l’unité',
        'ref_heading': 'Référence rapide',
        'overview': 'Bienvenue dans cette leçon ! Vous allez apprendre les mots, les expressions et les structures de base nécessaires pour parler de ce sujet avec confiance.',
        'vocab': 'Apprenez le vocabulaire principal et répétez chaque mot ou expression plusieurs fois.',
        'grammar': 'Concentrez-vous sur le modèle de phrase utilisé dans cette leçon et pratiquez-le avec de courts exemples.',
        'phrases': 'Dites chaque expression à voix haute et essayez de l’utiliser dans une phrase simple.',
        'pron': 'Pratiquez lentement et clairement. Répétez chaque mot plusieurs fois pour mieux mémoriser les sons.',
        'goal': 'À la fin de cette leçon, vous serez capable de comprendre et d’utiliser le langage principal de cette unité.',
        'ref_lines': ['Apprenez les mots clés d’abord.', 'Répétez les phrases principales à voix haute.', 'Pratiquez avec de courts exemples chaque jour.']
    },
}

for folder in target_dirs:
    folder_path = root / folder
    cfg = configs[folder]
    for path in sorted(folder_path.rglob('*.yaml')):
        text = path.read_text(encoding='utf-8')
        if 'guidebook: |' not in text:
            continue
        title_match = re.search(r"^\s*name:\s*[\"']?(.*?)[\"']?\s*$", text, re.M)
        title = title_match.group(1).strip() if title_match else path.stem.replace('-', ' ').title()
        ref_lines_text = '\n'.join(f"  - {line}" for line in cfg['ref_lines'])
        block = f'''guidebook: |
  #  {cfg['title_prefix']} {title}

  ## 📖 {cfg['overview_heading']}

  {cfg['overview']}

  ---

  ## 🧠 {cfg['vocab_heading']}

  {cfg['vocab']}

  ---

  ## 📝 {cfg['grammar_heading']}

  {cfg['grammar']}

  ---

  ## 💬 {cfg['phrases_heading']}

  {cfg['phrases']}

  ---

  ## 🔊 {cfg['pron_heading']}

  {cfg['pron']}

  ---

  ## 🔄 {cfg['ref_heading']}

{ref_lines_text}

  ---

  ## 🎯 {cfg['goal_heading']}

  {cfg['goal']}
'''
        pattern = re.compile(r'guidebook:\s*\|.*?(?=^exercises:|\Z)', re.S | re.M)
        text, _ = pattern.subn(block, text, count=1)
        path.write_text(text, encoding='utf-8')
        print(f'updated {path}')
