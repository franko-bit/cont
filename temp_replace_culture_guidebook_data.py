from pathlib import Path

path = Path('content/EN-TO-RW/level5/culture.yaml')
text = path.read_text(encoding='utf-8')
start = text.find('guidebook_data:')
end = text.find('\nexercises:', start)
if start == -1 or end == -1:
    raise SystemExit(f'Boundaries not found: start={start}, end={end}')
new_block = '''guidebook_data:
  header: "SECTION 5, UNIT 5 · Culture - Umuco"
  intro: "Explore core vocabulary and cultural concepts in Kinyarwanda — traditions, ceremonies, arts, and everyday cultural terms."

  vocabulary:
    - title: "🎭 Culture Vocabulary"
      sections:
        - category: "Core Cultural Terms"
          rows:
            - ["Culture", "Umuco"]
            - ["Tradition", "Umugenzo"]
            - ["Custom", "Imigenzo"]
            - ["Ceremony", "Umuhango"]
            - ["Festival", "Igitaramo / Umunsi mukuru"]
            - ["Heritage", "Umurage"]
            - ["Language", "Ururimi"]
            - ["Community", "Umuryango / Abaturage"]
            - ["Family", "Umuryango"]
            - ["Belief", "Icyizere / Icyemera"]

        - category: "Arts & Expression"
          rows:
            - ["Dance", "Imbyino"]
            - ["Music", "Umuziki"]
            - ["Song", "Indirimbo"]
            - ["Story", "Inkuru"]
            - ["Proverb", "Imigani"]
            - ["Art", "Ubuhanzi"]
            - ["Painting", "Igishushanyo"]
            - ["Sculpture", "Igishushanyo mbonera"]
            - ["Traditional clothing", "Imyambaro gakondo"]

        - category: "Rituals & Religion"
          rows:
            - ["Ritual", "Umuhango gakondo"]
            - ["Religion", "Idini"]
            - ["Greeting", "Guhura / Muraho"]

  grammar:
    title: "📝 Grammar tips"
    tips:
      - heading: "Rule 1: Describing Cultural Elements"
        text: "Use a simple noun plus description pattern to describe cultural things."
        items:
          - "Imbyino gakondo irakomeye cyane. → Traditional dance is very beautiful."
          - "Umurage wacu ni ingenzi. → Our heritage is important."
      - heading: "Rule 2: Expressing Frequency"
        text: "Use subject + verb + time expression to talk about how often cultural activities happen."
        items:
          - "Twizihiza umunsi mukuru buri mwaka. → We celebrate the festival every year."
      - heading: "Rule 3: Describing Actions"
        text: "Use subject + verb + object + location to describe cultural actions."
        items:
          - "Abantu bavuga inkuru hafi y'umuriro. → People tell stories around the fire."
      - heading: "Rule 4: Stating Functions"
        text: "Use subject + verb + purpose to explain why cultural elements exist."
        items:
          - "Imigani yigisha amasomo. → Proverbs teach lessons."
      - heading: "Rule 5: Stating Belonging"
        text: "Use 'ni igice cy'' plus a cultural aspect to show belonging."
        items:
          - "Ubuhanzi ni igice cy'umuco. → Art is part of culture."
      - heading: "Rule 6: Describing Connections"
        text: "Use subject + verb + object to show how culture connects people."
        items:
          - "Ururimi ruhuza abantu. → Language connects people."
      - heading: "Rule 7: Describing Practices"
        text: "Use subject + verb to describe cultural practices and respect."
        items:
          - "Imihango y'idini irubahirizwa. → Religious rituals are respected."
      - heading: "Rule 8: Stating Inclusivity"
        text: "Use elements + ni igice cy' plus celebration to explain inclusive cultural events."
        items:
          - "Umuziki n'imbyino ni igice cy'ibirori byose. → Music and dance are part of every celebration."

  key_phrases:
    title: "💬 Key phrases"
    rows:
      - ["Traditional dance is very beautiful.", "Imbyino gakondo irakomeye cyane."]
      - ["Our heritage is important.", "Umurage wacu ni ingenzi."]
      - ["Art is part of culture.", "Ubuhanzi ni igice cy'umuco."]
      - ["Language connects people.", "Ururimi ruhuza abantu."]
      - ["Clothing shows tradition.", "Imyambaro igaragaza umugenzo."]
      - ["Proverbs teach lessons.", "Imigani yigisha amasomo."]
      - ["Religious rituals are respected.", "Imihango y'idini irubahirizwa."]
      - ["We celebrate the festival every year.", "Twizihiza umunsi mukuru buri mwaka."]
      - ["People tell stories around the fire.", "Abantu bavuga inkuru hafi y'umuriro."]
      - ["Music and dance are part of every celebration.", "Umuziki n'imbyino ni igice cy'ibirori byose."]

  unit_goal:
    title: "🎯 Unit goal"
    intro: "By the end of this lesson, you will be able to use culture vocabulary and describe Rwandan traditions with confidence."
    goals:
      - "Use 25+ culture words in Kinyarwanda."
      - "Describe cultural elements clearly."
      - "Talk about celebrations and rituals."
      - "Express heritage and language connections."
    xp_available: 500

  examples:
    title: "📝 Sample dialogues"
    items:
      - "A: Umuco ni iki? / What is culture?\nB: Umuco ni imigenzo, imbyino, umuziki, n'ubuhanzi. / Culture is customs, dance, music, and art.\nA: Imbyino gakondo irakomeye cyane. / Traditional dance is very beautiful.\nB: Yego, imbyino gakondo ni nziza. / Yes, traditional dance is beautiful.\nA: Umuziki n'imbyino ni igice cy'ibirori byose. / Music and dance are part of every celebration."
      - "A: Umurage wacu ni ingenzi. / Our heritage is important.\nB: Yego, tuzagumana umurage wacu. / Yes, we will preserve our heritage.\nA: Imyambaro igaragaza umugenzo. / Clothing shows tradition.\nB: Umugenzo wacu ni mwiza. / Our tradition is beautiful.\nA: Imigani yigisha amasomo. / Proverbs teach lessons.\nB: Yego, imigani ni ingenzi mu mico yacu. / Yes, proverbs are important in our culture."
      - "A: Twizihiza umunsi mukuru buri mwaka. / We celebrate the festival every year.\nB: Abantu bavuga inkuru hafi y'umuriro? / Do people tell stories around the fire?\nA: Yego, abantu bavuga inkuru hafi y'umuriro. / Yes, people tell stories around the fire.\nB: Indirimbo n'imbyino ni byiza. / Songs and dances are beautiful.\nA: Yego, ni igice cy'umuco wacu. / Yes, they are part of our culture."
      - "A: Ururimi ruhuza abantu. / Language connects people.\nB: Yego, ururimi rwacu ni ingenzi. / Yes, our language is important.\nA: Idini n'imihango ni igice cy'umuco. / Religion and rituals are part of culture.\nB: Imihango y'idini irubahirizwa. / Religious rituals are respected.\nA: Ubuhanzi ni igice cy'umuco. / Art is part of culture.\nB: Yego, ubuhanzi bugaragaza umuco wacu. / Yes, art shows our culture."

  quick_reference:
    title: "🔄 Quick reference"
    rows:
      - ["Culture", "Umuco"]
      - ["Tradition", "Umugenzo"]
      - ["Custom", "Imigenzo"]
      - ["Ceremony", "Umuhango"]
      - ["Festival", "Umunsi mukuru"]
      - ["Heritage", "Umurage"]
      - ["Language", "Ururimi"]
      - ["Community", "Umuryango"]
      - ["Family", "Umuryango"]
      - ["Belief", "Icyizere"]
      - ["Dance", "Imbyino"]
      - ["Music", "Umuziki"]
      - ["Song", "Indirimbo"]
      - ["Story", "Inkuru"]
      - ["Proverb", "Imigani"]
      - ["Art", "Ubuhanzi"]
      - ["Painting", "Igishushanyo"]
      - ["Sculpture", "Igishushanyo mbonera"]
      - ["Traditional clothing", "Imyambaro gakondo"]
      - ["Ritual", "Umuhango gakondo"]
      - ["Religion", "Idini"]
      - ["Greeting", "Guhura / Muraho"]

  footer: "Practice using cultural words, phrases, and stories to discuss traditions, community life, and heritage in Kinyarwanda."
'''
new_text = text[:start] + new_block + text[end:]
path.write_text(new_text, encoding='utf-8')
print('updated', path)
