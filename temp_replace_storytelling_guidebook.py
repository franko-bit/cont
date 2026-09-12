from pathlib import Path

path = Path('content/EN-TO-RW/level4/storytelling.yaml')
text = path.read_text(encoding='utf-8')
start = text.find('guidebook: |')
end = text.find('\nexercises:', start)
if start == -1 or end == -1:
    raise SystemExit(f'Boundaries not found: start={start}, end={end}')
new_block = '''guidebook: |

  # 📘 Guidebook for Storytelling - Kuvuga Inkuru mu Kinyarwanda

  ## 📖 Overview

  Welcome to the Storytelling lesson! In this unit, you'll learn how to tell stories in Kinyarwanda - from classic "once upon a time" openings to describing characters, places, and actions. You'll master essential vocabulary for narrative structure and be able to share your own stories. With **500 XP** available, you'll be telling stories in Kinyarwanda with confidence!

  ---

  ## 📖 STORYTELLING VOCABULARY

  ### Story Openings

  | English | Kinyarwanda |
  |---------|-------------|
  | Once upon a time | **Cyera Habayeho** |
  | Long ago | **Hashize igihe kirekire** |
  | There was / There were | **Hariho** |
  | Lived | **Yabayeho** |

  ---

  ### Places

  | English | Kinyarwanda |
  |---------|-------------|
  | In a village | **Mu mudugudu** |
  | In a town | **Mu mujyi** |
  | In a house | **Mu nzu** |
  | Forest | **Ishyamba** |
  | Mountain | **Umusozi** |
  | River | **Umugezi** |

  ---

  ### Characters

  | English | Kinyarwanda |
  |---------|-------------|
  | A boy | **Umuhungu** |
  | A girl | **Umukobwa** |
  | Mother | **Mama** |
  | Father | **Data** |
  | Friend | **Inshuti** |
  | Animals | **Inyamaswa** |

  ---

  ### Actions (Past Tense)

  | English | Kinyarwanda |
  |---------|-------------|
  | Went to | **Yagiye** |
  | Saw | **Yabonye** |
  | Met | **Yahuye na** |
  | Helped | **Yafashije** |
  | Played | **Yakinnye** |
  | Ate | **Yariye** |
  | Drank | **Yanyoye** |
  | Returned | **Yasubiye** |

  ---

  ### Emotions

  | English | Kinyarwanda |
  |---------|-------------|
  | Happy | **Yishimye** |
  | Sad | **Yababaye** |

  ---

  ### Story Elements

  | English | Kinyarwanda |
  |---------|-------------|
  | Problem | **Ikibazo** |
  | Solution | **Igisubizo** |

  ---

  ## 📝 GRAMMAR TIPS

  ### Rule 1: Opening a Story

  **Pattern:** `Cyera Habayeho + [character] + [description]`

  | Kinyarwanda | English |
  |-------------|---------|
  | Cyera Habayeho umuhungu witwa Paul. | Once upon a time, there was a boy named Paul. |

  ---

  ### Rule 2: Describing Where Someone Lived

  **Pattern:** `[Subject] + yabayeho + [place]`

  | Kinyarwanda | English |
  |-------------|---------|
  | Yabayeho mu mudugudu muto. | He lived in a small village. |

  ---

  ### Rule 3: Action Sequences

  **Pattern:** `[Time] + [subject] + [past verb] + [place/object]`

  | Kinyarwanda | English |
  |-------------|---------|
  | Umunsi umwe, yagiye mu ishyamba. | One day, he went to the forest. |
  | Yabonye inyoni nziza. | He saw a beautiful bird. |

  ---

  ### Rule 4: Describing States

  **Pattern:** `[Subject] + yari + [state]`

  | Kinyarwanda | English |
  |-------------|---------|
  | Inyoni yari yafashwe. | The bird was trapped. |

  ---

  ### Rule 5: Actions with Emotions

  **Pattern:** `[Subject] + [action] + [emotion]`

  | Kinyarwanda | English |
  |-------------|---------|
  | Inyoni yiruka yishimye. | The bird flew away happily. |

  ---

  ### Rule 6: Reactions

  **Pattern:** `[Subject] + [emotion] + [reason]`

  | Kinyarwanda | English |
  |-------------|---------|
  | Mama we yishimye kumubona. | His mother was happy to see him. |

  ---

  ### Rule 7: Conclusions

  **Pattern:** `Kuva + [time] + [subject] + [result]`

  | Kinyarwanda | English |
  |-------------|---------|
  | Kuva uwo munsi, Paul yakunze inyamaswa cyane. | From that day, Paul loved animals even more. |

  ---

  ## 💬 KEY PHRASES

  ### Story Openings

  | English | Kinyarwanda |
  |---------|-------------|
  | Once upon a time, there was a boy named Paul. | Cyera Habayeho umuhungu witwa Paul. |
  | He lived in a small village. | Yabayeho mu mudugudu muto. |

  ### Actions

  | English | Kinyarwanda |
  |---------|-------------|
  | One day, he went to the forest. | Umunsi umwe, yagiye mu ishyamba. |
  | He saw a beautiful bird. | Yabonye inyoni nziza. |
  | The bird was trapped. | Inyoni yari yafashwe. |
  | Paul helped the bird. | Paul yafashije iyo nyoni. |
  | The bird flew away happily. | Inyoni yiruka yishimye. |
  | Paul returned home. | Paul yasubiye mu rugo. |
  | His mother was happy to see him. | Mama we yishimye kumubona. |
  | From that day, Paul loved animals even more. | Kuva uwo munsi, Paul yakunze inyamaswa cyane. |

  ---

  ## 📚 SAMPLE STORY

  ### Paul and the Bird - Paul n'Inyoni

  **Cyera Habayeho umuhungu witwa Paul. Yabayeho mu mudugudu muto.**

  *(Once upon a time, there was a boy named Paul. He lived in a small village.)*

  **Umunsi umwe, yagiye mu ishyamba. Yabonye inyoni nziza. Inyoni yari yafashwe.**

  *(One day, he went to the forest. He saw a beautiful bird. The bird was trapped.)*

  **Paul yafashije iyo nyoni. Inyoni yiruka yishimye.**

  *(Paul helped the bird. The bird flew away happily.)*

  **Paul yasubiye mu rugo. Mama we yishimye kumubona.**

  *(Paul returned home. His mother was happy to see him.)*

  **Kuva uwo munsi, Paul yakunze inyamaswa cyane.**

  *(From that day, Paul loved animals even more.)*

  ---

  ## 📚 SAMPLE DIALOGUES

  ### Dialogue 1: Telling a Story

  **A:** Ushaka kumva inkuru?  
  *(Do you want to hear a story?)*

  **B:** Yego, ndashaka kumva inkuru.  
  *(Yes, I want to hear a story.)*

  **A:** Cyera Habayeho umuhungu witwa Paul.  
  *(Once upon a time, there was a boy named Paul.)*

  **B:** Yabayeho he?  
  *(Where did he live?)*

  **A:** Yabayeho mu mudugudu muto.  
  *(He lived in a small village.)*

  ---

  ### Dialogue 2: What Happened Next?

  **A:** Umunsi umwe, yagiye mu ishyamba.  
  *(One day, he went to the forest.)*

  **B:** Yabonye iki?  
  *(What did he see?)*

  **A:** Yabonye inyoni nziza. Inyoni yari yafashwe.  
  *(He saw a beautiful bird. The bird was trapped.)*

  **B:** Yafashije iyo nyoni?  
  *(Did he help the bird?)*

  **A:** Yego, yafashije iyo nyoni.  
  *(Yes, he helped the bird.)*

  ---

  ### Dialogue 3: The Ending

  **A:** Inyoni yiruka yishimye.  
  *(The bird flew away happily.)*

  **B:** Paul yasubiye mu rugo?  
  *(Did Paul return home?)*

  **A:** Yego, yasubiye mu rugo. Mama we yishimye kumubona.  
  *(Yes, he returned home. His mother was happy to see him.)*

  **B:** Kuva uwo munsi, Paul yakunze inyamaswa cyane.  
  *(From that day, Paul loved animals even more.)*

  **A:** Inkuru nziza!  
  *(Nice story!)*

  ---

  ## 🔄 QUICK REFERENCE

  ### Story Openings

  | English | Kinyarwanda |
  |---------|-------------|
  | Once upon a time | Cyera Habayeho |
  | Long ago | Hashize igihe kirekire |
  | There was / There were | Hariho |
  | Lived | Yabayeho |

  ### Places

  | English | Kinyarwanda | English | Kinyarwanda |
  |---------|-------------|---------|-------------|
  | Village | Umudugudu | Town | Umujyi |
  | House | Inzu | Forest | Ishyamba |
  | Mountain | Umusozi | River | Umugezi |

  ### Characters

  | English | Kinyarwanda | English | Kinyarwanda |
  |---------|-------------|---------|-------------|
  | Boy | Umuhungu | Girl | Umukobwa |
  | Mother | Mama | Father | Data |
  | Friend | Inshuti | Animals | Inyamaswa |

  ### Actions

  | English | Kinyarwanda | English | Kinyarwanda |
  |---------|-------------|---------|-------------|
  | Went | Yagiye | Saw | Yabonye |
  | Met | Yahuye na | Helped | Yafashije |
  | Played | Yakinnye | Ate | Yariye |
  | Drank | Yanyoye | Returned | Yasubiye |

  ### Emotions & Elements

  | English | Kinyarwanda | English | Kinyarwanda |
  |---------|-------------|---------|-------------|
  | Happy | Yishimye | Sad | Yababaye |
  | Problem | Ikibazo | Solution | Igisubizo |

  ### Story Sentences

  | English | Kinyarwanda |
  |---------|-------------|
  | Once upon a time, there was a boy named Paul. | Cyera Habayeho umuhungu witwa Paul. |
  | He lived in a small village. | Yabayeho mu mudugudu muto. |
  | One day, he went to the forest. | Umunsi umwe, yagiye mu ishyamba. |
  | He saw a beautiful bird. | Yabonye inyoni nziza. |
  | The bird was trapped. | Inyoni yari yafashwe. |
  | Paul helped the bird. | Paul yafashije iyo nyoni. |
  | The bird flew away happily. | Inyoni yiruka yishimye. |
  | Paul returned home. | Paul yasubiye mu rugo. |
  | His mother was happy to see him. | Mama we yishimye kumubona. |
  | From that day, Paul loved animals even more. | Kuva uwo munsi, Paul yakunze inyamaswa cyane. |

  ---

  ## 🎯 UNIT GOAL

  By the end of this lesson, you will be able to:

  ✅ Use **story openings** (Cyera Habayeho, Hashize igihe kirekire)

  ✅ Name **places** in a story (village, forest, mountain, river)

  ✅ Name **characters** (boy, girl, mother, father, friend, animals)

  ✅ Use **past tense actions** (went, saw, met, helped, played, returned)

  ✅ Describe **emotions** (happy, sad)

  ✅ Tell a **complete story** from beginning to end

  ---

  ### 🏆 Total XP Available: **500 XP**

  ---

  ## 📋 EXERCISE TYPES IN THIS LESSON

  | Exercise Type | What You'll Do | Example |
  |---------------|----------------|---------|
  | **Translation** | Translate storytelling words/phrases | "Once upon a time" → "Cyera Habayeho" |
  | **Multiple Choice** | Choose the correct answer | What does "Umuhungu" mean? → Boy |
  | **Listen & Type** | Type what you hear | Listen to "Cyera Habayeho" → type it |
  | **Listen & Choose** | Listen and pick the answer | Hear a word → choose it |
  | **Speaking** | Say the word/phrase aloud | Say "Cyera Habayeho umuhungu" |
  | **Word Bank** | Build sentences from words | ["Habayeho", "rimwe", "umuhungu"] |
  | **Sentence Scramble** | Unscramble sentences | "Habayeho rimwe umuhungu" |
  | **Fill in the Blank** | Complete the sentence | "Cyera Habayeho ___." → umuhungu |
  | **Picture → Word** | Match images to story elements | Village image → Umudugudu |
  | **Matching** | Pair English with Kinyarwanda | "Boy" ↔ "Umuhungu" |
  | **True/False** | Verify statements | "Umuhungu means girl" → False |
  | **Conversation** | Respond to questions | "Witwa nde?" → Answer |
  | **Story** | Read and build sentences | Paul and the Bird story |
  | **Flashcards** | Study with images | Front/Back cards |
  | **Speed Matching** | Match under time limit | 45 seconds challenge |
  | **Timed Challenge** | Quick-fire questions | 30-second lightning round |

  ---

  ## 🌟 STUDY TIPS

  1. **Learn Story Structure**
  A good story has:
  - **Beginning:** Cyera Habayeho + character introduction
  - **Middle:** Action + problem
  - **End:** Solution + conclusion

  2. **Learn Story Openings**
  Master starting a story:
  - Cyera Habayeho umuhungu witwa Paul. (Once upon a time, there was a boy named Paul.)
  - Hashize igihe kirekire... (Long ago...)

  3. **Learn Place Vocabulary**
  Practice describing where stories happen:
  - Yabayeho mu mudugudu muto. (He lived in a small village.)
  - Yagiye mu ishyamba. (He went to the forest.)

  4. **Learn Action Sequences**
  Practice telling what happened:
  - Umunsi umwe, yagiye mu ishyamba. (One day, he went to the forest.)
  - Yabonye inyoni nziza. (He saw a beautiful bird.)
  - Paul yafashije iyo nyoni. (Paul helped the bird.)

  5. **Learn Emotions**
  Practice describing how characters feel:
  - Yishimye (happy)
  - Yababaye (sad)

  6. **Learn Story Endings**
  Master concluding a story:
  - Kuva uwo munsi, Paul yakunze inyamaswa cyane. (From that day, Paul loved animals even more.)

  7. **Combine with Other Lessons**
  Use vocabulary from other units:
  - "Yabonye inka." (He saw a cow.)
  - "Yagiye ku isoko." (He went to the market.)
  - "Yahuye n'inshuti ye." (He met his friend.)

  8. **Flashcards Daily**
  Use the flashcards to review daily. Practice with images for better retention!

  ---

  ## 🔗 CONNECTIONS TO OTHER UNITS

  ### From Animals Guidebook:
  | Animal + Story | Kinyarwanda |
  |----------------|-------------|
  | He saw a bird | Yabonye inyoni |
  | He helped the cow | Yafashije inka |
  | The animals were happy | Inyamaswa zishimye |

  ### From Family Guidebook:
  | Family + Story | Kinyarwanda |
  |----------------|-------------|
  | His mother was happy | Mama we yishimye |
  | His father helped | Data we yafashije |

  ### From Past Tense Guidebook:
  | Past + Story | Kinyarwanda |
  |--------------|-------------|
  | He went | Yagiye |
  | He saw | Yabonye |
  | He met | Yahuye |
  | He returned | Yasubiye |

  ### From Emotions Guidebook:
  | Emotion + Story | Kinyarwanda |
  |-----------------|-------------|
  | Happy | Yishimye |
  | Sad | Yababaye |

  ---

  ## ✅ CHECKLIST FOR COMPLETION

  Before moving on, make sure you can:

  - Say "Once upon a time" (Cyera Habayeho)
  - Say "Long ago" (Hashize igihe kirekire)
  - Say "There was" (Hariho) and "Lived" (Yabayeho)
  - Say places: Village (Umudugudu), Forest (Ishyamba), Mountain (Umusozi), River (Umugezi)
  - Say characters: Boy (Umuhungu), Girl (Umukobwa), Mother (Mama), Father (Data), Friend (Inshuti)
  - Say actions: Went (Yagiye), Saw (Yabonye), Met (Yahuye na), Helped (Yafashije), Played (Yakinnye), Returned (Yasubiye)
  - Say emotions: Happy (Yishimye), Sad (Yababaye)
  - Say story elements: Problem (Ikibazo), Solution (Igisubizo)
  - Say "Once upon a time, there was a boy named Paul" (Cyera Habayeho umuhungu witwa Paul)
  - Say "He lived in a small village" (Yabayeho mu mudugudu muto)
  - Say "One day, he went to the forest" (Umunsi umwe, yagiye mu ishyamba)
  - Say "He saw a beautiful bird" (Yabonye inyoni nziza)
  - Say "The bird was trapped" (Inyoni yari yafashwe)
  - Say "Paul helped the bird" (Paul yafashije iyo nyoni)
  - Say "The bird flew away happily" (Inyoni yiruka yishimye)
  - Say "Paul returned home" (Paul yasubiye mu rugo)
  - Say "His mother was happy to see him" (Mama we yishimye kumubona)
  - Say "From that day, Paul loved animals even more" (Kuva uwo munsi, Paul yakunze inyamaswa cyane)

  ---

  ## 💡 FUN FACTS

  1. "Cyera Habayeho" is the classic opening for Kinyarwanda folktales - every story starts with these words.
  2. "Hashize igihe kirekire" means "a long time has passed" - another common way to begin a story.
  3. "Hariho" means "there was" and is used to introduce characters or objects.
  4. "Yabayeho" specifically means "lived" or "existed" - it's used to describe where someone lived.
  5. "Yahuye na" means "met with" and is used for encounters between characters.
  6. "Yasubiye" means "returned" and is a common way to end a journey in a story.
  7. "Ikibazo" is the problem in the story, and "Igisubizo" is the solution - these are key parts of any narrative.
  8. "Kuva uwo munsi" means "from that day" and is used to show how characters changed after their experiences.

  Good luck! Start with the translation exercises, then work through listening, speaking, and the interactive challenges. Practice telling stories to friends and family - it's the best way to learn! 🔥

  *Umusanzu mwiza! (Good luck!)*

'''
new_text = text[:start] + new_block + text[end:]
path.write_text(new_text, encoding='utf-8')
print('updated', path)
