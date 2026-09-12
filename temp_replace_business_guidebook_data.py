from pathlib import Path

path = Path('content/EN-TO-RW/level5/business.yaml')
text = path.read_text(encoding='utf-8')
start = text.find('guidebook_data:')
end = text.find('\nexercises:', start)
if start == -1 or end == -1:
    raise SystemExit(f'Boundaries not found: start={start}, end={end}')
new_block = '''guidebook_data:
  header: "SECTION 5, UNIT 1 · Business - Ubucuruzi"
  intro: "Business vocabulary helps you talk about work, meetings, and professional settings in Kinyarwanda."
  vocabulary:
    - title: "💼 Business Vocabulary"
      sections:
        - category: "Company & People"
          rows:
            - ["Business", "Ubucuruzi"]
            - ["Company", "Sosiyete"]
            - ["Employee", "Umukozi"]
            - ["Employer", "Umukoresha"]
            - ["Boss / Manager", "Umuyobozi"]
            - ["Partner", "Umufatanyabikorwa"]
            - ["Client / Customer", "Umukiriya"]

        - category: "Work & Meetings"
          rows:
            - ["Work", "Akazi"]
            - ["Office", "Ibiro"]
            - ["Meeting", "Inama"]
            - ["Project", "Umushinga"]
            - ["Report", "Raporo"]
            - ["Plan", "Gahunda"]

        - category: "Products & Finance"
          rows:
            - ["Product", "Igicuruzwa"]
            - ["Service", "Serivisi"]
            - ["Market", "Isoko"]
            - ["Sales", "Kugurisha"]
            - ["Purchase", "Kugura"]
            - ["Profit", "Inyungu"]
            - ["Loss", "Igihombo"]
            - ["Investment", "Ishoramari"]
            - ["Budget", "Ingengo y'imari"]
            - ["Salary", "Umushahara"]

        - category: "Legal & Strategy"
          rows:
            - ["Contract", "Amasezerano"]
            - ["Negotiation", "Kugirana ibiganiro"]
            - ["Strategy", "Uburyo bwo gukora"]

  verbs:
    title: "🏃 Useful verbs"
    rows:
      - ["work", "gukora"]
      - ["discuss", "gukorana"]
      - ["plan", "gutegura"]
      - ["sign", "gushyira umukono"]
      - ["need", "gukeneye"]

  grammar:
    title: "📝 Grammar tips"
    tips:
      - heading: "Talking about people and roles"
        text: "Use short subject-verb-object phrases to describe business roles and duties."
        items:
          - "Mfite sosiyete. → I run a company."
          - "Dufite inama uyu munsi. → We have a meeting today."
      - heading: "Expressing success or results"
        text: "Describe business outcomes with verbs like 'yabonye' and 'yagize'."
        items:
          - "Sosiyete yabonye inyungu. → The company made a profit."
          - "Sosiyete yagize igihombo. → The company suffered a loss."
      - heading: "Asking about budget or plans"
        text: "Use a question structure with 'ni iyihe' to ask for details."
        items:
          - "Ingengo y'imari y'uyu mushinga ni iyihe? → What is the budget for this project?"

  pronunciation:
    title: "🔊 Pronunciation tip"
    tips:
      - heading: "Say it clearly"
        text: "Practice each word slowly and clearly. Repeat the most important business terms several times."
        items:
          - "Say 'Ubucuruzi' with the stress on the second syllable."
          - "Pronounce 'Amasezerano' slowly: A-ma-se-ze-ra-no."
          - "Say 'Turimo kugirana ibiganiro n'umukiriya' with a steady rhythm."

  key_phrases:
    title: "💬 Key phrases"
    rows:
      - ["I run a company", "Mfite sosiyete"]
      - ["We have a meeting today", "Dufite inama uyu munsi"]
      - ["The client is satisfied", "Umukiriya yishimye"]
      - ["The report is ready", "Raporo irarangiye"]
      - ["We need a strategy for sales", "Dukeneye uburyo bwo kugurisha"]
      - ["We are negotiating with the client", "Turimo kugirana ibiganiro n'umukiriya"]
      - ["What is the budget for this project?", "Ingengo y'imari y'uyu mushinga ni iyihe?"]

  unit_goal:
    title: "🎯 Unit goal"
    intro: "By the end of this unit, you will be able to use business vocabulary and simple professional phrases with confidence."
    goals:
      - "Recognize core business vocabulary"
      - "Describe meetings, products, and finance in Kinyarwanda"
      - "Ask and answer simple business questions"
      - "Use short, professional sentences correctly"
    xp_available: 500

  examples:
    title: "📝 Example sentences"
    items:
      - "Mfite sosiyete. → I run a company."
      - "Dufite inama uyu munsi. → We have a meeting today."
      - "Umukiriya yishimye. → The client is satisfied."
      - "Raporo irarangiye. → The report is ready."
      - "Twasinye amasezerano. → We signed the contract."

  quick_reference:
    title: "🔍 Quick reference"
    rows:
      - ["Business", "Ubucuruzi"]
      - ["Company", "Sosiyete"]
      - ["Work", "Akazi"]
      - ["Meeting", "Inama"]
      - ["Contract", "Amasezerano"]
      - ["Profit / Loss", "Inyungu / Igihombo"]

  footer: "Practice these business words and phrases regularly to build confidence for meetings, negotiations, and workplace conversations."
'''
new_text = text[:start] + new_block + text[end:]
path.write_text(new_text, encoding='utf-8')
print('updated', path)
