from pathlib import Path

path = Path(r'c:\xampp\htdocs\language-platform\content\EN-TO-RW\level2\food.yaml')
text = path.read_text(encoding='utf-8')
start = text.find('  guidebook_data:')
end = text.find('\n  guidebook: |', start)
if start == -1 or end == -1:
    raise RuntimeError('Could not find guidebook_data or guidebook marker in food.yaml')
replacement = '''  guidebook_data:
    header: "SECTION 2, UNIT 2 · Food"
    intro: "Welcome to the Food lesson! In this unit, you'll learn the names of foods in Kinyarwanda — from staples to fruits and vegetables. You'll also practice useful food verbs, shopping phrases, and meal vocabulary. With 500 XP available, you'll be talking about food in Kinyarwanda with confidence!"
    vocabulary:
      - title: "🍽️ Food Vocabulary"
        sections:
          - category: "Core Foods"
            rows:
              - ["Food", "Ibiryo", "General term"]
              - ["Water", "Amazi", ""]
              - ["Bread", "Umugati", ""]
              - ["Rice", "Umuceri", ""]
              - ["Beans", "Ibishyimbo", ""]
              - ["Meat", "Inyama", ""]
              - ["Fish", "Ifi", ""]
              - ["Milk", "Amata", ""]
              - ["Egg", "Igi", ""]
              - ["Eggs", "Amagi", ""]
          - category: "Fruits (Imbuto)"
            rows:
              - ["Fruit", "Imbuto", "General term"]
              - ["Banana", "Igitoki", ""]
              - ["Orange", "Icunga", ""]
              - ["Mango", "Umwembe", ""]
              - ["Pineapple", "Inanasi", ""]
              - ["Papaya", "Ipapa", ""]
              - ["Avocado", "Avoka", "Borrowed word"]
          - category: "Vegetables (Imboga)"
            rows:
              - ["Vegetable", "Imboga", "General term"]
              - ["Potato", "Ibirayi", ""]
              - ["Sweet potato", "Ibijumba", ""]
              - ["Tomato", "Inyanya", ""]
              - ["Onion", "Igitunguru", ""]
              - ["Carrot", "Karoti", "Borrowed word"]
              - ["Cabbage", "Amashu", ""]
          - category: "Meals"
            rows:
              - ["Breakfast", "Ifunguro rya mu gitondo", "Morning meal"]
              - ["Lunch", "Ifunguro rya saa sita", "Midday meal"]
              - ["Dinner", "Ifunguro rya nimugoroba", "Evening meal"]
    verbs:
      title: "🏃 Useful verbs"
      rows:
        - ["Eat", "Kurya", "Ndarya", "Ararya"]
        - ["Drink", "Kunywa", "Nnywa", "Anywa"]
        - ["Cook", "Guteka", "Nteka", "Ateka"]
        - ["Want", "Gushaka", "Ndashaka", "Ashaka"]
        - ["Buy", "Kugura", "Naguze", "Yaguze"]
    grammar:
      title: "📝 Grammar tips"
      rules:
        - heading: "I Eat / I Drink Statements"
          pattern: "[Subject prefix] + [verb stem] + [food item]"
          examples:
            - ["Ndarya ibiryo.", "I eat food."]
            - ["Ndarya umuceri.", "I eat rice."]
            - ["Nnywa amazi.", "I drink water."]
            - ["Ndarya igitoki.", "I eat a banana."]
        - heading: "She/He Statements"
          pattern: "[Subject prefix] + [verb stem] + [food item]"
          examples:
            - ["Ararya ibishyimbo.", "She eats beans."]
            - ["Anywa amata.", "He drinks milk."]
            - ["Ararya imbuto.", "She eats fruit."]
        - heading: "We / They Statements"
          pattern: "[Subject prefix] + [verb stem] + [food item]"
          examples:
            - ["Turya ibirayi.", "We eat potatoes."]
            - ["Bateka ibiryo.", "They cook food."]
        - heading: "Expressing Desire (Want)"
          pattern: "[Subject prefix] + [shaka] + [food item]"
          examples:
            - ["Ndashaka ibiryo.", "I want food."]
            - ["Ndashaka umuceri.", "I want rice."]
            - ["Ndashaka umugati.", "I want bread."]
        - heading: "Asking About Price"
          pattern: "[This item] + ni angahe?"
          examples:
            - ["Iki gitoki ni angahe?", "How much is this banana?"]
            - ["Uyu muceri ni angahe?", "How much is this rice?"]
            - ["Uyu mugati ni angahe?", "How much is this bread?"]
        - heading: "Stating Prices"
          pattern: "[Item] + igura + amafaranga + [amount]"
          examples:
            - ["Uyu muceri ugura amafaranga igihumbi.", "This rice costs 1,000 francs."]
            - ["Iki gitoki ni amafaranga ijana.", "This banana is 100 francs."]
    key_phrases:
      title: "💬 Key phrases"
      rows:
        - ["I eat food.", "Ndarya ibiryo."]
        - ["I eat rice.", "Ndarya umuceri."]
        - ["I eat a banana.", "Ndarya igitoki."]
        - ["I drink water.", "Nnywa amazi."]
        - ["She eats beans.", "Ararya ibishyimbo."]
        - ["He drinks milk.", "Anywa amata."]
        - ["She eats fruit.", "Ararya imbuto."]
        - ["We eat potatoes.", "Turya ibirayi."]
        - ["They cook food.", "Bateka ibiryo."]
        - ["I want food.", "Ndashaka ibiryo."]
        - ["I want rice.", "Ndashaka umuceri."]
        - ["I bought bread.", "Naguze umugati."]
        - ["How much is this banana?", "Iki gitoki ni angahe?"]
        - ["How much is this rice?", "Uyu muceri ni angahe?"]
        - ["This rice costs 1,000 francs.", "Uyu muceri ugura amafaranga igihumbi."]
        - ["I eat breakfast.", "Ndarya ifunguro rya mu gitondo."]
        - ["I eat lunch.", "Ndarya ifunguro rya saa sita."]
        - ["I eat dinner.", "Ndarya ifunguro rya nimugoroba."]
    quick_reference:
      title: "🔄 Quick reference"
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
        - ["Fruit", "Imbuto"]
        - ["Banana", "Igitoki"]
        - ["Tomato", "Inyanya"]
    unit_goal:
      title: "🎯 Unit goal"
      goals:
        - "Name 30+ foods in Kinyarwanda, including staples, fruits, and vegetables."
        - "Say 'I eat...' and 'I drink...' with food items."
        - "Say 'I want...' when shopping for food."
        - "Ask 'How much is this...?' for food items."
        - "Say prices in Kinyarwanda."
      xp_available: "Total XP Available: 500 XP"
'''
new_text = text[:start] + replacement + text[end:]
path.write_text(new_text, encoding='utf-8')
print('Updated food.yaml')
