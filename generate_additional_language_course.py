from __future__ import annotations

from pathlib import Path
import copy
import re
import yaml

SOURCE_ROOT = Path('content/EN-TO-RW/EN-TO-RW')

LANGUAGES = {
    'de': {
        'folder': 'DE-TO-RW',
        'name': 'German',
        'replacements': {
            'How do you say': 'Wie sagt man',
            'in Kinyarwanda?': 'auf Kinyarwanda?',
            'What is': 'Was ist',
            'What does': 'Was bedeutet',
            'mean?': 'in diesem Satz?',
            'Learn the key vocabulary and practice exercises for this lesson.': 'Lerne das wichtige Vokabular und übe die Übungen dieser Lektion.',
            'Welcome to this lesson! In this unit, you will build vocabulary, sentence patterns, and practical speaking skills.': 'Willkommen zu dieser Lektion! In dieser Einheit lernst du Vokabular, Satzmuster und praktische Sprechfähigkeiten.',
            'Core words': 'Wichtige Wörter',
            'Key Phrases': 'Wichtige Phrasen',
            'Grammar Tips': 'Grammatiktipps',
            'Sentence pattern': 'Satzmuster',
            'Use the lesson vocabulary in short, clear sentences.': 'Verwende das Vokabular der Lektion in kurzen, klaren Sätzen.',
            'Unit Goal': 'Lernziel',
            'Quick Reference': 'Schnellreferenz',
            'Example Sentences': 'Beispielsätze',
            'Learn the main vocabulary of this lesson': 'Lerne das wichtigste Vokabular dieser Lektion',
            'Use the new words in simple sentences': 'Verwende die neuen Wörter in einfachen Sätzen',
            'Practice listening, speaking, and translation': 'Übe Zuhören, Sprechen und Übersetzen',
            'Total XP Available: 500 XP': 'Gesamt-XP verfügbar: 500 XP',
            'By the end of this lesson, you will be able to use the main vocabulary and simple sentence patterns for this topic with confidence.': 'Am Ende dieser Lektion wirst du das Hauptvokabular und einfache Satzmuster zu diesem Thema sicher anwenden können.',
            'Good luck! Continue with the exercises to reinforce your learning. 🔥': 'Viel Erfolg! Mach weiter mit den Übungen, um dein Lernen zu festigen. 🔥',
            'Left': 'Links',
            'Right': 'Rechts',
            'Straight': 'Geradeaus',
            'Front': 'Vorn',
            'Behind': 'Hinten',
            'North': 'Norden',
            'South': 'Süden',
            'East': 'Osten',
            'West': 'Westen',
            'Near': 'Nahe',
            'Far': 'Weit',
            'Around': 'Umher',
            'Turn left': 'Biege links ab',
            'Turn right': 'Biege rechts ab',
            'Go straight': 'Gehe geradeaus',
            'Go back': 'Geh zurück',
            'Cross the street': 'Überquere die Straße',
            'The market is near': 'Der Markt ist nahe',
            'The hospital is far': 'Das Krankenhaus ist weit entfernt',
            'The bank is in front of the school': 'Die Bank steht vor der Schule',
            'The bank is in front of the school.': 'Die Bank steht vor der Schule.',
            'The hospital is far.': 'Das Krankenhaus ist weit entfernt.',
            'Back': 'Zurück',
            'Stop': 'Stop',
            'House': 'Haus',
            'Room': 'Raum',
            'Door': 'Tür',
            'Window': 'Fenster',
            'Roof': 'Dach',
            'Wall': 'Wand',
            'Floor': 'Boden',
            'Happy': 'Glücklich',
            'Sad': 'Traurig',
            'Angry': 'Wütend',
            'Scared': 'Verängstigt',
            'Listen': 'Höre zu',
            'Talk': 'Sprich',
            'Understand': 'Verstehe',
            'Repeat': 'Wiederhole',
            'Pronounce': 'Sprich aus',
            'Clarify': 'Kläre',
            'Slowly': 'Langsam',
            'Traffic light': 'Ampel',
            'Street': 'Straße',
            'Sign': 'Schild',
            'Corner': 'Ecke',
            'Intersection': 'Kreuzung',
            'Crossroads': 'Kreuzung',
            'Market': 'Markt',
            'Hospital': 'Krankenhaus',
            'Bank': 'Bank',
            'School': 'Schule',
            'Place': 'Ort',
            'If': 'Wenn',
            'When': 'Wenn',
            'Then': 'Dann',
            'Before': 'Vorher',
            'After': 'Danach',
            'Tomorrow': 'Morgen',
            'Yesterday': 'Gestern',
            'Always': 'Immer',
            'Never': 'Nie',
            'Hello': 'Hallo',
            'Goodbye': 'Auf Wiedersehen',
            'How are you?': 'Wie geht es dir?',
            'I am fine': 'Mir geht es gut',
            'What is your name?': 'Wie heißt du?',
            'My name is': 'Ich heiße',
            'This is my family': 'Das ist meine Familie',
            'How old are you?': 'Wie alt bist du?',
            'I am happy': 'Ich bin glücklich',
            'I am sad': 'Ich bin traurig',
            'I am tired': 'Ich bin müde',
            'I am hungry': 'Ich bin hungrig',
            'I am thirsty': 'Ich bin durstig',
        }
    },
    'it': {
        'folder': 'IT-TO-RW',
        'name': 'Italian',
        'replacements': {
            'How do you say': 'Come si dice',
            'in Kinyarwanda?': 'in kinyarwanda?',
            'What is': 'Cos è',
            'What does': 'Che cosa significa',
            'mean?': 'in questa frase?',
            'Learn the key vocabulary and practice exercises for this lesson.': 'Impara il vocabolario chiave e pratica gli esercizi di questa lezione.',
            'Welcome to this lesson! In this unit, you will build vocabulary, sentence patterns, and practical speaking skills.': 'Benvenuto in questa lezione! In questa unità imparerai vocaboli, modelli di frase e abilità pratiche di conversazione.',
            'Core words': 'Parole chiave',
            'Key Phrases': 'Frasi chiave',
            'Grammar Tips': 'Suggerimenti di grammatica',
            'Sentence pattern': 'Schema della frase',
            'Use the lesson vocabulary in short, clear sentences.': 'Usa il vocabolario della lezione in frasi brevi e chiare.',
            'Unit Goal': 'Obiettivo dell’unità',
            'Quick Reference': 'Riferimento rapido',
            'Example Sentences': 'Frasi esempio',
            'Learn the main vocabulary of this lesson': 'Impara il vocabolario principale di questa lezione',
            'Use the new words in simple sentences': 'Usa le nuove parole in frasi semplici',
            'Practice listening, speaking, and translation': 'Esercitati nell’ascolto, nel parlare e nella traduzione',
            'Total XP Available: 500 XP': 'XP totale disponibile: 500 XP',
            'By the end of this lesson, you will be able to use the main vocabulary and simple sentence patterns for this topic with confidence.': 'Alla fine di questa lezione sarai in grado di usare con sicurezza il vocabolario principale e i modelli di frase semplici di questo argomento.',
            'Good luck! Continue with the exercises to reinforce your learning. 🔥': 'Buona fortuna! Continua con gli esercizi per rafforzare l’apprendimento. 🔥',
            'Left': 'Sinistra',
            'Right': 'Destra',
            'Straight': 'Dritto',
            'Front': 'Davanti',
            'Behind': 'Dietro',
            'North': 'Nord',
            'South': 'Sud',
            'East': 'Est',
            'West': 'Ovest',
            'Near': 'Vicino',
            'Far': 'Lontano',
            'Around': 'Intorno',
            'Turn left': 'Gira a sinistra',
            'Turn right': 'Gira a destra',
            'Go straight': 'Vai dritto',
            'Go back': 'Torna indietro',
            'Cross the street': 'Attraversa la strada',
            'The market is near': 'Il mercato è vicino',
            'The hospital is far': 'L’ospedale è lontano',
            'The bank is in front of the school': 'La banca è davanti alla scuola',
            'The bank is in front of the school.': 'La banca è davanti alla scuola.',
            'The hospital is far.': 'L’ospedale è lontano.',
            'Back': 'Indietro',
            'Stop': 'Fermati',
            'House': 'Casa',
            'Room': 'Stanza',
            'Door': 'Porta',
            'Window': 'Finestra',
            'Roof': 'Tetto',
            'Wall': 'Parete',
            'Floor': 'Pavimento',
            'Happy': 'Felice',
            'Sad': 'Triste',
            'Angry': 'Arrabbiato',
            'Scared': 'Spaventato',
            'Listen': 'Ascolta',
            'Talk': 'Parla',
            'Understand': 'Capisci',
            'Repeat': 'Ripeti',
            'Pronounce': 'Pronuncia',
            'Clarify': 'Spiega',
            'Slowly': 'Lentamente',
            'Traffic light': 'Semaforo',
            'Street': 'Strada',
            'Sign': 'Segnale',
            'Corner': 'Angolo',
            'Intersection': 'Incrocio',
            'Crossroads': 'Crocevia',
            'Market': 'Mercato',
            'Hospital': 'Ospedale',
            'Bank': 'Banca',
            'School': 'Scuola',
            'Place': 'Luogo',
            'If': 'Se',
            'When': 'Quando',
            'Then': 'Poi',
            'Before': 'Prima',
            'After': 'Dopo',
            'Tomorrow': 'Domani',
            'Yesterday': 'Ieri',
            'Always': 'Sempre',
            'Never': 'Mai',
            'Hello': 'Ciao',
            'Goodbye': 'Addio',
            'How are you?': 'Come stai?',
            'I am fine': 'Sto bene',
            'What is your name?': 'Come ti chiami?',
            'My name is': 'Mi chiamo',
            'This is my family': 'Questa è la mia famiglia',
            'How old are you?': 'Quanti anni hai?',
            'I am happy': 'Sono felice',
            'I am sad': 'Sono triste',
            'I am tired': 'Sono stanco',
            'I am hungry': 'Ho fame',
            'I am thirsty': 'Ho sete',
        }
    }
}

COMMON_TRANSLATIONS = {
    'de': {
        'Greetings': 'Begrüßungen', 'Numbers': 'Zahlen', 'Family': 'Familie', 'Colors': 'Farben', 'Animals': 'Tiere',
        'Food': 'Essen', 'Daily Routines': 'Tagesabläufe', 'Weather': 'Wetter', 'Clothing': 'Kleidung', 'House': 'Haus',
        'Travel': 'Reisen', 'Shopping': 'Einkaufen', 'Restaurant': 'Restaurant', 'Directions': 'Wegbeschreibung',
        'Emotions': 'Gefühle', 'Past Tense': 'Vergangenheit', 'Future Tense': 'Zukunft', 'Conditionals': 'Bedingungssätze',
        'Opinions': 'Meinungen', 'Storytelling': 'Geschichten erzählen', 'Culture': 'Kultur', 'Days': 'Tage', 'Business': 'Geschäft',
        'Debates': 'Debatten', 'Negotiations': 'Verhandlungen', 'Fluency': 'Sprachfluss', 'Animal': 'Tier', 'Cow': 'Kuh',
        'Dog': 'Hund', 'Cat': 'Katze', 'Goat': 'Ziege', 'Sheep': 'Schaf', 'Pig': 'Schwein', 'Chicken': 'Huhn', 'Rabbit': 'Kaninchen',
        'Donkey': 'Esel', 'Pigeon': 'Taube', 'Bird': 'Vogel', 'Lion': 'Löwe', 'Elephant': 'Elefant', 'Mountain gorilla': 'Berggorilla',
        'Leopard': 'Leopard', 'Buffalo': 'Büffel', 'Zebra': 'Zebra', 'Hippopotamus': 'Nilpferd', 'Giraffe': 'Giraffe', 'Hyena': 'Hyäne',
        'Monkey': 'Affe', 'Grey crowned crane': 'Grauer Kronenkranich', 'Snake': 'Schlange', 'Frog': 'Frosch', 'Fish': 'Fisch', 'Bee': 'Biene',
        'eats': 'isst', 'drinks': 'trinkt', 'sleeps': 'schläft', 'runs': 'läuft', 'flies': 'fliegt', 'swims': 'schwimmt', 'plays': 'spielt',
        'grass': 'Gras', 'water': 'Wasser', 'meat': 'Fleisch', 'milk': 'Milch', 'above': 'oben', 'near': 'nahe', 'field': 'Feld',
        'house': 'Haus', 'market': 'Markt', 'street': 'Straße', 'school': 'Schule', 'bank': 'Bank', 'hospital': 'Krankenhaus', 'road': 'Straße',
        'room': 'Zimmer', 'door': 'Tür', 'window': 'Fenster', 'roof': 'Dach', 'wall': 'Wand', 'floor': 'Boden', 'Happy': 'Glücklich',
        'Sad': 'Traurig', 'Angry': 'Wütend', 'Scared': 'Verängstigt', 'Listen': 'Zuhören', 'Talk': 'Sprechen', 'Understand': 'Verstehen',
        'Repeat': 'Wiederholen', 'Pronounce': 'Aussprechen', 'Clarify': 'Klären', 'Slowly': 'Langsam', 'Traffic light': 'Ampel', 'Sign': 'Schild',
        'Corner': 'Ecke', 'Intersection': 'Kreuzung', 'Crossroads': 'Kreuzung', 'Place': 'Ort', 'If': 'Wenn', 'When': 'Wenn', 'Then': 'Dann',
        'Before': 'Vorher', 'After': 'Danach', 'Tomorrow': 'Morgen', 'Yesterday': 'Gestern', 'Always': 'Immer', 'Never': 'Nie', 'Company': 'Unternehmen',
        'Employee': 'Mitarbeiter', 'Employer': 'Arbeitgeber', 'Boss': 'Chef', 'Client': 'Kunde', 'Meeting': 'Besprechung', 'Project': 'Projekt',
        'Price': 'Preis', 'Cost': 'Kosten', 'Offer': 'Angebot', 'Accept': 'Annehmen', 'Reject': 'Ablehnen', 'Agreement': 'Vereinbarung', 'Deal': 'Geschäft',
        'Tradition': 'Tradition', 'Custom': 'Brauch', 'Ceremony': 'Zeremonie', 'Festival': 'Fest', 'Heritage': 'Erbe', 'People': 'Menschen',
        'Breakfast': 'Frühstück', 'Lunch': 'Mittagessen', 'Dinner': 'Abendessen', 'Day': 'Tag', 'Night': 'Nacht', 'Rain': 'Regen', 'Sun': 'Sonne',
        'Cloud': 'Wolke', 'Wind': 'Wind', 'Snow': 'Schnee', 'Storm': 'Sturm', 'Blue': 'Blau', 'Red': 'Rot', 'Green': 'Grün', 'Yellow': 'Gelb',
        'Black': 'Schwarz', 'White': 'Weiß', 'Orange': 'Orange', 'Brown': 'Braun', 'Purple': 'Lila', 'Name': 'Name', 'Date': 'Datum', 'Time': 'Zeit',
        'Friend': 'Freund', 'Mother': 'Mutter', 'Father': 'Vater', 'Brother': 'Bruder', 'Sister': 'Schwester', 'Child': 'Kind', 'Children': 'Kinder',
        'Home': 'Zuhause', 'Work': 'Arbeit', 'Good morning': 'Guten Morgen', 'Good evening': 'Guten Abend', 'Hello': 'Hallo', 'Goodbye': 'Auf Wiedersehen',
        'Practice': 'Üben', 'Learn': 'Lernen', 'Vocabulary': 'Vokabular', 'Grammar': 'Grammatik', 'Sentence': 'Satz', 'Examples': 'Beispiele',
        'Read': 'Lesen', 'Write': 'Schreiben', 'Speak': 'Sprechen', 'Choose': 'Auswählen', 'Match': 'Zuordnen', 'Answer': 'Antwort', 'Question': 'Frage',
        'Level': 'Stufe', 'Flashcards': 'Karteikarten', 'Listen and type the animal name': 'Höre zu und schreibe den Tiernamen',
        'Listen and choose the correct animal': 'Höre zu und wähle das richtige Tier', 'Say this word': 'Sage dieses Wort', 'Say this sentence': 'Sage diesen Satz',
        'Choose the missing word': 'Wähle das fehlende Wort', 'Select the correct option': 'Wähle die richtige Option',
        'Read the sentence and choose the correct answer': 'Lies den Satz und wähle die richtige Antwort',
        'Translate the following sentence into Kinyarwanda:': 'Übersetze den folgenden Satz ins Kinyarwanda:',
        'Is this statement true or false?': 'Ist diese Aussage wahr oder falsch?', 'Left': 'Links', 'left': 'links', 'Right': 'Rechts', 'right': 'rechts',
        'Straight': 'Geradeaus', 'straight': 'geradeaus', 'Front': 'Vorn', 'front': 'vorn', 'Behind': 'Hinten', 'behind': 'hinten', 'Back': 'Zurück', 'back': 'zurück',
        'Near': 'Nahe', 'near': 'nahe', 'Far': 'Weit', 'far': 'weit', 'Around': 'Umher', 'around': 'umher', 'Stop': 'Stopp',
        'Turn left': 'Biege links ab', 'turn left': 'biege links ab', 'Turn right': 'Biege rechts ab', 'turn right': 'biege rechts ab',
        'Go straight': 'Gehe geradeaus', 'go straight': 'gehe geradeaus', 'Go back': 'Geh zurück', 'go back': 'geh zurück',
        'Cross the street': 'Überquere die Straße', 'cross the street': 'überquere die Straße', 'The market is near': 'Der Markt ist nahe',
        'The hospital is far': 'Das Krankenhaus ist weit entfernt', 'The bank is in front of the school': 'Die Bank ist vor der Schule', 'The hospital is far.': 'Das Krankenhaus ist weit entfernt.',
    },
    'it': {
        'Greetings': 'Saluti', 'Numbers': 'Numeri', 'Family': 'Famiglia', 'Colors': 'Colori', 'Animals': 'Animali', 'Food': 'Cibo',
        'Daily Routines': 'Routine quotidiane', 'Weather': 'Meteo', 'Clothing': 'Abbigliamento', 'House': 'Casa', 'Travel': 'Viaggi',
        'Shopping': 'Acquisti', 'Restaurant': 'Ristorante', 'Directions': 'Indicazioni', 'Emotions': 'Emozioni', 'Past Tense': 'Passato',
        'Future Tense': 'Futuro', 'Conditionals': 'Condizionali', 'Opinions': 'Opinioni', 'Storytelling': 'Raccontare storie', 'Culture': 'Cultura',
        'Days': 'Giorni', 'Business': 'Affari', 'Debates': 'Dibattiti', 'Negotiations': 'Negoziazioni', 'Fluency': 'Fluidità', 'Animal': 'Animale',
        'Cow': 'Mucca', 'Dog': 'Cane', 'Cat': 'Gatto', 'Goat': 'Capra', 'Sheep': 'Pecora', 'Pig': 'Maiale', 'Chicken': 'Pollo', 'Rabbit': 'Coniglio',
        'Donkey': 'Asino', 'Pigeon': 'Piccione', 'Bird': 'Uccello', 'Lion': 'Leone', 'Elephant': 'Elefante', 'Mountain gorilla': 'Gorilla di montagna',
        'Leopard': 'Leopardo', 'Buffalo': 'Bufalo', 'Zebra': 'Zebra', 'Hippopotamus': 'Ippopotamo', 'Giraffe': 'Giraffa', 'Hyena': 'Iena', 'Monkey': 'Scimmia',
        'Grey crowned crane': 'Gru coronata grigia', 'Snake': 'Serpente', 'Frog': 'Rana', 'Fish': 'Pesce', 'Bee': 'Ape', 'eats': 'mangia', 'drinks': 'beve',
        'sleeps': 'dorme', 'runs': 'corre', 'flies': 'vola', 'swims': 'nuota', 'plays': 'gioca', 'grass': 'erba', 'water': 'acqua', 'meat': 'carne', 'milk': 'latte',
        'above': 'sopra', 'near': 'vicino', 'field': 'campo', 'house': 'casa', 'market': 'mercato', 'street': 'strada', 'school': 'scuola', 'bank': 'banca',
        'hospital': 'ospedale', 'road': 'strada', 'room': 'stanza', 'door': 'porta', 'window': 'finestra', 'roof': 'tetto', 'wall': 'parete', 'floor': 'pavimento',
        'Happy': 'Felice', 'Sad': 'Triste', 'Angry': 'Arrabbiato', 'Scared': 'Spaventato', 'Listen': 'Ascolta', 'Talk': 'Parla', 'Understand': 'Capisci',
        'Repeat': 'Ripeti', 'Pronounce': 'Pronuncia', 'Clarify': 'Chiarisci', 'Slowly': 'Lentamente', 'Traffic light': 'Semaforo', 'Sign': 'Segnale',
        'Corner': 'Angolo', 'Intersection': 'Incrocio', 'Crossroads': 'Crocevia', 'Place': 'Luogo', 'If': 'Se', 'When': 'Quando', 'Then': 'Poi', 'Before': 'Prima',
        'After': 'Dopo', 'Tomorrow': 'Domani', 'Yesterday': 'Ieri', 'Always': 'Sempre', 'Never': 'Mai', 'Company': 'Azienda', 'Employee': 'Dipendente',
        'Employer': 'Datore di lavoro', 'Boss': 'Capo', 'Client': 'Cliente', 'Meeting': 'Riunione', 'Project': 'Progetto', 'Price': 'Prezzo', 'Cost': 'Costo',
        'Offer': 'Offerta', 'Accept': 'Accetta', 'Reject': 'Rifiuta', 'Agreement': 'Accordo', 'Deal': 'Affare', 'Tradition': 'Tradizione', 'Custom': 'Usanza',
        'Ceremony': 'Cerimonia', 'Festival': 'Festival', 'Heritage': 'Patrimonio', 'People': 'Persone', 'Breakfast': 'Colazione', 'Lunch': 'Pranzo', 'Dinner': 'Cena',
        'Day': 'Giorno', 'Night': 'Notte', 'Rain': 'Pioggia', 'Sun': 'Sole', 'Cloud': 'Nuvola', 'Wind': 'Vento', 'Snow': 'Neve', 'Storm': 'Tempesta',
        'Blue': 'Blu', 'Red': 'Rosso', 'Green': 'Verde', 'Yellow': 'Giallo', 'Black': 'Nero', 'White': 'Bianco', 'Orange': 'Arancione', 'Brown': 'Marrone', 'Purple': 'Viola',
        'Name': 'Nome', 'Date': 'Data', 'Time': 'Ora', 'Friend': 'Amico', 'Mother': 'Madre', 'Father': 'Padre', 'Brother': 'Fratello', 'Sister': 'Sorella',
        'Child': 'Bambino', 'Children': 'Bambini', 'Home': 'Casa', 'Work': 'Lavoro', 'Good morning': 'Buongiorno', 'Good evening': 'Buonasera', 'Hello': 'Ciao', 'Goodbye': 'Arrivederci',
        'Practice': 'Pratica', 'Learn': 'Impara', 'Vocabulary': 'Vocabolario', 'Grammar': 'Grammatica', 'Sentence': 'Frase', 'Examples': 'Esempi', 'Read': 'Leggi',
        'Write': 'Scrivi', 'Speak': 'Parla', 'Choose': 'Scegli', 'Match': 'Abbina', 'Answer': 'Risposta', 'Question': 'Domanda', 'Level': 'Livello', 'Flashcards': 'Schede',
        'Listen and type the animal name': 'Ascolta e scrivi il nome dell’animale', 'Listen and choose the correct animal': 'Ascolta e scegli l’animale corretto',
        'Say this word': 'Pronuncia questa parola', 'Say this sentence': 'Pronuncia questa frase', 'Choose the missing word': 'Scegli la parola mancante',
        'Select the correct option': 'Seleziona l’opzione corretta', 'Read the sentence and choose the correct answer': 'Leggi la frase e scegli la risposta corretta',
        'Translate the following sentence into Kinyarwanda:': 'Traduci la frase seguente in kinyarwanda:', 'Is this statement true or false?': 'Questa affermazione è vera o falsa?',
        'Left': 'Sinistra', 'left': 'sinistra', 'Right': 'Destra', 'right': 'destra', 'Straight': 'Dritto', 'straight': 'dritto', 'Front': 'Davanti', 'front': 'davanti',
        'Behind': 'Dietro', 'behind': 'dietro', 'Back': 'Indietro', 'back': 'indietro', 'Near': 'Vicino', 'near': 'vicino', 'Far': 'Lontano', 'far': 'lontano',
        'Around': 'Intorno', 'around': 'intorno', 'Stop': 'Fermati', 'Turn left': 'Gira a sinistra', 'turn left': 'gira a sinistra', 'Turn right': 'Gira a destra', 'turn right': 'gira a destra',
        'Go straight': 'Vai dritto', 'go straight': 'vai dritto', 'Go back': 'Torna indietro', 'go back': 'torna indietro', 'Cross the street': 'Attraversa la strada',
        'cross the street': 'attraversa la strada', 'The market is near': 'Il mercato è vicino', 'The hospital is far': 'L’ospedale è lontano',
        'The bank is in front of the school': 'La banca è davanti alla scuola', 'The hospital is far.': 'L’ospedale è lontano.',
    },
}

FALLBACK_WORDS = {
    'de': {
        'I': 'Ich', 'i': 'ich', 'You': 'Du', 'you': 'du', 'We': 'Wir', 'we': 'wir', 'They': 'Sie', 'they': 'sie',
        'He': 'Er', 'he': 'er', 'She': 'Sie', 'she': 'sie', 'It': 'Es', 'it': 'es', 'My': 'Mein', 'my': 'mein',
        'Your': 'Dein', 'your': 'dein', 'Our': 'Unser', 'our': 'unser', 'Their': 'Ihr', 'their': 'ihr', 'What': 'Was',
        'what': 'was', 'Where': 'Wo', 'where': 'wo', 'When': 'Wann', 'when': 'wann', 'Who': 'Wer', 'who': 'wer', 'Why': 'Warum',
        'why': 'warum', 'Which': 'Welche', 'which': 'welche', 'How': 'Wie', 'how': 'wie', 'Can': 'Kann', 'can': 'kann',
        'Could': 'Könnte', 'could': 'könnte', 'Will': 'Wird', 'will': 'wird', 'Would': 'Würde', 'would': 'würde', 'Should': 'Sollte',
        'should': 'sollte', 'Must': 'Muss', 'must': 'muss', 'have': 'haben', 'has': 'hat', 'had': 'hatte', 'is': 'ist', 'are': 'sind',
        'am': 'bin', 'was': 'war', 'were': 'waren', 'be': 'sein', 'been': 'gewesen', 'do': 'tun', 'does': 'macht', 'did': 'tat',
        'not': 'nicht', 'no': 'nein', 'yes': 'ja', 'and': 'und', 'or': 'oder', 'but': 'aber', 'because': 'weil', 'if': 'wenn',
        'then': 'dann', 'than': 'als', 'the': 'der', 'a': 'ein', 'an': 'ein', 'this': 'dies', 'that': 'das', 'these': 'diese',
        'those': 'jene', 'in': 'in', 'on': 'auf', 'at': 'bei', 'to': 'zu', 'from': 'von', 'for': 'für', 'with': 'mit', 'without': 'ohne',
        'about': 'über', 'into': 'in', 'of': 'von', 'as': 'als', 'by': 'durch', 'before': 'vor', 'after': 'nach', 'more': 'mehr',
        'less': 'weniger', 'many': 'viele', 'much': 'viel', 'some': 'einige', 'all': 'alle', 'other': 'andere', 'another': 'ein weiteres',
        'same': 'gleich', 'different': 'anders', 'correct': 'richtig', 'wrong': 'falsch', 'true': 'wahr', 'false': 'falsch', 'new': 'neu',
        'old': 'alt', 'big': 'groß', 'small': 'klein', 'good': 'gut', 'bad': 'schlecht', 'beautiful': 'schön', 'important': 'wichtig',
        'possible': 'möglich', 'impossible': 'unmöglich', 'expensive': 'teuer', 'cheap': 'günstig', 'hot': 'heiß', 'cold': 'kalt',
        'today': 'heute', 'tomorrow': 'morgen', 'yesterday': 'gestern', 'now': 'jetzt', 'later': 'später', 'again': 'wieder', 'already': 'bereits',
        'every': 'jeden', 'week': 'Woche', 'year': 'Jahr', 'time': 'Zeit', 'day': 'Tag', 'name': 'Name', 'word': 'Wort', 'sentence': 'Satz',
        'question': 'Frage', 'answer': 'Antwort', 'activity': 'Aktivität', 'color': 'Farbe', 'sky': 'Himmel', 'image': 'Bild', 'picture': 'Bild',
        'person': 'Person', 'people': 'Menschen', 'boy': 'Junge', 'girl': 'Mädchen', 'children': 'Kinder', 'family': 'Familie', 'member': 'Mitglied',
        'place': 'Ort', 'airport': 'Flughafen', 'cashier': 'Kassierer', 'luggage': 'Gepäck', 'train': 'Zug', 'clock': 'Uhr', 'temperature': 'Temperatur',
        'weather': 'Wetter', 'furniture': 'Möbel', 'utensil': 'Küchenutensil', 'money': 'Geld', 'house': 'Haus', 'home': 'Zuhause', 'forest': 'Wald',
        'story': 'Geschichte', 'stories': 'Geschichten', 'concept': 'Konzept', 'element': 'Element', 'conditions': 'Bedingungen', 'risks': 'Risiken',
        'benefits': 'Vorteile', 'kind': 'Art', 'direction': 'Richtung', 'temperature': 'Temperatur', 'emotion': 'Gefühl', 'opinion': 'Meinung',
        'future': 'Zukunft', 'agreement': 'Vereinbarung', 'negotiation': 'Verhandlung', 'activity': 'Aktivität', 'doing': 'tun', 'buy': 'kaufen',
        'bought': 'gekauft', 'drink': 'trinken', 'drank': 'getrunken', 'eat': 'essen', 'ate': 'gegessen', 'go': 'gehen', 'went': 'ging',
        'going': 'gehen', 'come': 'kommen', 'came': 'kam', 'meet': 'treffen', 'met': 'getroffen', 'see': 'sehen', 'saw': 'sah', 'read': 'lesen',
        'wrote': 'schrieb', 'write': 'schreiben', 'copied': 'kopierte', 'signed': 'unterschrieb', 'sign': 'unterschreiben', 'travel': 'reisen',
        'traveling': 'reisen', 'traveled': 'reiste', 'celebrate': 'feiern', 'celebrates': 'feiert', 'celebrated': 'feierte', 'need': 'brauchen',
        'needs': 'braucht', 'want': 'wollen', 'like': 'mögen', 'think': 'denken', 'feel': 'fühlen', 'excited': 'aufgeregt', 'work': 'arbeiten',
        'working': 'arbeiten', 'help': 'helfen', 'helped': 'half', 'close': 'schließen', 'open': 'öffnen', 'leave': 'verlassen', 'leaves': 'verlässt',
        'sleep': 'schlafen', 'wake': 'aufwachen', 'cook': 'kochen', 'cooks': 'kocht', 'order': 'bestellen', 'ordering': 'bestellen', 'reach': 'erreichen',
        'plan': 'planen', 'decide': 'entscheiden', 'provide': 'bereitstellen', 'happen': 'passieren', 'study': 'lernen', 'fail': 'scheitern', 'succeed': 'erfolgreich sein',
        'play': 'spielen', 'show': 'zeigen', 'indicate': 'anzeigen', 'mean': 'bedeuten', 'definitely': 'bestimmt', 'maybe': 'vielleicht', 'proud': 'stolz',
        'calm': 'ruhig', 'lonely': 'einsam', 'scared': 'ängstlich', 'angry': 'wütend', 'sad': 'traurig', 'wife': 'Ehefrau', 'year': 'Jahr',
        'bag': 'Tasche', 'bed': 'Bett', 'book': 'Buch', 'car': 'Auto', 'contract': 'Vertrag', 'decision': 'Entscheidung', 'discount': 'Rabatt',
        'map': 'Karte', 'passport': 'Reisepass', 'project': 'Projekt', 'receipt': 'Quittung', 'repetition': 'Wiederholung', 'report': 'Bericht',
        'reservation': 'Reservierung', 'table': 'Tisch', 'ticket': 'Fahrkarte', 'alternative': 'Alternative', 'explanation': 'Erklärung', 'fire': 'Feuer',
        'phrase': 'Phrase', 'command': 'Befehl', 'expression': 'Ausdruck', 'invitation': 'Einladung', 'request': 'Bitte', 'menu': 'Speisekarte', 'bill': 'Rechnung',
        'mall': 'Einkaufszentrum', 'shop': 'Geschäft', 'proposal': 'Vorschlag', 'money': 'Geld', 'apple': 'Apfel', 'apples': 'Äpfel', 'fruit': 'Obst',
        'fruits': 'Obst', 'vegetables': 'Gemüse', 'kitchen': 'Küche', 'chair': 'Stuhl', 'bedroom': 'Schlafzimmer', 'juice': 'Saft', 'clothes': 'Kleidung',
        'shoes': 'Schuhe', 'profit': 'Gewinn', 'investment': 'Investition', 'restaurant': 'Restaurant', 'conversation': 'Gespräch', 'compromise': 'Kompromiss',
        'music': 'Musik', 'dance': 'Tanz', 'art': 'Kunst', 'celebration': 'Feier', 'month': 'Monat', 'next': 'nächste', 'last': 'letzte', 'step': 'Schritt',
        'steps': 'Schritte', 'build': 'Bilde', 'Build': 'Bilde', 'arrange': 'Ordne', 'Arrange': 'Ordne', 'ask': 'Frage', 'Ask': 'Frage', 'careful': 'vorsichtig',
        'slippery': 'rutschig', 'acceptable': 'akzeptabel', 'both': 'beide', 'reduce': 'senken', 'explain': 'erklären', 'repeat': 'wiederholen', 'call': 'anrufen',
        'clean': 'putzen', 'sit': 'sitzen', 'try': 'versuchen', 'trying': 'versuchen', 'stay': 'bleiben', 'rain': 'regnen', 'sunny': 'sonnig', 'final': 'letztes',
        'traditional': 'traditionell', 'mutual': 'gemeinsam', 'satisfied': 'zufrieden', 'delicious': 'köstlich', 'successful': 'erfolgreich', 'trapped': 'gefangen',
        'happily': 'glücklich', 'away': 'weg', 'later': 'später', 'next': 'nächste', 'thank': 'danken', 'thank you': 'danke', 'hello': 'Hallo', 'speak': 'sprechen',
        'means': 'bedeutet', 'actions': 'Handlungen', 'vocabulary': 'Vokabular', 'words': 'Wörter', 'phrases': 'Phrasen', 'manager': 'Manager', 'partner': 'Partner',
        'product': 'Produkt', 'service': 'Dienstleistung', 'office': 'Büro', 'loss': 'Verlust', 'salary': 'Gehalt', 'strategy': 'Strategie', 'customer': 'Kunde',
    },
    'it': {
        'I': 'Io', 'i': 'io', 'You': 'Tu', 'you': 'tu', 'We': 'Noi', 'we': 'noi', 'They': 'Loro', 'they': 'loro', 'He': 'Lui', 'he': 'lui',
        'She': 'Lei', 'she': 'lei', 'It': 'Esso', 'it': 'esso', 'My': 'Mio', 'my': 'mio', 'Your': 'Tuo', 'your': 'tuo', 'Our': 'Nostro', 'our': 'nostro',
        'Their': 'Loro', 'their': 'loro', 'What': 'Che cosa', 'what': 'che cosa', 'Where': 'Dove', 'where': 'dove', 'When': 'Quando', 'when': 'quando',
        'Who': 'Chi', 'who': 'chi', 'Why': 'Perché', 'why': 'perché', 'Which': 'Quale', 'which': 'quale', 'How': 'Come', 'how': 'come', 'Can': 'Può', 'can': 'può',
        'Could': 'Potrebbe', 'could': 'potrebbe', 'Will': 'Sarà', 'will': 'sarà', 'Would': 'Vorrebbe', 'would': 'vorrebbe', 'Should': 'Dovrebbe', 'should': 'dovrebbe',
        'Must': 'Deve', 'must': 'deve', 'have': 'avere', 'has': 'ha', 'had': 'aveva', 'is': 'è', 'are': 'sono', 'am': 'sono', 'was': 'era', 'were': 'erano',
        'be': 'essere', 'been': 'stato', 'do': 'fare', 'does': 'fa', 'did': 'fece', 'not': 'non', 'no': 'no', 'yes': 'sì', 'and': 'e', 'or': 'o', 'but': 'ma',
        'because': 'perché', 'if': 'se', 'then': 'poi', 'than': 'di', 'the': 'il', 'a': 'un', 'an': 'un', 'this': 'questo', 'that': 'quello', 'these': 'questi',
        'those': 'quelli', 'in': 'in', 'on': 'su', 'at': 'a', 'to': 'a', 'from': 'da', 'for': 'per', 'with': 'con', 'without': 'senza', 'about': 'su', 'into': 'in',
        'of': 'di', 'as': 'come', 'by': 'da', 'before': 'prima', 'after': 'dopo', 'more': 'più', 'less': 'meno', 'many': 'molti', 'much': 'molto', 'some': 'alcuni',
        'all': 'tutti', 'other': 'altro', 'another': 'un altro', 'same': 'stesso', 'different': 'diverso', 'correct': 'corretto', 'wrong': 'sbagliato', 'true': 'vero',
        'false': 'falso', 'new': 'nuovo', 'old': 'vecchio', 'big': 'grande', 'small': 'piccolo', 'good': 'buono', 'bad': 'cattivo', 'beautiful': 'bello', 'important': 'importante',
        'possible': 'possibile', 'impossible': 'impossibile', 'expensive': 'costoso', 'cheap': 'economico', 'hot': 'caldo', 'cold': 'freddo', 'today': 'oggi', 'now': 'ora',
        'later': 'più tardi', 'again': 'di nuovo', 'already': 'già', 'every': 'ogni', 'week': 'settimana', 'year': 'anno', 'time': 'tempo', 'day': 'giorno', 'word': 'parola',
        'sentence': 'frase', 'question': 'domanda', 'answer': 'risposta', 'activity': 'attività', 'color': 'colore', 'sky': 'cielo', 'image': 'immagine', 'picture': 'immagine',
        'person': 'persona', 'boy': 'ragazzo', 'girl': 'ragazza', 'children': 'bambini', 'family': 'famiglia', 'member': 'membro', 'place': 'luogo', 'airport': 'aeroporto',
        'cashier': 'cassiere', 'luggage': 'bagaglio', 'train': 'treno', 'clock': 'orologio', 'temperature': 'temperatura', 'weather': 'meteo', 'furniture': 'mobile',
        'utensil': 'utensile', 'money': 'denaro', 'home': 'casa', 'forest': 'foresta', 'story': 'storia', 'stories': 'storie', 'concept': 'concetto', 'element': 'elemento',
        'conditions': 'condizioni', 'risks': 'rischi', 'benefits': 'vantaggi', 'kind': 'tipo', 'direction': 'direzione', 'emotion': 'emozione', 'opinion': 'opinione',
        'future': 'futuro', 'negotiation': 'negoziazione', 'doing': 'fare', 'buy': 'comprare', 'bought': 'comprato', 'drink': 'bere', 'drank': 'bevuto', 'eat': 'mangiare',
        'ate': 'mangiato', 'go': 'andare', 'went': 'andato', 'going': 'andare', 'come': 'venire', 'came': 'venuto', 'meet': 'incontrare', 'met': 'incontrato', 'see': 'vedere',
        'saw': 'vide', 'read': 'leggere', 'wrote': 'scrisse', 'write': 'scrivere', 'copied': 'copiato', 'signed': 'firmato', 'sign': 'firmare', 'travel': 'viaggiare', 'traveling': 'viaggiare',
        'traveled': 'viaggiato', 'celebrate': 'celebrare', 'celebrates': 'celebra', 'celebrated': 'celebrò', 'need': 'avere bisogno', 'needs': 'ha bisogno', 'want': 'volere',
        'like': 'piacere', 'think': 'pensare', 'feel': 'sentire', 'excited': 'entusiasta', 'work': 'lavorare', 'working': 'lavorando', 'help': 'aiutare', 'helped': 'aiutò',
        'close': 'chiudere', 'open': 'aprire', 'leave': 'partire', 'leaves': 'parte', 'sleep': 'dormire', 'wake': 'svegliarsi', 'cook': 'cucinare', 'cooks': 'cucina',
        'order': 'ordinare', 'ordering': 'ordinando', 'reach': 'raggiungere', 'plan': 'pianificare', 'decide': 'decidere', 'provide': 'fornire', 'happen': 'succedere',
        'study': 'studiare', 'fail': 'fallire', 'succeed': 'riuscire', 'play': 'giocare', 'show': 'mostrare', 'indicate': 'indicare', 'mean': 'significare', 'definitely': 'sicuramente',
        'maybe': 'forse', 'proud': 'orgoglioso', 'calm': 'calmo', 'lonely': 'solo', 'scared': 'spaventato', 'angry': 'arrabbiato', 'sad': 'triste', 'wife': 'moglie',
        'bag': 'borsa', 'bed': 'letto', 'book': 'libro', 'car': 'auto', 'contract': 'contratto', 'decision': 'decisione', 'discount': 'sconto', 'map': 'mappa',
        'passport': 'passaporto', 'project': 'progetto', 'receipt': 'ricevuta', 'repetition': 'ripetizione', 'report': 'rapporto', 'reservation': 'prenotazione',
        'table': 'tavolo', 'ticket': 'biglietto', 'alternative': 'alternativa', 'explanation': 'spiegazione', 'fire': 'fuoco', 'phrase': 'frase', 'command': 'comando',
        'expression': 'espressione', 'invitation': 'invito', 'request': 'richiesta', 'menu': 'menu', 'bill': 'conto', 'mall': 'centro commerciale', 'shop': 'negozio',
        'proposal': 'proposta', 'money': 'denaro', 'apple': 'mela', 'apples': 'mele', 'fruit': 'frutta', 'fruits': 'frutta', 'vegetables': 'verdure', 'kitchen': 'cucina',
        'chair': 'sedia', 'bedroom': 'camera da letto', 'juice': 'succo', 'clothes': 'vestiti', 'shoes': 'scarpe', 'profit': 'profitto', 'investment': 'investimento',
        'restaurant': 'ristorante', 'conversation': 'conversazione', 'compromise': 'compromesso', 'music': 'musica', 'dance': 'danza', 'art': 'arte', 'celebration': 'celebrazione',
        'month': 'mese', 'next': 'prossima', 'last': 'scorsa', 'step': 'passo', 'steps': 'passi', 'build': 'Costruisci', 'Build': 'Costruisci', 'arrange': 'Disponi',
        'Arrange': 'Disponi', 'ask': 'Chiedi', 'Ask': 'Chiedi', 'careful': 'attento', 'slippery': 'scivoloso', 'acceptable': 'accettabile', 'both': 'entrambi',
        'reduce': 'ridurre', 'explain': 'spiegare', 'repeat': 'ripetere', 'call': 'chiamare', 'clean': 'pulire', 'sit': 'sedersi', 'try': 'provare', 'trying': 'provando',
        'stay': 'restare', 'rain': 'piovere', 'sunny': 'soleggiato', 'final': 'finale', 'traditional': 'tradizionale', 'mutual': 'reciproco', 'satisfied': 'soddisfatto',
        'delicious': 'delizioso', 'successful': 'riuscito', 'trapped': 'intrappolato', 'happily': 'felicemente', 'away': 'via', 'thank': 'ringraziare', 'thank you': 'grazie',
        'hello': 'ciao', 'speak': 'parlare', 'means': 'significa', 'actions': 'azioni', 'vocabulary': 'vocabolario', 'words': 'parole', 'phrases': 'frasi',
        'manager': 'manager', 'partner': 'partner', 'product': 'prodotto', 'service': 'servizio', 'office': 'ufficio', 'loss': 'perdita', 'salary': 'stipendio',
        'strategy': 'strategia', 'customer': 'cliente',
    },
}

PROTECTED_KEYS = {
    'id', 'type', 'xp_reward', 'difficulty', 'time_limit_seconds', 'topic',
    'audio', 'audio_url', 'audio_file', 'tts_audio', 'image', 'image_url'
}

SPECIAL_KEYS = {'answer', 'correct_answer', 'correct_sentence', 'correct_response', 'tts_text', 'prompt', 'statement', 'context_sentence', 'bot_message', 'text_with_blank', 'sentence', 'word', 'front'}

_REPLACEMENT_CACHE = {}


def apply_replacements(value: str, replacements: dict[str, str]) -> str:
    cache_key = tuple(sorted(replacements.items()))
    compiled = _REPLACEMENT_CACHE.get(cache_key)
    if compiled is None:
        lookup = {source.casefold(): target for source, target in replacements.items()}
        pattern = re.compile(r'(?<!\w)(?:' + '|'.join(re.escape(source) for source in sorted(replacements, key=len, reverse=True)) + r')(?!\w)', re.IGNORECASE)
        compiled = (pattern, lookup)
        _REPLACEMENT_CACHE[cache_key] = compiled
    pattern, lookup = compiled
    return pattern.sub(lambda match: lookup.get(match.group(0).casefold(), match.group(0)), value)


def apply_translation_layers(value: str, replacements: dict[str, str], fallback: dict[str, str]) -> str:
    cache_key = tuple(sorted(replacements.items()))
    compiled = _REPLACEMENT_CACHE.get(cache_key)
    if compiled is None:
        apply_replacements('', replacements)
        compiled = _REPLACEMENT_CACHE[cache_key]
    pattern, lookup = compiled
    protected = {}

    def protect(match):
        marker = f'\x00{len(protected)}\x00'
        protected[marker] = lookup.get(match.group(0).casefold(), match.group(0))
        return marker

    result = pattern.sub(protect, value)
    result = apply_replacements(result, fallback)
    for marker, translated in protected.items():
        result = result.replace(marker, translated)
    return result


def translate_text(value: str, lang: str) -> str:
    if not isinstance(value, str):
        return value
    if value.startswith('http://') or value.startswith('https://'):
        return value
    replacements = {**COMMON_TRANSLATIONS[lang], **LANGUAGES[lang]['replacements']}
    result = apply_translation_layers(value, replacements, FALLBACK_WORDS[lang])
    result = re.sub(r"How do you say '(.+?)' in Kinyarwanda\?", lambda m: f"Come si dice '{m.group(1)}' in kinyarwanda?" if lang == 'it' else f"Wie sagt man '{m.group(1)}' auf Kinyarwanda?", result)
    result = re.sub(r"What does '(.+?)' mean\?", lambda m: f"Che cosa significa '{m.group(1)}'?" if lang == 'it' else f"Was bedeutet '{m.group(1)}'?", result)
    result = re.sub(r"What is (.+?)\?", lambda m: f"Cos'è {m.group(1)}?" if lang == 'it' else f"Was ist {m.group(1)}?", result)
    return result


def walk(value, key='', lang='de'):
    if isinstance(value, dict):
        result = {}
        entry_type = value.get('type')
        for child_key, child in value.items():
            if child_key in PROTECTED_KEYS:
                result[child_key] = copy.deepcopy(child)
            elif child_key in SPECIAL_KEYS and entry_type not in {'multiple_choice', 'listen_and_choose', 'choose_missing', 'fill_blank', 'read_answer', 'identify_meaning'}:
                result[child_key] = copy.deepcopy(child)
            elif child_key in {'answer', 'correct_answer'} and entry_type in {'multiple_choice', 'listen_and_choose', 'choose_missing', 'fill_blank', 'read_answer', 'identify_meaning'}:
                result[child_key] = walk(child, child_key, lang)
            else:
                result[child_key] = walk(child, child_key, lang)
        return result
    if isinstance(value, list):
        if key in {'rows'}:
            return [[translate_text(row[0], lang), *copy.deepcopy(row[1:])] if isinstance(row, list) and row else walk(row, key, lang) for row in value]
        if key in {'options', 'word_bank', 'scrambled_words', 'questions'}:
            return [translate_text(item, lang) if isinstance(item, str) else walk(item, key, lang) for item in value]
        return [walk(item, key, lang) for item in value]
    if isinstance(value, str):
        return translate_text(value, lang)
    return value


def second_layer_cleanup(value, parent_key=None, lang='de'):
    if isinstance(value, dict):
        return {k: second_layer_cleanup(v, k, lang) for k, v in value.items()}
    if isinstance(value, list):
        return [second_layer_cleanup(item, parent_key, lang) for item in value]
    if isinstance(value, str):
        if parent_key in {'left', 'right', 'front', 'back'}:
            return value
        if value.startswith('http://') or value.startswith('https://'):
            return value
        replacements = {**COMMON_TRANSLATIONS[lang], **LANGUAGES[lang]['replacements']}
        result = apply_replacements(value, replacements)
        return result
    return value


def generate_for_language(lang: str):
    cfg = LANGUAGES[lang]
    folder = Path('content') / cfg['folder']
    folder.mkdir(parents=True, exist_ok=True)

    for source_file in sorted(SOURCE_ROOT.rglob('*.yaml')):
        relative = source_file.relative_to(SOURCE_ROOT)
        output_file = folder / relative
        output_file.parent.mkdir(parents=True, exist_ok=True)

        source_data = yaml.safe_load(source_file.read_text(encoding='utf-8').replace("\\'", "'")) or {}
        translated = walk(source_data, lang=lang)
        translated = second_layer_cleanup(translated, lang=lang)
        output_file.write_text(yaml.safe_dump(translated, allow_unicode=True, sort_keys=False, width=120), encoding='utf-8')
        print(f'Wrote {output_file}')


def validate_generated(lang: str):
    cfg = LANGUAGES[lang]
    source_files = sorted(SOURCE_ROOT.rglob('*.yaml'))
    output_files = sorted((Path('content') / cfg['folder']).rglob('*.yaml'))
    assert len(source_files) == len(output_files), (lang, len(source_files), len(output_files))
    for sf, of in zip(source_files, output_files):
        s = yaml.safe_load(sf.read_text(encoding='utf-8').replace("\\'", "'"))
        o = yaml.safe_load(of.read_text(encoding='utf-8'))
        assert len(s.get('exercises', [])) == len(o.get('exercises', [])), (lang, sf, len(s.get('exercises', [])), len(o.get('exercises', [])))
        assert [e.get('id') for e in s.get('exercises', [])] == [e.get('id') for e in o.get('exercises', [])], (lang, sf)
        assert [e.get('xp_reward') for e in s.get('exercises', [])] == [e.get('xp_reward') for e in o.get('exercises', [])], (lang, sf)
    print(f'VALID: {lang.upper()} course generated with matching exercise IDs and XP values')


if __name__ == '__main__':
    for lang in ['de', 'it']:
        generate_for_language(lang)
        validate_generated(lang)
