from pathlib import Path

path = Path('content/EN-TO-RW/level5/days.yaml')
text = path.read_text(encoding='utf-8')
start = text.find('guidebook_data:')
end = text.find('\nexercises:', start)
if start == -1 or end == -1:
    raise SystemExit(f'Boundaries not found: start={start}, end={end}')
new_block = '''guidebook_data:
  header: "SECTION 5, UNIT 5 · Days & Times - Iminsi n'Isaha"
  intro: "Learn how to talk about days of the week, time expressions, and daily routines in Kinyarwanda."

  vocabulary:
    - title: "📅 Days of the Week"
      sections:
        - category: "Days of the Week"
          rows:
            - ["Monday", "Ku wa Mbere"]
            - ["Tuesday", "Ku wa Kabiri"]
            - ["Wednesday", "Ku wa Gatatu"]
            - ["Thursday", "Ku wa Kane"]
            - ["Friday", "Ku wa Gatanu"]
            - ["Saturday", "Ku wa Gatandatu"]
            - ["Sunday", "Ku Cyumweru"]

    - title: "⏰ Time Vocabulary"
      sections:
        - category: "Time Units"
          rows:
            - ["Hour / o'clock", "Isaha"]
            - ["Minute", "Umunota"]
            - ["Second", "Akanya"]
            - ["Week", "Icyumweru"]
            - ["Weekend", "Icyumweru kirangira / Iminsi y'ikiruhuko"]
            - ["Month", "Ukwezi"]
            - ["Year", "Umwaka"]
            - ["Day", "Umunsi"]

        - category: "Parts of the Day"
          rows:
            - ["Morning", "Mu gitondo"]
            - ["Afternoon", "Nyuma ya saa sita / Nyuma ya saa sita z'umunsi"]
            - ["Evening", "Nimugoroba"]
            - ["Night", "Ijoro"]
            - ["Noon / Midday", "Saa sita z'amanywa"]
            - ["Midnight", "Saa sita z'ijoro"]

        - category: "Relative Time"
          rows:
            - ["Today", "Uyu munsi"]
            - ["Tomorrow", "Ejo"]
            - ["Yesterday", "Ejo hashize"]

  grammar:
    title: "📝 Grammar tips"
    tips:
      - heading: "Rule 1: Asking About the Day"
        text: "Use 'Ni uyu munsi w'iki?' to ask what day it is today."
        items:
          - "Ni uyu munsi w'iki? → What day is it today?"
      - heading: "Rule 2: Stating the Day"
        text: "Use [Today/Tomorrow/Yesterday] + ni + [day] to say the day."
        items:
          - "Uyu munsi ni Ku wa Mbere. → Today is Monday."
          - "Ejo ni Ku wa Kabiri. → Tomorrow is Tuesday."
          - "Ejo hashize ni Ku Cyumweru. → Yesterday was Sunday."
      - heading: "Rule 3: Asking About Time"
        text: "Use 'Saa ngahe?' to ask what time it is."
        items:
          - "Saa ngahe? → What time is it?"
      - heading: "Rule 4: Stating the Time"
        text: "Use 'Ni isaha + [time]' to say the current time."
        items:
          - "Ni isaha saa moya. → It is 7 o'clock."
      - heading: "Rule 5: Describing When Something Happens"
        text: "Use [Time of day] + [action] to describe when events happen."
        items:
          - "Mu gitondo hatangira saa kumi n'ebyiri. → Morning starts at 6."
          - "Nyuma ya saa sita z'amanywa. → Afternoon starts at 12."
          - "Nimugoroba hatangira saa kumi n'ebyiri z'umugoroba. → Evening starts at 6."
      - heading: "Rule 6: Describing Routine"
        text: "Use [Subject] + [verb] + [time] + [frequency] for repeated actions."
        items:
          - "Ngaruka ku isaha ya saa kumi n'ebyiri buri gitondo. → I wake up at 6 every morning."
          - "Njyana kuryama saa tatu z'ijoro. → I go to bed at 9 at night."
      - heading: "Rule 7: Describing States"
        text: "Use [Subject] + [adjective] to describe a condition or state."
        items:
          - "Ijoro rituje. → Night is quiet."

  key_phrases:
    title: "💬 Key phrases"
    rows:
      - ["What day is it today?", "Ni uyu munsi w'iki?"]
      - ["Today is Monday.", "Uyu munsi ni Ku wa Mbere."]
      - ["Tomorrow is Tuesday.", "Ejo ni Ku wa Kabiri."]
      - ["Yesterday was Sunday.", "Ejo hashize ni Ku Cyumweru."]
      - ["What time is it?", "Saa ngahe?"]
      - ["It is 7 o'clock.", "Ni isaha saa moya."]
      - ["Morning starts at 6.", "Mu gitondo hatangira saa kumi n'ebyiri."]
      - ["Afternoon starts at 12.", "Nyuma ya saa sita z'amanywa."]
      - ["Evening starts at 6.", "Nimugoroba hatangira saa kumi n'ebyiri z'umugoroba."]
      - ["I wake up at 6 every morning.", "Ngaruka ku isaha ya saa kumi n'ebyiri buri gitondo."]
      - ["I go to bed at 9 at night.", "Njyana kuryama saa tatu z'ijoro."]
      - ["Night is quiet.", "Ijoro rituje."]

  time_system:
    title: "🕐 Time System in Kinyarwanda"
    rows:
      - ["6:00 AM", "Saa kumi n'ebyiri (12)"]
      - ["7:00 AM", "Saa moya (1)"]
      - ["8:00 AM", "Saa mbiri (2)"]
      - ["9:00 AM", "Saa tatu (3)"]
      - ["10:00 AM", "Saa kane (4)"]
      - ["11:00 AM", "Saa gatanu (5)"]
      - ["12:00 PM", "Saa sita (6)"]
      - ["1:00 PM", "Saa saba (7)"]
      - ["2:00 PM", "Saa munani (8)"]
      - ["3:00 PM", "Saa cyenda (9)"]
      - ["4:00 PM", "Saa icumi (10)"]
      - ["5:00 PM", "Saa kumi na rimwe (11)"]
      - ["6:00 PM", "Saa kumi n'ebyiri (12)"]

  examples:
    title: "📚 Sample dialogues"
    items:
      - "A: Ni uyu munsi w'iki? / What day is it today?\nB: Uyu munsi ni Ku wa Mbere. / Today is Monday.\nA: Ejo ni Ku wa Kabiri? / Is tomorrow Tuesday?\nB: Yego, ejo ni Ku wa Kabiri. / Yes, tomorrow is Tuesday."
      - "A: Saa ngahe? / What time is it?\nB: Ni isaha saa moya. / It is 7 o'clock.\nA: Mu gitondo hatangira ryari? / When does morning start?\nB: Mu gitondo hatangira saa kumi n'ebyiri. / Morning starts at 6."
      - "A: Ugaruka ryari buri gitondo? / What time do you wake up every morning?\nB: Ngaruka ku isaha ya saa kumi n'ebyiri buri gitondo. / I wake up at 6 every morning.\nA: Ujyana kuryama ryari? / What time do you go to bed?\nB: Njyana kuryama saa tatu z'ijoro. / I go to bed at 9 at night."
      - "A: Uyu munsi ni Ku wa Gatatu? / Is today Wednesday?\nB: Oya, uyu munsi ni Ku wa Kabiri. / No, today is Tuesday.\nA: Ejo hashize ni Ku wa Mbere? / Was yesterday Monday?\nB: Yego, ejo hashize ni Ku wa Mbere. / Yes, yesterday was Monday."

  quick_reference:
    title: "🔄 Quick reference"
    rows:
      - ["Monday", "Ku wa Mbere"]
      - ["Tuesday", "Ku wa Kabiri"]
      - ["Wednesday", "Ku wa Gatatu"]
      - ["Thursday", "Ku wa Kane"]
      - ["Friday", "Ku wa Gatanu"]
      - ["Saturday", "Ku wa Gatandatu"]
      - ["Sunday", "Ku Cyumweru"]
      - ["Today", "Uyu munsi"]
      - ["Tomorrow", "Ejo"]
      - ["Yesterday", "Ejo hashize"]
      - ["Morning", "Mu gitondo"]
      - ["Evening", "Nimugoroba"]
      - ["Night", "Ijoro"]
      - ["Hour", "Isaha"]
      - ["Minute", "Umunota"]
      - ["Second", "Akanya"]
      - ["Week", "Icyumweru"]
      - ["Month", "Ukwezi"]
      - ["Year", "Umwaka"]

  footer: "Practice talking about days, times, and daily routines to build confidence in Kinyarwanda."
'''
new_text = text[:start] + new_block + text[end:]
path.write_text(new_text, encoding='utf-8')
print('updated', path)
