from pathlib import Path

path = Path(r'c:\xampp\htdocs\language-platform\content\EN-TO-RW\level2\house.yaml')
text = path.read_text(encoding='utf-8')
start = text.find('guidebook_data:')
end = text.find('\nguidebook: |', start)
if start == -1 or end == -1:
    raise RuntimeError('Could not find guidebook_data or guidebook marker in house.yaml')
replacement = '''guidebook_data:
  header: "SECTION 2, UNIT 2 · House in Kinyarwanda"
  intro: "Welcome to the House lesson! In this unit, you'll learn the names of rooms, furniture, and parts of a house in Kinyarwanda. You'll master essential vocabulary for describing your home, talking about where you live, and doing daily activities around the house. With 500 XP available, you'll be talking about your home in Kinyarwanda with confidence!"
  vocabulary:
    - title: "🏠 HOUSE VOCABULARY"
      sections:
        - category: "Core House Words"
          rows:
            - ["House", "Inzu", ""]
            - ["Home", "Urugo", ""]
            - ["Room", "Icyumba", ""]
            - ["Door", "Urugi", ""]
            - ["Window", "Idirishya", ""]
            - ["Roof", "Igisenge", ""]
            - ["Wall", "Urukuta", ""]
            - ["Floor", "Hasi", ""]
            - ["Key", "Urufunguzo", ""]
        - category: "Rooms (Ibyumba)"
          rows:
            - ["Bedroom", "Icyumba cyo kuraramo", "Literally 'room for sleeping'"]
            - ["Living room", "Icyumba cyo kwakiriramo", "Literally 'room for receiving guests'"]
            - ["Kitchen", "Igikoni", ""]
            - ["Bathroom", "Ubwiherero", ""]
            - ["Dining room", "Icyumba cyo kuriramo", "Literally 'room for eating'"]
        - category: "Furniture (Ibikoresho)"
          rows:
            - ["Bed", "Uburiri", ""]
            - ["Table", "Ameza", ""]
            - ["Chair", "Intebe", ""]
            - ["Sofa", "Sofa", "Borrowed word"]
            - ["Lamp", "Itara", ""]
            - ["Cupboard", "Akabati", ""]
  verbs:
    title: "🏃 Useful verbs"
    rows:
      - ["Enter", "Kwinjira", "Ninjira", "Awinjira"]
      - ["Leave / Go out", "Gusohoka", "Nsohoka", "Asohoka"]
      - ["Open", "Gufungura", "Mfunguza", "Afungura"]
      - ["Close", "Gufunga", "Mfunze", "Afunze"]
      - ["Clean", "Gusukura", "Nsukura", "Asukura"]
      - ["Sleep", "Gusinzira", "Nsinzira", "Asinzira"]
      - ["Sit", "Kwicara", "Nicara", "Aicara"]
      - ["Cook", "Guteka", "Nteka", "Ateka"]
  grammar:
    title: "📝 Grammar tips"
    rules:
      - heading: "I Have / Possession"
        pattern: "[Subject] + [have] + [house word]"
        examples:
          - ["Mfite inzu.", "I have a house."]
          - ["Mfite urugi.", "I have a door."]
          - ["Mfite ibyumba byinshi.", "I have many rooms."]
      - heading: "Describing the House"
        pattern: "[House/Room] + [adjective]"
        examples:
          - ["Inzu nini.", "The house is big."]
          - ["Inzu nziza.", "The house is nice."]
          - ["Icyumba gihumura.", "The room is clean."]
          - ["Igikoni nini.", "The kitchen is big."]
      - heading: "Location / Where"
        pattern: "[Subject] + [verb] + mu/ku + [place]"
        examples:
          - ["Nsinzira mu cyumba cyo kuraramo.", "I sleep in the bedroom."]
          - ["Nteka mu gikoni.", "I cook in the kitchen."]
          - ["Nicara ku ntebe.", "I sit on the chair."]
          - ["Nsohoka mu nzu.", "I leave the house."]
          - ["Ninjira mu nzu.", "I enter the house."]
      - heading: "Open / Close States"
        pattern: "[Door/Window] + [state verb]"
        examples:
          - ["Urugi rurafunguye.", "The door is open."]
          - ["Idirishya rirafunze.", "The window is closed."]
          - ["Urugi rurafunze.", "The door is closed."]
          - ["Idirishya rirafunguye.", "The window is open."]
      - heading: "Actions in the House"
        pattern: "[Subject] + [action] + [house part/object]"
        examples:
          - ["Mfunguza urugi.", "I open the door."]
          - ["Mfunze idirishya.", "I close the window."]
          - ["Nsukura inzu.", "I clean the house."]
          - ["Nteka ibiryo mu gikoni.", "I cook food in the kitchen."]
          - ["Umwana asinzira ku buriri.", "The child sleeps on the bed."]
  key_phrases:
    title: "💬 Key phrases"
    rows:
      - ["I have a house.", "Mfite inzu."]
      - ["The house is big.", "Inzu nini."]
      - ["The house is nice.", "Inzu nziza."]
      - ["The house has many rooms.", "Inzu ifite ibyumba byinshi."]
      - ["The room is clean.", "Icyumba gihumura."]
      - ["The door is open.", "Urugi rurafunguye."]
      - ["The window is closed.", "Idirishya rirafunze."]
      - ["I open the door.", "Mfunguza urugi."]
      - ["I close the window.", "Mfunze idirishya."]
      - ["I sleep in the bedroom.", "Nsinzira mu cyumba cyo kuraramo."]
      - ["I cook in the kitchen.", "Nteka mu gikoni."]
      - ["I sit on the chair.", "Nicara ku ntebe."]
      - ["I clean the house.", "Nsukura inzu."]
      - ["I cook food in the kitchen.", "Nteka ibiryo mu gikoni."]
      - ["The child sleeps on the bed.", "Umwana asinzira ku buriri."]
      - ["I enter the house.", "Ninjira mu nzu."]
      - ["I leave the house.", "Nsohoka mu nzu."]
  quick_reference:
    title: "🔄 Quick reference"
    rows:
      - ["House", "Inzu"]
      - ["Home", "Urugo"]
      - ["Room", "Icyumba"]
      - ["Door", "Urugi"]
      - ["Window", "Idirishya"]
      - ["Roof", "Igisenge"]
      - ["Wall", "Urukuta"]
      - ["Floor", "Hasi"]
      - ["Key", "Urufunguzo"]
      - ["Bedroom", "Icyumba cyo kuraramo"]
      - ["Living room", "Icyumba cyo kwakiriramo"]
      - ["Kitchen", "Igikoni"]
      - ["Bathroom", "Ubwiherero"]
      - ["Dining room", "Icyumba cyo kuriramo"]
  unit_goal:
    title: "🎯 Unit goal"
    goals:
      - "Name the main house parts and rooms in Kinyarwanda."
      - "Describe your home with simple adjectives."
      - "Talk about where you live and what you do around the house."
      - "Use open/close, possessive, and location phrases correctly."
    xp_available: "Total XP Available: 500 XP"
'''
new_text = text[:start] + replacement + text[end:]
path.write_text(new_text, encoding='utf-8')
print('Updated house.yaml')
