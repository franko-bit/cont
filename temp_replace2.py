from pathlib import Path

replacements = {
    Path(r'c:\xampp\htdocs\language-platform\content\EN-TO-RW\level2\daily-routines.yaml'): '''  guidebook_data:
  header: "SECTION 2, UNIT 2 · Daily Routines"
  intro: "Welcome to the Daily Routines lesson! In this unit, you'll learn how to talk about your everyday activities in Kinyarwanda. You'll practice useful verbs, time phrases, and simple sentence patterns for talking about routines."
  vocabulary:
    - title: "⏰ Daily Routine Vocabulary"
      sections:
        - category: "Basic Verbs"
          rows:
            - ["Wake up", "Kubyuka"]
            - ["Sleep", "Gusinzira"]
            - ["Eat", "Kurya"]
            - ["Drink", "Kunywa"]
            - ["Work", "Gukora"]
            - ["Study", "Kwiga"]
            - ["Walk", "Kugenda"]
            - ["Run", "Kwiruka"]
            - ["Cook", "Guteka"]
            - ["Clean", "Gusukura"]
            - ["Write", "Kwandika"]
            - ["Listen", "Kumva"]
            - ["Speak", "Kuvuga"]
            - ["Rest", "Kuruhuka"]
        - category: "Daily Hygiene"
          rows:
            - ["Brush teeth", "Koza amenyo"]
            - ["Wash hands", "Gukaraba intoki"]
            - ["Wash face", "Gukaraba mu maso"]
            - ["Take a shower", "Koga"]
            - ["Take a bath", "Koga"]
        - category: "Daily Activities"
          rows:
            - ["Go to school", "Kujya ku ishuri"]
            - ["Go to work", "Kujya ku kazi"]
            - ["Go home", "Kujya iwanyu / Kujya mu rugo"]
            - ["Read a book", "Gusoma igitabo"]
            - ["Talk with family", "Kuvugana n'umuryango"]
        - category: "Meal Times"
          rows:
            - ["Breakfast", "Ifunguro rya mu gitondo"]
            - ["Lunch", "Ifunguro rya saa sita"]
            - ["Dinner", "Ifunguro rya nimugoroba"]
            - ["Food", "Ibiryo / Ifunguro"]
  verbs:
    title: "🏃 Useful verbs"
    rows:
      - ["I wake up", "Mbyuka"]
      - ["I brush", "Nkoza"]
      - ["I eat", "Ndarya"]
      - ["I go", "Njya"]
      - ["I study", "Niga"]
      - ["She/He goes", "Ajya"]
      - ["She/He eats", "Ararya"]
      - ["She/He works", "Akora"]
      - ["We study", "Twiga"]
      - ["They work", "Bakora"]
  grammar:
    title: "📝 Grammar: Daily Routine Sentences"
    tips:
      - heading: "Present tense pattern"
        text: "Use a subject prefix followed by the verb stem and an optional object or time phrase."
        examples:
          - "Mbyuka mu gitondo. → I wake up in the morning."
          - "Nkoza amenyo. → I brush my teeth."
          - "Ndarya ifunguro rya mu gitondo. → I eat breakfast."
      - heading: "Question formation"
        text: "Put 'iki' after the verb to ask what someone does."
        examples:
          - "Ukora iki mu gitondo? → What do you do in the morning?"
          - "Ukora iki nijoro? → What do you do at night?"
      - heading: "Connecting actions"
        text: "Use 'nka' or connect verbs directly to say 'and'."
        examples:
          - "Mbyuka nkajya ku ishuri. → I wake up and go to school."
          - "Nkoza amenyo nkaraba mu maso. → I brush my teeth and wash my face."
  key_phrases:
    title: "💬 Key phrases"
    rows:
      - ["I wake up in the morning.", "Mbyuka mu gitondo."]
      - ["I wake up early.", "Mbyuka kare."]
      - ["I brush my teeth.", "Nkoza amenyo."]
      - ["I wash my face.", "Nkaraba mu maso."]
      - ["I take a shower.", "Nkoga."]
      - ["I eat breakfast.", "Ndarya ifunguro rya mu gitondo."]
      - ["I go to school.", "Njya ku ishuri."]
      - ["I study in the afternoon.", "Niga nyuma ya saa sita."]
      - ["I walk with my friends.", "Ngenda n'inshuti zanjye."]
      - ["I sleep at night.", "Nsiga nijoro."]
  quick_reference:
    title: "🔄 Quick reference"
    rows:
      - ["Wake up", "Mbyuka"]
      - ["Eat", "Ndarya"]
      - ["Go", "Njya"]
      - ["Study", "Niga"]
      - ["Sleep", "Nsiga"]
  unit_goal:
    title: "🎯 Unit goal"
    goals:
      - "Name daily routine verbs and time phrases in Kinyarwanda"
      - "Describe morning, afternoon, and evening activities"
      - "Form simple present sentences and questions"
    xp_available: "Total XP Available: 500 XP"
''',
    Path(r'c:\xampp\htdocs\language-platform\content\EN-TO-RW\level2\food.yaml'): '''  guidebook_data:
  header: "SECTION 2, UNIT 2 · Food"
  intro: "Welcome to the Food lesson! In this unit, you'll learn the names of foods in Kinyarwanda — from staples to fruits and vegetables. You'll also practice useful food verbs, shopping phrases, and meal vocabulary."
  vocabulary:
    - title: "🍽️ Food Vocabulary"
      sections:
        - category: "Core Foods"
          rows:
            - ["Food", "Ibiryo"]
            - ["Water", "Amazi"]
            - ["Bread", "Umugati"]
            - ["Rice", "Umuceri"]
            - ["Beans", "Ibishyimbo"]
            - ["Meat", "Inyama"]
            - ["Fish", "Ifi"]
            - ["Milk", "Amata"]
            - ["Egg", "Igi"]
            - ["Eggs", "Amagi"]
        - category: "Fruits"
          rows:
            - ["Fruit", "Imbuto"]
            - ["Banana", "Igitoki"]
            - ["Orange", "Icunga"]
            - ["Mango", "Umwembe"]
            - ["Pineapple", "Inanasi"]
            - ["Papaya", "Ipapa"]
            - ["Avocado", "Avoka"]
        - category: "Vegetables"
          rows:
            - ["Vegetable", "Imboga"]
            - ["Potato", "Ibirayi"]
            - ["Sweet potato", "Ibijumba"]
            - ["Tomato", "Inyanya"]
            - ["Onion", "Igitunguru"]
            - ["Carrot", "Karoti"]
            - ["Cabbage", "Amashu"]
        - category: "Meals"
          rows:
            - ["Breakfast", "Ifunguro rya mu gitondo"]
            - ["Lunch", "Ifunguro rya saa sita"]
            - ["Dinner", "Ifunguro rya nimugoroba"]
  verbs:
    title: "🏃 Useful verbs"
    rows:
      - ["Eat", "Kurya", "Ndarya"]
      - ["Drink", "Kunywa", "Nnywa"]
      - ["Cook", "Guteka", "Nteka"]
      - ["Want", "Gushaka", "Ndashaka"]
      - ["Buy", "Kugura", "Naguze"]
  grammar:
    title: "📝 Grammar tips"
    tips:
      - heading: "I eat / I drink"
        text: "Use the present verb and the food item to say what you eat or drink."
        examples:
          - "Ndarya umuceri. → I eat rice."
          - "Nnywa amazi. → I drink water."
      - heading: "She/He statements"
        text: "Use the third-person prefix to talk about what someone else eats or drinks."
        examples:
          - "Ararya ibishyimbo. → She eats beans."
          - "Anywa amata. → He drinks milk."
      - heading: "Shopping phrases"
        text: "Ask price with 'ni angahe?' and state cost with 'igura amafaranga'."
        examples:
          - "Iki gitoki ni angahe? → How much is this banana?"
          - "Uyu muceri ugura amafaranga igihumbi. → This rice costs 1,000 francs."
  key_phrases:
    title: "💬 Key phrases"
    rows:
      - ["I eat food.", "Ndarya ibiryo."]
      - ["I eat rice.", "Ndarya umuceri."]
      - ["I eat a banana.", "Ndarya igitoki."]
      - ["I drink water.", "Nnywa amazi."]
      - ["She eats beans.", "Ararya ibishyimbo."]
      - ["He drinks milk.", "Anywa amata."]
      - ["I want rice.", "Ndashaka umuceri."]
      - ["How much is this banana?", "Iki gitoki ni angahe?"]
      - ["This rice costs 1,000 francs.", "Uyu muceri ugura amafaranga igihumbi."]
  quick_reference:
    title: "🔄 Quick reference"
    rows:
      - ["Rice", "Umuceri"]
      - ["Banana", "Igitoki"]
      - ["Bread", "Umugati"]
      - ["Beans", "Ibishyimbo"]
      - ["Drink water", "Nnywa amazi"]
  unit_goal:
    title: "🎯 Unit goal"
    goals:
      - "Name common foods in Kinyarwanda"
      - "Use eating and shopping phrases"
      - "Ask and say prices for food items"
    xp_available: "Total XP Available: 500 XP"
'''
}
for path, new_block in replacements.items():
    text = path.read_text(encoding='utf-8')
    start = text.find('  guidebook_data: |')
    end = text.find('\n  guidebook: |', start)
    if start == -1 or end == -1:
        raise RuntimeError(f'Marker not found in {path}')
    text = text[:start] + new_block + text[end+1:]
    path.write_text(text, encoding='utf-8')
    print(f'Updated {path}')
