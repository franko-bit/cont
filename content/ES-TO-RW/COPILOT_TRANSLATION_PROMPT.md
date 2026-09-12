You are translating the learner-facing content of a language course.

Target language: Spanish

Goal:
Translate all learner-facing English text in the listed YAML files into Spanish.

Hard rules:
- Preserve the YAML structure exactly (keys, order, indentation, comments).
- Do NOT translate these keys or their values:
  audio, audiofile, author, code, created, file, filename, href, id, image, imageurl, index, key, keys, kinyarwanda, lang, language, level, license, locale, order, path, rw, slug, type, updated, url, version, xp
- Do NOT translate URLs, emails, file names, or placeholders like {name} or $VAR.
- Do NOT translate Kinyarwanda text.
- Do NOT modify lesson IDs, XP values, paths, or audio/image references.
- If a string is already in Spanish, leave it unchanged.
- Translate only the values of learner-facing keys (titles, prompts, instructions,
  vocabulary entries, example sentences, explanations, answers).
- If a key has a list of strings, translate each item individually.

Output:
Write translated copies into the target folder, preserving the source tree layout.

Source root:
content\EN-TO-RW\EN-TO-RW

Target folder:
content\ES-TO-RW

Files to translate:
- level1\animals.yaml
- level1\colors.yaml
- level1\family.yaml
- level1\greetings.yaml
- level1\numbers.yaml
- level2\clothing.yaml
- level2\daily-routines.yaml
- level2\food.yaml
- level2\house.yaml
- level2\weather.yaml
- level3\directions.yaml
- level3\emotions.yaml
- level3\restaurant.yaml
- level3\shopping.yaml
- level3\travel.yaml
- level4\conditionals.yaml
- level4\future-tense.yaml
- level4\opinions.yaml
- level4\past-tense.yaml
- level4\storytelling.yaml
- level5\business.yaml
- level5\culture.yaml
- level5\days.yaml
- level5\debates.yaml
- level6\fluency.yaml
- level6\negotiations.yaml
