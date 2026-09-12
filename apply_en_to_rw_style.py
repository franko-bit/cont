from pathlib import Path
import re

root = Path('content')
source = root / 'EN-TO-RW' / 'level1' / 'animals.yaml'
source_text = source.read_text(encoding='utf-8')

# Extract the structured guidebook_data block from the source lesson.
source_match = re.search(r'guidebook_data:\s*\n(?P<body>(?:.*\n)*?)(?=^guidebook:|\Z)', source_text, re.M)
if not source_match:
    raise SystemExit('Could not find source guidebook_data block')
source_body = source_match.group('body')

# Keep only the data structure content, replacing the inline YAML values with a target-language-friendly markdown builder.
def build_markdown(folder, lesson_name, lesson_key, unit):
    if folder == 'EN-TO-SW':
        lang_name = 'Kiswahili'
        intro = 'Karibu kwenye somo hili! Katika somo hili utajifunza maneno muhimu, sentensi rahisi, na mifano ya matumizi ya mada hii kwa ujasiri.'
        vocab_title = '📚 Animal Vocabulary'
        verbs_title = '🏃 Animal Actions (Verbs)'
        food_title = '🍽️ Animal Food'
        grammar_title = '📖 Grammar Tips'
        pron_title = '🗣️ Pronunciation Tips'
        key_title = '💬 Key Phrases'
        goal_title = '🎯 Unit Goal'
        example_title = '📝 Example Sentences'
        ref_title = '🔍 Quick Reference'
        footer = 'Karibu kwenye somo hili! Endelea kwa mazoezi ya kutafsiri, kusikiliza, kuzungumza na kuoanisha ili kuimarisha ujifunzaji wako. 🔥'
        return f'''guidebook: |
  #  Guidebook

  ## SECTION 1, UNIT {unit}
  Animals in {lang_name}

  {intro}

  ANIMAL VOCABULARY

  ### Domestic & Farm Animals

  | English | {lang_name} |
  ||-|
  | Cow | Ngombe |
  | Dog | Mbwa |
  | Cat | Paka |
  | Goat | Mbuzi |
  | Sheep | Kondoo |
  | Pig | Nguruwe |
  | Chicken | Kuku |
  | Rabbit | Sungura |
  | Donkey | Punda |
  | Pigeon | Njiwa |
  | Bird | Ndege |

  ### Wildlife Animals

  | English | {lang_name} |
  ||-|
  | Lion | Simba |
  | Elephant | Tembo |
  | Mountain gorilla | Gorila |
  | Leopard | Chui |
  | Buffalo | Nyati |
  | Zebra | Punda Milia |
  | Hippopotamus | Kiboko |
  | Giraffe | Twiga |
  | Hyena | Fisi |
  | Monkey | Tumbili |

  ### Birds, Reptiles, Insects & Fish

  | English | {lang_name} |
  ||-|
  | Grey crowned crane | Korongo |
  | Snake | Nyoka |
  | Frog | Chura |
  | Fish | Samaki |
  | Bee | Nyuki |

  ## ANIMAL ACTIONS (VERBS)

  | English | {lang_name} |
  ||-|
  | eats | hula |
  | drinks | kunywa |
  | sleeps | hulala |
  | runs | hukimbia |
  | flies | huruka |
  | swims | huogelea |
  | plays | hucheza |

  ## ANIMAL FOOD

  | English | {lang_name} |
  ||-|
  | grass | nyasi |
  | water | maji |
  | meat | nyama |

  ## GRAMMAR TIPS

  ### Word Order (Subject + Verb + Object)
  In Kiswahili, simple sentences follow the pattern: Subject + Verb + Object.

  Examples:
  - Ngombe hula nyasi. → The cow eats grass.
  - Mbwa kunywa maji. → The dog drinks water.
  - Ndege huruka. → The bird flies.

  ### Verb Prefixes
  Notice that verbs often begin with hu- or h- in simple present forms:
  - hula (eats)
  - kunywa (drinks)
  - hulala (sleeps)
  - huruka (flies)

  ## 🗣️ PRONUNCIATION TIPS

  ### Vowels
  Kiswahili vowels are clear and open:
  - a → like "a" in "father"
  - e → like "e" in "bed"
  - i → like "ee" in "see"
  - o → like "o" in "go"
  - u → like "oo" in "boot"

  ### Stress
  Stress usually falls on the penultimate syllable of a word.

  ## KEY PHRASES

  | English | {lang_name} |
  ||-|
  | The cow eats grass | Ngombe hula nyasi |
  | The dog drinks water | Mbwa kunywa maji |
  | The bird flies | Ndege huruka |
  | The fish swims | Samaki huogelea |
  | A dog is an animal | Mbwa ni mnyama |
  | Where is the cow? | Ngombe yuko wapi? |

  ## UNIT GOAL

  By the end of this unit, you will be able to:

  - Name 30+ animals in Kiswahili (domestic, wildlife, birds, reptiles, insects, and fish)
  - Use action verbs to describe what animals do (eat, drink, sleep, run, fly, swim, play)
  - Name common foods that animals eat (grass, water, meat)
  - Build simple sentences about animals (subject + verb + object)
  - Understand and respond to questions about animals

  ### Total XP Available: 500 XP

  ## EXAMPLE SENTENCES

  1. Ngombe hula nyasi. → The cow eats grass.
  2. Mbwa kunywa maji. → The dog drinks water.
  3. Ndege huruka juu. → The bird flies above.
  4. Simba hula nyama. → The lion eats meat.
  5. Samaki huogelea. → The fish swims.
  6. Mbwa ni mnyama. → A dog is an animal.
  7. Ngombe yuko shambani. → The cow is in the field.

  ## QUICK REFERENCE

  | Category | Examples |
  |-|-|
  | Farm animals | Ngombe, Mbwa, Paka, Mbuzi, Kondoo, Nguruwe, Kuku |
  | Wild animals | Simba, Tembo, Gorila, Chui, Nyati, Punda Milia |
  | Other animals | Korongo, Nyoka, Chura, Samaki, Nyuki |
  | Actions | hula, kunywa, hulala, hukimbia, huruka, huogelea |
  | Food | nyasi, maji, nyama |

  {footer}
'''
    elif folder == 'FR-TO-RW':
        return f'''guidebook: |
  #  Guidebook

  ## SECTION 1, UNIT {unit}
  Animals in Kinyarwanda

  Murakaza neza muri iki kigereranyo! Muri iki gice muziga amagambo, imvugo, n’imiterere ya interuro ikenewe yo kuvuga ku kibazo cyanyu neza.

  ANIMAL VOCABULARY

  ### Domestic & Farm Animals

  | English | Kinyarwanda |
  ||-|
  | Cow | Inka |
  | Dog | Imbwa |
  | Cat | Injangwe |
  | Goat | Ihene |
  | Sheep | Intama |
  | Pig | Ingurube |
  | Chicken | Inkoko |
  | Rabbit | Urukwavu |
  | Donkey | Indogobe |
  | Pigeon | Inuma |
  | Bird | Inyoni |

  ### Wildlife Animals

  | English | Kinyarwanda |
  ||-|
  | Lion | Intare |
  | Elephant | Inzovu |
  | Mountain gorilla | Ingagi |
  | Leopard | Ingwe |
  | Buffalo | Imbogo |
  | Zebra | Imparage |
  | Hippopotamus | Imvubu |
  | Giraffe | Agasumbashyamba |
  | Hyena | Imfyisi |
  | Monkey | Inkende |

  ### Birds, Reptiles, Insects & Fish

  | English | Kinyarwanda |
  ||-|
  | Grey crowned crane | Umusambi |
  | Snake | Inzoka |
  | Frog | Igikeri |
  | Fish | Ifi |
  | Bee | Inzuki |

  ## ANIMAL ACTIONS (VERBS)

  | English | Kinyarwanda |
  ||-|
  | eats | irya |
  | drinks | inywa |
  | sleeps | irasinzira |
  | runs | iriruka |
  | flies | iraguruka |
  | swims | iroga |
  | plays | irakina |

  ## ANIMAL FOOD

  | English | Kinyarwanda |
  ||-|
  | grass | ibyatsi |
  | water | amazi |
  | meat | inyama |

  ## GRAMMAR TIPS

  ### Word Order (Subject + Verb + Object)
  In Kinyarwanda, simple sentences follow the pattern: Subject + Verb + Object.

  Examples:
  - Inka irya ibyatsi. → The cow eats grass.
  - Imbwa inywa amazi. → The dog drinks water.
  - Inyoni iraguruka. → The bird flies.

  ### Verb Prefixes
  Notice that verbs often begin with i- or ira- when talking about animals:
  - irya (eats)
  - inywa (drinks)
  - irasinzira (sleeps)
  - iraguruka (flies)

  ## 🗣️ PRONUNCIATION TIPS

  ### Vowels
  Kinyarwanda vowels are pure sounds:
  - a → like "a" in "father"
  - e → like "e" in "bed"
  - i → like "ee" in "see"
  - o → like "o" in "go"
  - u → like "oo" in "boot"

  ### Stress
  Stress usually falls on the second-to-last syllable of a word.

  ## KEY PHRASES

  | English | Kinyarwanda |
  ||-|
  | The cow eats grass | Inka irya ibyatsi |
  | The dog drinks water | Imbwa inywa amazi |
  | The bird flies | Inyoni iraguruka |
  | The fish swims | Ifi iroga |
  | A dog is an animal | Imbwa ni inyamaswa |
  | Where is the cow? | Inka iri he? |

  ## UNIT GOAL

  By the end of this unit, you will be able to:

  - Name 30+ animals in Kinyarwanda (domestic, wildlife, birds, reptiles, insects, and fish)
  - Use action verbs to describe what animals do (eat, drink, sleep, run, fly, swim, play)
  - Name common foods that animals eat (grass, water, meat)
  - Build simple sentences about animals (subject + verb + object)
  - Understand and respond to questions about animals

  ### Total XP Available: 500 XP

  ## EXAMPLE SENTENCES

  1. Inka irya ibyatsi. → The cow eats grass.
  2. Imbwa inywa amazi. → The dog drinks water.
  3. Inyoni iraguruka hejuru. → The bird flies above.
  4. Intare irya inyama. → The lion eats meat.
  5. Ifi iroga. → The fish swims.
  6. Imbwa ni inyamaswa. → A dog is an animal.
  7. Inka iri mu murima. → The cow is in the field.

  ## QUICK REFERENCE

  | Category | Examples |
  |-|-|
  | Farm animals | Inka, Imbwa, Injangwe, Ihene, Intama, Ingurube, Inkoko |
  | Wild animals | Intare, Inzovu, Ingagi, Ingwe, Imbogo, Imparage |
  | Other animals | Umusambi, Inzoka, Igikeri, Ifi, Inzuki |
  | Actions | irya, inywa, irasinzira, iriruka, iraguruka, iroga |
  | Food | ibyatsi, amazi, inyama |

  Good luck! Start with the translation exercises, then try the listening, speaking, and matching activities to reinforce your learning. 🔥
'''
    else:
        lang_name = 'Kiswahili' if folder == 'EN-TO-SW' else 'Kinyarwanda' if folder == 'FR-TO-RW' else 'English' if folder in {'RW-TO-EN', 'SW-TO-EN'} else 'Français'
        intro = 'Karibu kwenye somo hili! Katika somo hili utajifunza maneno muhimu, sentensi rahisi, na mifano ya matumizi ya mada hii kwa ujasiri.' if folder in {'EN-TO-SW', 'FR-TO-SW'} else 'Bienvenue dans cette leçon ! Dans cette unité, vous apprendrez le vocabulaire essentiel, les structures de phrases simples et les expressions utiles pour ce sujet.' if folder in {'RW-TO-FR', 'SW-TO-FR'} else 'Welcome to this lesson! In this unit, you will learn the core vocabulary, simple sentence patterns, and useful phrases for this topic.'
        word1, word2, word3, word4, word5, word6, word7, word8, word9, word10, word11 = 'Ngombe', 'Mbwa', 'Paka', 'Mbuzi', 'Kondoo', 'Nguruwe', 'Kuku', 'Sungura', 'Punda', 'Njiwa', 'Ndege'
        word12, word13, word14, word15, word16, word17, word18, word19, word20, word21 = 'Simba', 'Tembo', 'Gorila', 'Chui', 'Nyati', 'Punda Milia', 'Kiboko', 'Twiga', 'Fisi', 'Tumbili'
        word22, word23, word24, word25, word26 = 'Korongo', 'Nyoka', 'Chura', 'Samaki', 'Nyuki'
        verb1, verb2, verb3, verb4, verb5, verb6, verb7 = 'hula', 'kunywa', 'hulala', 'hukimbia', 'huruka', 'huogelea', 'hucheza'
        food1, food2, food3 = 'nyasi', 'maji', 'nyama'
        sentence1, sentence2, sentence3 = 'Ngombe hula nyasi', 'Mbwa kunywa maji', 'Ndege huruka'
        verb1_phrase, verb2_phrase, verb3_phrase, verb5_phrase = 'hula (eats)', 'kunywa (drinks)', 'hulala (sleeps)', 'huruka (flies)'
        phrase1, phrase2, phrase3, phrase4, phrase5, phrase6 = 'Ngombe hula nyasi', 'Mbwa kunywa maji', 'Ndege huruka', 'Samaki huogelea', 'Mbwa ni mnyama', 'Ngombe yuko wapi?'
        example1, example2, example3, example4, example5, example6, example7 = 'Ngombe hula nyasi. → The cow eats grass.', 'Mbwa kunywa maji. → The dog drinks water.', 'Ndege huruka juu. → The bird flies above.', 'Simba hula nyama. → The lion eats meat.', 'Samaki huogelea. → The fish swims.', 'Mbwa ni mnyama. → A dog is an animal.', 'Ngombe yuko shambani. → The cow is in the field.'
        farm_examples, wild_examples, other_examples, action_examples, food_examples = 'Ngombe, Mbwa, Paka, Mbuzi, Kondoo, Nguruwe, Kuku', 'Simba, Tembo, Gorila, Chui, Nyati, Punda Milia', 'Korongo, Nyoka, Chura, Samaki, Nyuki', 'hula, kunywa, hulala, hukimbia, huruka, huogelea', 'nyasi, maji, nyama'
        return f'''guidebook: |
  #  Guidebook

  ## SECTION 1, UNIT {unit}
  Animals in {lang_name}

  {intro}

  ANIMAL VOCABULARY

  ### Domestic & Farm Animals

  | English | {lang_name} |
  ||-|
  | Cow | {word1} |
  | Dog | {word2} |
  | Cat | {word3} |
  | Goat | {word4} |
  | Sheep | {word5} |
  | Pig | {word6} |
  | Chicken | {word7} |
  | Rabbit | {word8} |
  | Donkey | {word9} |
  | Pigeon | {word10} |
  | Bird | {word11} |

  ### Wildlife Animals

  | English | {lang_name} |
  ||-|
  | Lion | {word12} |
  | Elephant | {word13} |
  | Mountain gorilla | {word14} |
  | Leopard | {word15} |
  | Buffalo | {word16} |
  | Zebra | {word17} |
  | Hippopotamus | {word18} |
  | Giraffe | {word19} |
  | Hyena | {word20} |
  | Monkey | {word21} |

  ### Birds, Reptiles, Insects & Fish

  | English | {lang_name} |
  ||-|
  | Grey crowned crane | {word22} |
  | Snake | {word23} |
  | Frog | {word24} |
  | Fish | {word25} |
  | Bee | {word26} |

  ## ANIMAL ACTIONS (VERBS)

  | English | {lang_name} |
  ||-|
  | eats | {verb1} |
  | drinks | {verb2} |
  | sleeps | {verb3} |
  | runs | {verb4} |
  | flies | {verb5} |
  | swims | {verb6} |
  | plays | {verb7} |

  ## ANIMAL FOOD

  | English | {lang_name} |
  ||-|
  | grass | {food1} |
  | water | {food2} |
  | meat | {food3} |

  ## GRAMMAR TIPS

  ### Word Order (Subject + Verb + Object)
  In {lang_name}, simple sentences follow the pattern: Subject + Verb + Object.

  Examples:
  - {sentence1} → The cow eats grass.
  - {sentence2} → The dog drinks water.
  - {sentence3} → The bird flies.

  ### Verb Prefixes
  Notice that verbs often begin with a clear prefix in this language:
  - {verb1_phrase}
  - {verb2_phrase}
  - {verb3_phrase}
  - {verb5_phrase}

  ## 🗣️ PRONUNCIATION TIPS

  ### Vowels
  The vowel sounds are clear and open:
  - a → like "a" in "father"
  - e → like "e" in "bed"
  - i → like "ee" in "see"
  - o → like "o" in "go"
  - u → like "oo" in "boot"

  ### Stress
  Stress usually falls on the penultimate syllable of a word.

  ## KEY PHRASES

  | English | {lang_name} |
  ||-|
  | The cow eats grass | {phrase1} |
  | The dog drinks water | {phrase2} |
  | The bird flies | {phrase3} |
  | The fish swims | {phrase4} |
  | A dog is an animal | {phrase5} |
  | Where is the cow? | {phrase6} |

  ## UNIT GOAL

  By the end of this unit, you will be able to:

  - Name 30+ animals in {lang_name} (domestic, wildlife, birds, reptiles, insects, and fish)
  - Use action verbs to describe what animals do (eat, drink, sleep, run, fly, swim, play)
  - Name common foods that animals eat (grass, water, meat)
  - Build simple sentences about animals (subject + verb + object)
  - Understand and respond to questions about animals

  ### Total XP Available: 500 XP

  ## EXAMPLE SENTENCES

  1. {example1}
  2. {example2}
  3. {example3}
  4. {example4}
  5. {example5}
  6. {example6}
  7. {example7}

  ## QUICK REFERENCE

  | Category | Examples |
  |-|-|
  | Farm animals | {farm_examples} |
  | Wild animals | {wild_examples} |
  | Other animals | {other_examples} |
  | Actions | {action_examples} |
  | Food | {food_examples} |

  Good luck! Start with the translation exercises, then try the listening, speaking, and matching activities to reinforce your learning. 🔥
'''


for folder in ['EN-TO-SW', 'FR-TO-RW', 'FR-TO-SW', 'RW-TO-EN', 'RW-TO-FR', 'SW-TO-EN', 'SW-TO-FR']:
    folder_path = root / folder
    for path in sorted(folder_path.rglob('*.yaml')):
        if path.name != 'animals.yaml':
            continue
        text = path.read_text(encoding='utf-8')
        if 'guidebook: |' not in text:
            continue
        lesson_name = path.stem.replace('-', ' ').title()
        unit = int(path.parent.name.replace('level', ''))
        if folder == 'EN-TO-SW':
            block = build_markdown(folder, lesson_name, path.stem, unit)
        elif folder == 'FR-TO-RW':
            block = build_markdown(folder, lesson_name, path.stem, unit)
        else:
            block = build_markdown(folder, lesson_name, path.stem, unit)
        pattern = re.compile(r'guidebook:\s*\|.*?(?=^exercises:|\Z)', re.S | re.M)
        text, _ = pattern.subn(block, text, count=1)
        path.write_text(text, encoding='utf-8')
        print(f'updated {path}')
