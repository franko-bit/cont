from pathlib import Path
import copy
import re
import yaml

SOURCE_ROOT = Path('content/EN-TO-RW/EN-TO-RW')
OUTPUT_ROOT = Path('content/ES-TO-RW')

TITLE_MAP = {
    'Greetings': 'Saludos',
    'Numbers': 'Números',
    'Family': 'Familia',
    'Colors': 'Colores',
    'Animals': 'Animales',
    'Food': 'Comida',
    'Daily Routines': 'Rutinas diarias',
    'Weather': 'Clima',
    'Clothing': 'Ropa',
    'House': 'Casa',
    'Travel': 'Viajes',
    'Shopping': 'Compras',
    'Restaurant': 'Restaurante',
    'Directions': 'Direcciones',
    'Emotions': 'Emociones',
    'Past Tense': 'Tiempo pasado',
    'Future Tense': 'Tiempo futuro',
    'Conditionals': 'Condicionales',
    'Opinions': 'Opiniones',
    'Storytelling': 'Narración',
    'Culture': 'Cultura',
    'Days': 'Días',
    'Business': 'Negocios',
    'Debates': 'Debates',
    'Negotiations': 'Negociaciones',
    'Fluency': 'Fluidez',
}

PHRASE_MAP = {
    'How do you say': '¿Cómo se dice',
    'How do you ask': '¿Cómo se pregunta',
    'in Kinyarwanda?': 'en kinyarwanda?',
    'What is': '¿Qué es',
    'What does': '¿Qué significa',
    'mean?': 'en esta frase?',
    'Listen and type the animal name': 'Escucha y escribe el nombre del animal',
    'Listen and choose the correct animal': 'Escucha y elige el animal correcto',
    'Say this word': 'Di esta palabra',
    'Say this sentence': 'Di esta frase',
    'Build the Kinyarwanda sentence for': 'Construye la frase en kinyarwanda para',
    'Arrange these words to form a correct Kinyarwanda sentence': 'Ordena estas palabras para formar una frase correcta en kinyarwanda',
    'Choose the correct word to complete the sentence': 'Elige la palabra correcta para completar la frase',
    'What animal is this?': '¿Qué animal es este?',
    'Match the animals': 'Relaciona los animales',
    'Tap the words you hear in order': 'Toca las palabras que escuchas en el orden correcto',
    'Choose the missing word': 'Elige la palabra que falta',
    'Where is the cow?': '¿Dónde está la vaca?',
    'What does the lion eat?': '¿Qué come el león?',
    'Is this statement true or false?': '¿Esta afirmación es verdadera o falsa?',
    'Respond to the question by building a sentence': 'Responde a la pregunta formando una frase',
    'Respond to the question': 'Responde a la pregunta',
    'Read about the animals. Then build the sentences.': 'Lee sobre los animales. Luego forma las frases.',
    'Flashcards': 'Tarjetas de memoria',
    'Match Madness: Animals': 'Desafío de relaciones: Animales',
    'Lightning Round: Animals': 'Ronda relámpago: Animales',
    'Learn the key vocabulary and practice exercises for this lesson.': 'Aprende el vocabulario clave y practica los ejercicios de esta lección.',
    'Welcome to this lesson! In this unit, you will build vocabulary, sentence patterns, and practical speaking skills.': 'Bienvenido a esta lección. En esta unidad aprenderás vocabulario, estructuras de frases y habilidades prácticas para hablar.',
    'Core words': 'Palabras básicas',
    'Key Phrases': 'Frases clave',
    'Grammar Tips': 'Consejos de gramática',
    'Sentence pattern': 'Estructura de la frase',
    'Use the lesson vocabulary in short, clear sentences.': 'Usa el vocabulario de la lección en frases cortas y claras.',
    'Unit Goal': 'Objetivo de la unidad',
    'Quick Reference': 'Referencia rápida',
    'Example Sentences': 'Frases de ejemplo',
    'Learn the main vocabulary of this lesson': 'Aprende el vocabulario principal de esta lección',
    'Use the new words in simple sentences': 'Usa las palabras nuevas en frases sencillas',
    'Practice listening, speaking, and translation': 'Practica la comprensión auditiva, la expresión oral y la traducción',
    'Total XP Available: 500 XP': 'XP total disponible: 500 XP',
    'By the end of this lesson, you will be able to use the main vocabulary and simple sentence patterns for this topic with confidence.': 'Al final de esta lección, podrás usar con confianza el vocabulario principal y estructuras sencillas de frases sobre este tema.',
    'Good luck! Continue with the exercises to reinforce your learning. 🔥': '¡Buena suerte! Continúa con los ejercicios para reforzar tu aprendizaje. 🔥',
    'Practice': 'Practica',
    'Learn': 'Aprende',
    'Vocabulary': 'Vocabulario',
    'Grammar': 'Gramática',
    'Sentence': 'Frase',
    'Examples': 'Ejemplos',
    'Listen': 'Escucha',
    'Read': 'Lee',
    'Write': 'Escribe',
    'Speak': 'Habla',
    'Choose': 'Elige',
    'Match': 'Relaciona',
    'Answer': 'Respuesta',
    'Question': 'Pregunta',
    'Level': 'Nivel',
}

PROTECTED_KEYS = {
    'id', 'type', 'xp_reward', 'difficulty', 'time_limit_seconds', 'topic',
    'audio', 'audio_url', 'audio_file', 'tts_audio', 'image', 'image_url', 'link'
}

SPECIAL_PROTECTED_KEYS = {
    'answer', 'correct_answer', 'correct_sentence', 'correct_response',
    'tts_text', 'prompt', 'statement', 'context_sentence', 'bot_message',
    'text_with_blank', 'sentence', 'word', 'front'
}

COMMON_REPLACEMENTS = {
    'Animals': 'Animales',
    'Animal': 'Animal',
    'Cow': 'Vaca',
    'Dog': 'Perro',
    'Cat': 'Gato',
    'Goat': 'Cabra',
    'Sheep': 'Oveja',
    'Pig': 'Cerdo',
    'Chicken': 'Pollo',
    'Rabbit': 'Conejo',
    'Donkey': 'Burro',
    'Pigeon': 'Paloma',
    'Bird': 'Pájaro',
    'Lion': 'León',
    'Elephant': 'Elefante',
    'Mountain gorilla': 'Gorila de montaña',
    'Leopard': 'Leopardo',
    'Buffalo': 'Búfalo',
    'Zebra': 'Cebra',
    'Hippopotamus': 'Hipopótamo',
    'Giraffe': 'Jirafa',
    'Hyena': 'Hiena',
    'Monkey': 'Mono',
    'Grey crowned crane': 'Grulla coronada gris',
    'Snake': 'Serpiente',
    'Frog': 'Rana',
    'Fish': 'Pez',
    'Bee': 'Abeja',
    'eats': 'come',
    'drinks': 'bebe',
    'sleeps': 'duerme',
    'runs': 'corre',
    'flies': 'vuela',
    'swims': 'nada',
    'plays': 'juega',
    'grass': 'hierba',
    'water': 'agua',
    'meat': 'carne',
    'milk': 'leche',
    'food': 'comida',
    'above': 'arriba',
    'near': 'cerca de',
    'field': 'campo',
    'house': 'casa',
    'market': 'mercado',
    'Welcome': 'Bienvenido',
    'Lesson': 'Lección',
    'learn': 'aprende',
    'English': 'Inglés',
    'French': 'Francés',
    'Spanish': 'Español',
    'Kinyarwanda': 'Kinyarwanda',
    'German': 'Alemán',
    'Italian': 'Italiano',
    'Swahili': 'Swahili',
    'Name': 'Nombre',
    'Date': 'Fecha',
    'Time': 'Hora',
    'Place': 'Lugar',
    'Family': 'Familia',
    'Colors': 'Colores',
    'Weather': 'Clima',
    'Travel': 'Viajes',
    'Shopping': 'Compras',
    'Restaurant': 'Restaurante',
    'Directions': 'Direcciones',
    'Emotions': 'Emociones',
    'Numbers': 'Números',
    'Greetings': 'Saludos',
    'School': 'Escuela',
    'Friend': 'Amigo',
    'Mother': 'Madre',
    'Father': 'Padre',
    'Brother': 'Hermano',
    'Sister': 'Hermana',
    'Child': 'Niño',
    'Children': 'Niños',
    'Home': 'Casa',
    'Work': 'Trabajo',
    'Day': 'Día',
    'Night': 'Noche',
    'Rain': 'Lluvia',
    'Sun': 'Sol',
    'Cloud': 'Nube',
    'Blue': 'Azul',
    'Red': 'Rojo',
    'Green': 'Verde',
    'Yellow': 'Amarillo',
    'Black': 'Negro',
    'White': 'Blanco',
    'Orange': 'Naranja',
    'Brown': 'Marrón',
    'Purple': 'Morado',
    'Good morning': 'Buenos días',
    'Good evening': 'Buenas noches',
    'Goodbye': 'Adiós',
    'Hello': 'Hola',
    'How are you?': '¿Cómo estás?',
    'I am fine': 'Estoy bien',
    'What is your name?': '¿Cómo te llamas?',
    'My name is': 'Me llamo',
    'This is my family': 'Esta es mi familia',
    'How old are you?': '¿Cuántos años tienes?',
    'I am happy': 'Estoy feliz',
    'I am sad': 'Estoy triste',
    'I am tired': 'Estoy cansado',
    'I am hungry': 'Tengo hambre',
    'I am thirsty': 'Tengo sed',
    'The cow eats grass': 'La vaca come hierba',
    'The dog drinks water': 'El perro bebe agua',
    'The bird flies above': 'El pájaro vuela arriba',
    'A dog is an animal': 'Un perro es un animal',
    'A cow flies': 'Una vaca vuela',
    'A fish swims': 'Un pez nada',
    'Left': 'Izquierda',
    'left': 'izquierda',
    'Right': 'Derecha',
    'right': 'derecha',
    'Straight': 'Recto',
    'straight': 'recto',
    'Front': 'Delante',
    'front': 'delante',
    'Behind': 'Detrás',
    'behind': 'detrás',
    'North': 'Norte',
    'north': 'norte',
    'South': 'Sur',
    'south': 'sur',
    'East': 'Este',
    'east': 'este',
    'West': 'Oeste',
    'west': 'oeste',
    'Near': 'Cerca',
    'near': 'cerca',
    'Far': 'Lejos',
    'far': 'lejos',
    'Around': 'Alrededor',
    'around': 'alrededor',
    'Turn left': 'Gira a la izquierda',
    'turn left': 'gira a la izquierda',
    'Turn right': 'Gira a la derecha',
    'turn right': 'gira a la derecha',
    'Go straight': 'Sigue recto',
    'go straight': 'sigue recto',
    'Go back': 'Vuelve',
    'go back': 'vuelve',
    'Cross the street': 'Cruza la calle',
    'cross the street': 'cruza la calle',
    'The market is near': 'El mercado está cerca',
    'The hospital is far': 'El hospital está lejos',
    'The bank is in front of the school': 'El banco está delante de la escuela',
    'The mercado is cerca de': 'El mercado está cerca de',
    'The hospital is far.': 'El hospital está lejos.',
    'The bank is in front of the school.': 'El banco está delante de la escuela.',
    'What does the lion eat?': '¿Qué come el león?',
    'Back': 'Atrás',
    'back': 'atrás',
    'Stop': 'Detente',
    'stop': 'detente',
    'street': 'calle',
    'market': 'mercado',
    'school': 'escuela',
    'bank': 'banco',
    'hospital': 'hospital',
    'House': 'Casa',
    'house': 'casa',
    'Room': 'Habitación',
    'room': 'habitación',
    'Door': 'Puerta',
    'door': 'puerta',
    'Window': 'Ventana',
    'window': 'ventana',
    'Roof': 'Tejado',
    'roof': 'tejado',
    'Wall': 'Pared',
    'wall': 'pared',
    'Floor': 'Suelo',
    'floor': 'suelo',
    'Happy': 'Feliz',
    'happy': 'feliz',
    'Sad': 'Triste',
    'sad': 'triste',
    'Angry': 'Enfadado',
    'angry': 'enfadado',
    'Scared': 'Asustado',
    'scared': 'asustado',
    'Listen': 'Escucha',
    'listen': 'escucha',
    'Talk': 'Habla',
    'talk': 'habla',
    'Understand': 'Entiende',
    'understand': 'entiende',
    'Repeat': 'Repite',
    'repeat': 'repite',
    'Pronounce': 'Pronuncia',
    'pronounce': 'pronuncia',
    'Clarify': 'Aclara',
    'clarify': 'aclara',
    'Slowly': 'Despacio',
    'slowly': 'despacio',
    'Traffic light': 'Semáforo',
    'traffic light': 'semáforo',
    'Sign': 'Señal',
    'sign': 'señal',
    'Corner': 'Esquina',
    'corner': 'esquina',
    'Intersection': 'Intersección',
    'intersection': 'intersección',
    'Crossroads': 'Cruce',
    'crossroads': 'cruce',
    'Market': 'Mercado',
    'market': 'mercado',
    'Bank': 'Banco',
    'bank': 'banco',
    'School': 'Escuela',
    'school': 'escuela',
    'Hospital': 'Hospital',
    'hospital': 'hospital',
    'Place': 'Lugar',
    'place': 'lugar',
    'If': 'Si',
    'if': 'si',
    'When': 'Cuando',
    'when': 'cuando',
    'Then': 'Entonces',
    'then': 'entonces',
    'Before': 'Antes',
    'before': 'antes',
    'After': 'Después',
    'after': 'después',
    'Tomorrow': 'Mañana',
    'tomorrow': 'mañana',
    'Yesterday': 'Ayer',
    'yesterday': 'ayer',
    'Always': 'Siempre',
    'always': 'siempre',
    'Never': 'Nunca',
    'never': 'nunca',
    'Good morning': 'Buenos días',
    'Good evening': 'Buenas noches',
    'Hello': 'Hola',
    'Hello!': '¡Hola!',
    'Goodbye': 'Adiós',
    'Business': 'Negocios',
    'Company': 'Empresa',
    'Employee': 'Empleado',
    'Employer': 'Empleador',
    'Boss': 'Jefe',
    'Client': 'Cliente',
    'Meeting': 'Reunión',
    'Project': 'Proyecto',
    'Price': 'Precio',
    'Cost': 'Costo',
    'Offer': 'Oferta',
    'Accept': 'Aceptar',
    'Reject': 'Rechazar',
    'Agreement': 'Acuerdo',
    'Deal': 'Trato',
    'Culture': 'Cultura',
    'Tradition': 'Tradición',
    'Custom': 'Costumbre',
    'Ceremony': 'Ceremonia',
    'Festival': 'Festival',
    'Heritage': 'Patrimonio',
    'People': 'Personas',
    'Road': 'Carretera',
    'Street': 'Calle',
    'Traffic light': 'Semáforo',
    'Sign': 'Señal',
    'Signpost': 'Señal',
    'Corner': 'Esquina',
    'Intersection': 'Intersección',
    'Crossroads': 'Cruce',
    'Market': 'Mercado',
    'Hospital': 'Hospital',
    'Bank': 'Banco',
    'School': 'Escuela',
    'Place': 'Lugar',
    'Next week': 'La próxima semana',
    'Tomorrow': 'Mañana',
    'Yesterday': 'Ayer',
    'Always': 'Siempre',
    'Never': 'Nunca',
    'If': 'Si',
    'When': 'Cuando',
    'Then': 'Entonces',
    'Before': 'Antes',
    'After': 'Después',
    'Breakfast': 'Desayuno',
    'Lunch': 'Almuerzo',
    'Dinner': 'Cena',
    'Day': 'Día',
    'Night': 'Noche',
    'Sun': 'Sol',
    'Rain': 'Lluvia',
    'Wind': 'Viento',
    'Cloud': 'Nube',
    'Snow': 'Nieve',
    'Storm': 'Tormenta',
}


def translate_text(text):
    if not isinstance(text, str):
        return text
    result = text
    for source, target in sorted({**PHRASE_MAP, **COMMON_REPLACEMENTS}.items(), key=lambda item: len(item[0]), reverse=True):
        result = result.replace(source, target)
    result = re.sub(r"How do you say '(.+?)' in Kinyarwanda\?", r"¿Cómo se dice '\1' en kinyarwanda?", result)
    result = re.sub(r"How do you ask '(.+?)' en kinyarwanda\?", r"¿Cómo se pregunta '\1' en kinyarwanda?", result)
    result = re.sub(r"What does '(.+?)' mean\?", r"¿Qué significa '\1'?", result)
    result = re.sub(r"What is (.+?)\?", r"¿Qué es \1?", result)
    result = re.sub(r"Choose the correct (.+?) to complete the sentence", r"Elige la palabra correcta para completar la frase", result)
    result = re.sub(r"Translate the following sentence into Kinyarwanda:", r"Traduce la siguiente frase al kinyarwanda:", result)
    result = re.sub(r"Read the sentence and choose the correct answer", r"Lee la frase y elige la respuesta correcta", result)
    result = re.sub(r"Listen and write the correct word", r"Escucha y escribe la palabra correcta", result)
    result = re.sub(r"Select the correct option", r"Selecciona la opción correcta", result)
    return result


def walk(value, key=''):
    if isinstance(value, dict):
        result = {}
        entry_type = value.get('type')
        for child_key, child in value.items():
            if child_key in PROTECTED_KEYS:
                result[child_key] = copy.deepcopy(child)
            elif child_key in SPECIAL_PROTECTED_KEYS and entry_type not in {'multiple_choice', 'listen_and_choose', 'choose_missing', 'fill_blank', 'read_answer', 'identify_meaning'}:
                result[child_key] = copy.deepcopy(child)
            elif child_key in {'answer', 'correct_answer'} and entry_type in {'multiple_choice', 'listen_and_choose', 'choose_missing', 'fill_blank', 'read_answer', 'identify_meaning'}:
                result[child_key] = walk(child, child_key)
            else:
                result[child_key] = walk(child, child_key)
        return result
    if isinstance(value, list):
        if key in {'rows'}:
            return [[translate_text(row[0]), *copy.deepcopy(row[1:])] if isinstance(row, list) and row else walk(row, key) for row in value]
        if key in {'options', 'word_bank', 'scrambled_words', 'questions'}:
            return [translate_text(item) if isinstance(item, str) else walk(item, key) for item in value]
        return [walk(item, key) for item in value]
    if isinstance(value, str):
        return translate_text(value)
    return value


def second_layer_cleanup(value, parent_key=None):
    if isinstance(value, dict):
        cleaned = {}
        for k, v in value.items():
            cleaned[k] = second_layer_cleanup(v, k)
        return cleaned
    if isinstance(value, list):
        return [second_layer_cleanup(item, parent_key) for item in value]
    if isinstance(value, str):
        if parent_key in {'left', 'right', 'front', 'back'}:
            return value
        result = value
        for source, target in sorted({**COMMON_REPLACEMENTS}.items(), key=lambda item: len(item[0]), reverse=True):
            result = result.replace(source, target)
        return result
    return value


def build_title_from_source(source_path: Path) -> str:
    base = source_path.stem
    return TITLE_MAP.get(base.replace('-', ' ').title().replace(' ', ' '), base.replace('-', ' ').title())


def main():
    for source_file in sorted(SOURCE_ROOT.rglob('*.yaml')):
        relative = source_file.relative_to(SOURCE_ROOT)
        output_file = OUTPUT_ROOT / relative
        output_file.parent.mkdir(parents=True, exist_ok=True)

        source = yaml.safe_load(source_file.read_text(encoding='utf-8').replace("\\'", "'")) or {}
        transformed = walk(source)
        transformed = second_layer_cleanup(transformed)

        if 'lesson' in transformed and isinstance(transformed['lesson'], dict):
            lesson_name = transformed['lesson'].get('name')
            if lesson_name:
                transformed['lesson']['name'] = lesson_name

        output_file.write_text(yaml.safe_dump(transformed, allow_unicode=True, sort_keys=False, width=120), encoding='utf-8')
        print(f'Wrote {output_file}')


if __name__ == '__main__':
    main()
