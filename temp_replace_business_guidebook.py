from pathlib import Path

path = Path('content/EN-TO-RW/level5/business.yaml')
text = path.read_text(encoding='utf-8')
start = text.find('guidebook: |')
end = text.find('\nexercises:', start)
if start == -1 or end == -1:
    raise SystemExit(f'Boundaries not found: start={start}, end={end}')
new_block = '''guidebook: |

  # 📘 Guidebook for Business - Ubucuruzi mu Kinyarwanda

  ## 📖 Overview

  Welcome to the Business lesson! In this unit, you'll learn how to talk about professional settings, work, meetings, and commerce in Kinyarwanda. You'll master essential vocabulary for business interactions - from company structures and client relationships to contracts, finance, and negotiations. With **500 XP** available, you'll be conducting business in Kinyarwanda with confidence!

  ---

  ## 💼 BUSINESS VOCABULARY

  ### Company & People

  | English | Kinyarwanda |
  |---------|-------------|
  | Business | **Ubucuruzi** |
  | Company | **Sosiyete** |
  | Employee | **Umukozi** |
  | Employer | **Umukoresha** |
  | Boss / Manager | **Umuyobozi** |
  | Partner | **Umufatanyabikorwa** |
  | Client / Customer | **Umukiriya** |

  ---

  ### Work & Meetings

  | English | Kinyarwanda |
  |---------|-------------|
  | Work | **Akazi** |
  | Office | **Ibiro** |
  | Meeting | **Inama** |
  | Project | **Umushinga** |
  | Report | **Raporo** |
  | Plan | **Gahunda** |

  ---

  ### Products & Services

  | English | Kinyarwanda |
  |---------|-------------|
  | Product | **Igicuruzwa** |
  | Service | **Serivisi** |
  | Market | **Isoko** |
  | Sales | **Kugurisha** |
  | Purchase | **Kugura** |

  ---

  ### Finance

  | English | Kinyarwanda |
  |---------|-------------|
  | Profit | **Inyungu** |
  | Loss | **Igihombo** |
  | Investment | **Ishoramari** |
  | Finance | **Imari** |
  | Budget | **Ingengo y'imari** |
  | Salary | **Umushahara** |

  ---

  ### Legal & Strategy

  | English | Kinyarwanda |
  |---------|-------------|
  | Contract / Agreement | **Amasezerano** |
  | Negotiation | **Kugirana ibiganiro** |
  | Strategy | **Uburyo bwo gukora** |

  ---

  ## 📝 GRAMMAR TIPS

  ### Rule 1: Stating Ownership

  **Pattern:** `[Subject] + [have verb] + [business entity]`

  | Kinyarwanda | English |
  |-------------|---------|
  | Mfite sosiyete. | I run a company. |
  | Dufite inama uyu munsi. | We have a meeting today. |

  ---

  ### Rule 2: Describing Business Performance

  **Pattern:** `[Company] + [profit/loss verb] + [financial result]`

  | Kinyarwanda | English |
  |-------------|---------|
  | Sosiyete yabonye inyungu. | The company made a profit. |
  | Sosiyete yagize igihombo. | The company suffered a loss. |

  ---

  ### Rule 3: Describing Client Satisfaction

  **Pattern:** `[Client] + [satisfaction verb]`

  | Kinyarwanda | English |
  |-------------|---------|
  | Umukiriya yishimye. | The client is satisfied. |

  ---

  ### Rule 4: Stating Completion

  **Pattern:** `[Document/task] + [completion verb]`

  | Kinyarwanda | English |
  |-------------|---------|
  | Raporo irarangiye. | The report is ready. |

  ---

  ### Rule 5: Expressing Need

  **Pattern:** `[Subject] + [need verb] + [item]`

  | Kinyarwanda | English |
  |-------------|---------|
  | Dukeneye uburyo bwo kugurisha. | We need a strategy for sales. |

  ---

  ### Rule 6: Stating Success

  **Pattern:** `[Subject] + [success verb]`

  | Kinyarwanda | English |
  |-------------|---------|
  | Ishoramari ryagenze neza. | The investment was successful. |

  ---

  ### Rule 7: Describing Role

  **Pattern:** `[Subject] + [role verb] + [project]`

  | Kinyarwanda | English |
  |-------------|---------|
  | Ni we muyobozi w'umushinga. | She is the manager of the project. |

  ---

  ### Rule 8: Stating Action

  **Pattern:** `[Subject] + [action verb] + [object]`

  | Kinyarwanda | English |
  |-------------|---------|
  | Twasinye amasezerano. | We signed the contract. |

  ---

  ### Rule 9: Stating Current Activity

  **Pattern:** `[Subject] + [present continuous] + [object]`

  | Kinyarwanda | English |
  |-------------|---------|
  | Turimo kugirana ibiganiro n'umukiriya. | We are negotiating with the client. |

  ---

  ### Rule 10: Asking for Information

  **Pattern:** `[Item] + [question word]?`

  | Kinyarwanda | English |
  |-------------|---------|
  | Ingengo y'imari y'uyu mushinga ni iyihe? | What is the budget for this project? |

  ---

  ## 💬 KEY PHRASES

  ### Company & Work

  | English | Kinyarwanda |
  |---------|-------------|
  | I run a company. | Mfite sosiyete. |
  | We have a meeting today. | Dufite inama uyu munsi. |
  | He works in finance. | Akora mu by'imari. |
  | She is the manager of the project. | Ni we muyobozi w'umushinga. |

  ### Clients & Products

  | English | Kinyarwanda |
  |---------|-------------|
  | The client is satisfied. | Umukiriya yishimye. |
  | Our product is popular. | Igicuruzwa cyacu kizwi cyane. |

  ### Financial Performance

  | English | Kinyarwanda |
  |---------|-------------|
  | The company made a profit. | Sosiyete yabonye inyungu. |
  | The company suffered a loss. | Sosiyete yagize igihombo. |
  | The investment was successful. | Ishoramari ryagenze neza. |

  ### Contracts & Negotiations

  | English | Kinyarwanda |
  |---------|-------------|
  | We signed the contract. | Twasinye amasezerano. |
  | We are negotiating with the client. | Turimo kugirana ibiganiro n'umukiriya. |

  ### Reports & Planning

  | English | Kinyarwanda |
  |---------|-------------|
  | The report is ready. | Raporo irarangiye. |
  | We need a strategy for sales. | Dukeneye uburyo bwo kugurisha. |
  | We need to plan the next steps. | Dukeneye gutegura intambwe zikurikira. |

  ### Questions

  | English | Kinyarwanda |
  |---------|-------------|
  | What is the budget for this project? | Ingengo y'imari y'uyu mushinga ni iyihe? |

  ---

  ## 📚 SAMPLE DIALOGUES

  ### Dialogue 1: Company and Meeting

  **A:** Ufite sosiyete?  
  *(Do you have a company?)*

  **B:** Yego, mfite sosiyete.  
  *(Yes, I run a company.)*

  **A:** Mufite inama uyu munsi?  
  *(Do you have a meeting today?)*

  **B:** Yego, dufite inama uyu munsi.  
  *(Yes, we have a meeting today.)*

  **A:** Umukiriya yishimye?  
  *(Is the client satisfied?)*

  **B:** Yego, yishimye cyane.  
  *(Yes, he is very satisfied.)*

  ---

  ### Dialogue 2: Business Performance

  **A:** Sosiyete yabonye inyungu?  
  *(Did the company make a profit?)*

  **B:** Yego, yabonye inyungu.  
  *(Yes, it made a profit.)*

  **A:** Ishoramari ryagenze neza?  
  *(Was the investment successful?)*

  **B:** Yego, ryagenze neza cyane.  
  *(Yes, it was very successful.)*

  **A:** Twasinye amasezerano?  
  *(Did we sign the contract?)*

  **B:** Yego, twasinye amasezerano.  
  *(Yes, we signed the contract.)*

  ---

  ### Dialogue 3: Reports and Planning

  **A:** Raporo irarangiye?  
  *(Is the report ready?)*

  **B:** Yego, irarangiye.  
  *(Yes, it's ready.)*

  **A:** Dukeneye uburyo bwo kugurisha.  
  *(We need a strategy for sales.)*

  **B:** Ni byiza. Dukeneye gutegura intambwe zikurikira.  
  *(That's good. We need to plan the next steps.)*

  **A:** Ingengo y'imari y'uyu mushinga ni iyihe?  
  *(What is the budget for this project?)*

  **B:** Ni amafaranga ibihumbi icumi.  
  *(It's 10,000 francs.)*

  ---

  ### Dialogue 4: Negotiating

  **A:** Turimo kugirana ibiganiro n'umukiriya.  
  *(We are negotiating with the client.)*

  **B:** Ni we muyobozi w'umushinga?  
  *(Is she the manager of the project?)*

  **A:** Yego, ni we muyobozi w'umushinga.  
  *(Yes, she is the manager of the project.)*

  **B:** Akora mu by'imari?  
  *(Does she work in finance?)*

  **A:** Yego, akora mu by'imari.  
  *(Yes, she works in finance.)*

  ---

  ## 🔄 QUICK REFERENCE

  ### Company & People

  | English | Kinyarwanda | English | Kinyarwanda |
  |---------|-------------|---------|-------------|
  | Business | Ubucuruzi | Company | Sosiyete |
  | Employee | Umukozi | Employer | Umukoresha |
  | Boss | Umuyobozi | Manager | Umuyobozi |
  | Partner | Umufatanyabikorwa | Client | Umukiriya |

  ### Work & Meetings

  | English | Kinyarwanda | English | Kinyarwanda |
  |---------|-------------|---------|-------------|
  | Work | Akazi | Office | Ibiro |
  | Meeting | Inama | Project | Umushinga |
  | Report | Raporo | Plan | Gahunda |

  ### Products & Services

  | English | Kinyarwanda | English | Kinyarwanda |
  |---------|-------------|---------|-------------|
  | Product | Igicuruzwa | Service | Serivisi |
  | Market | Isoko | Sales | Kugurisha |
  | Purchase | Kugura | | |

  ### Finance

  | English | Kinyarwanda | English | Kinyarwanda |
  |---------|-------------|---------|-------------|
  | Profit | Inyungu | Loss | Igihombo |
  | Investment | Ishoramari | Finance | Imari |
  | Budget | Ingengo y'imari | Salary | Umushahara |

  ### Legal & Strategy

  | English | Kinyarwanda |
  |---------|-------------|
  | Contract | Amasezerano |
  | Negotiation | Kugirana ibiganiro |
  | Strategy | Uburyo bwo gukora |

  ### Useful Sentences

  | English | Kinyarwanda |
  |---------|-------------|
  | I run a company. | Mfite sosiyete. |
  | We have a meeting today. | Dufite inama uyu munsi. |
  | The client is satisfied. | Umukiriya yishimye. |
  | He works in finance. | Akora mu by'imari. |
  | The report is ready. | Raporo irarangiye. |
  | The investment was successful. | Ishoramari ryagenze neza. |
  | We signed the contract. | Twasinye amasezerano. |
  | The company made a profit. | Sosiyete yabonye inyungu. |
  | The company suffered a loss. | Sosiyete yagize igihombo. |
  | She is the manager of the project. | Ni we muyobozi w'umushinga. |
  | We are negotiating with the client. | Turimo kugirana ibiganiro n'umukiriya. |
  | What is the budget for this project? | Ingengo y'imari y'uyu mushinga ni iyihe? |
  | We need to plan the next steps. | Dukeneye gutegura intambwe zikurikira. |

  ---

  ## 🎯 UNIT GOAL

  By the end of this lesson, you will be able to:

  ✅ Use **30+ business words** in Kinyarwanda

  ✅ Say **"I run a company"** (Mfite sosiyete)

  ✅ Say **"We have a meeting today"** (Dufite inama uyu munsi)

  ✅ Say **"The client is satisfied"** (Umukiriya yishimye)

  ✅ Say **"We signed the contract"** (Twasinye amasezerano)

  ✅ Say **"The company made a profit"** (Sosiyete yabonye inyungu)

  ✅ Say **"The company suffered a loss"** (Sosiyete yagize igihombo)

  ✅ Say **"The investment was successful"** (Ishoramari ryagenze neza)

  ✅ Say **"The report is ready"** (Raporo irarangiye)

  ✅ Say **"She is the manager of the project"** (Ni we muyobozi w'umushinga)

  ✅ Say **"We are negotiating with the client"** (Turimo kugirana ibiganiro n'umukiriya)

  ✅ Ask **"What is the budget for this project?"** (Ingengo y'imari y'uyu mushinga ni iyihe?)

  ✅ Say **"We need to plan the next steps"** (Dukeneye gutegura intambwe zikurikira)

  ---

  ### 🏆 Total XP Available: **500 XP**

  ---

  ## 📋 EXERCISE TYPES IN THIS LESSON

  | Exercise Type | What You'll Do | Example |
  |---------------|----------------|---------|
  | **Translation** | Translate business words/phrases | "Business" → "Ubucuruzi" |
  | **Multiple Choice** | Choose the correct answer | What does "Ubucuruzi" mean? → Business |
  | **Listen & Type** | Type what you hear | Listen to "Ubucuruzi" → type it |
  | **Listen & Choose** | Listen and pick the answer | Hear a word → choose it |
  | **Speaking** | Say the word/phrase aloud | Say "Mfite sosiyete" |
  | **Word Bank** | Build sentences from words | ["Mfite", "sosiyete"] |
  | **Sentence Scramble** | Unscramble sentences | "Mfite sosiyete" |
  | **Fill in the Blank** | Complete the sentence | "Mfite ___." → sosiyete |
  | **Picture → Word** | Match images to business concepts | Office image → Ibiro |
  | **Matching** | Pair English with Kinyarwanda | "Business" ↔ "Ubucuruzi" |
  | **True/False** | Verify statements | "Ubucuruzi means company" → False |
  | **Conversation** | Respond to questions | "Ufite sosiyete?" → Answer |
  | **Story** | Read and build sentences | Business Meeting story |
  | **Flashcards** | Study with images | Front/Back cards |
  | **Speed Matching** | Match under time limit | 45 seconds challenge |
  | **Timed Challenge** | Quick-fire questions | 30-second lightning round |

  ---

  ## 🌟 STUDY TIPS

  ### 1. **Learn by Category**
  Group business vocabulary by category:
  - **People:** Umukozi, Umukoresha, Umuyobozi, Umukiriya
  - **Work:** Akazi, Ibiro, Inama, Umushinga
  - **Finance:** Inyungu, Igihombo, Ishoramari, Imari
  - **Legal:** Amasezerano, Kugirana ibiganiro

  ### 2. **Learn Company Phrases**
  Master talking about companies:
  - Mfite sosiyete. (I run a company.)
  - Sosiyete yabonye inyungu. (The company made a profit.)
  - Sosiyete yagize igihombo. (The company suffered a loss.)

  ### 3. **Learn Meeting Phrases**
  Master talking about meetings:
  - Dufite inama uyu munsi. (We have a meeting today.)
  - Turimo kugirana ibiganiro n'umukiriya. (We are negotiating with the client.)

  ### 4. **Learn Client Phrases**
  Master talking about clients:
  - Umukiriya yishimye. (The client is satisfied.)
  - Turimo kugirana ibiganiro n'umukiriya. (We are negotiating with the client.)

  ### 5. **Learn Document Phrases**
  Master talking about documents:
  - Raporo irarangiye. (The report is ready.)
  - Twasinye amasezerano. (We signed the contract.)

  ### 6. **Learn Question Forms**
  Master asking questions:
  - Ingengo y'imari y'uyu mushinga ni iyihe? (What is the budget for this project?)

  ### 7. **Combine with Other Lessons**
  Use vocabulary from other units:
  - "Mfite sosiyete nini." (I have a big company.)
  - "Dufite inama ku isoko." (We have a meeting at the market.)
  - "Naguriye ibicuruzwa." (I bought products.)

  ### 8. **Flashcards Daily**
  Use the flashcards to review daily. Practice with images for better retention!

  ---

  ## 🔗 CONNECTIONS TO OTHER UNITS

  ### From Shopping Guidebook:
  | Shopping + Business | Kinyarwanda |
  |---------------------|-------------|
  | We sell products | Tugurisha ibicuruzwa |
  | The market is busy | Isoko rirakomeye |
  | I bought goods | Naguriye ibicuruzwa |

  ### From House Guidebook:
  | House + Business | Kinyarwanda |
  |------------------|-------------|
  | The office is in the city | Ibiro biri mu mujyi |
  | The company has a building | Sosiyete ifite inzu |

  ### From Greetings Guidebook:
  | Greeting + Business | Kinyarwanda |
  |---------------------|-------------|
  | Hello, we have a meeting | Muraho, dufite inama |
  | Good morning, colleague | Mwaramutse, mugenzi wacu |

  ---

  ## ✅ CHECKLIST FOR COMPLETION

  Before moving on, make sure you can:

  - [ ] Say "Business" (Ubucuruzi), "Company" (Sosiyete)
  - [ ] Say "Employee" (Umukozi), "Employer" (Umukoresha)
  - [ ] Say "Boss" (Umuyobozi), "Client" (Umukiriya)
  - [ ] Say "Meeting" (Inama), "Project" (Umushinga)
  - [ ] Say "Product" (Igicuruzwa), "Service" (Serivisi)
  - [ ] Say "Office" (Ibiro), "Work" (Akazi)
  - [ ] Say "Profit" (Inyungu), "Loss" (Igihombo)
  - [ ] Say "Investment" (Ishoramari), "Budget" (Ingengo y'imari)
  - [ ] Say "Contract" (Amasezerano), "Strategy" (Uburyo bwo gukora)
  - [ ] Say "I run a company" (Mfite sosiyete)
  - [ ] Say "We have a meeting today" (Dufite inama uyu munsi)
  - [ ] Say "The client is satisfied" (Umukiriya yishimye)
  - [ ] Say "We signed the contract" (Twasinye amasezerano)
  - [ ] Say "The company made a profit" (Sosiyete yabonye inyungu)
  - [ ] Say "The investment was successful" (Ishoramari ryagenze neza)
  - [ ] Say "The report is ready" (Raporo irarangiye)
  - [ ] Ask "What is the budget for this project?" (Ingengo y'imari y'uyu mushinga ni iyihe?)

  ---

  ## 💡 FUN FACTS

  1. "Ubucuruzi" is the general term for "business" and encompasses all commercial activities.
  2. "Sosiyete" is borrowed from English/French and is commonly used for "company" or "corporation."
  3. "Umuyobozi" means both "boss" and "manager" - it's a versatile term for leadership roles.
  4. "Inyungu" means "profit" and is related to the concept of "benefit" or "advantage."
  5. "Igihombo" is the opposite of "inyungu" - it means "loss" and is used for financial setbacks.
  6. "Ishoramari" literally means "putting money" - it's the Kinyarwanda term for "investment."
  7. "Amasezerano" are "contracts" or "agreements" - they are legally binding documents in business.
  8. "Kugirana ibiganiro" means "negotiating" - it comes from "kugira" (to have) and "ibiganiro" (discussions).

  Good luck! Start with the translation exercises, then work through listening, speaking, and the interactive challenges. Practice using business vocabulary in professional contexts - it's the best way to learn! 🔥

  *Umusanzu mwiza! (Good luck!)*

'''
new_text = text[:start] + new_block + text[end:]
path.write_text(new_text, encoding='utf-8')
print('updated', path)
