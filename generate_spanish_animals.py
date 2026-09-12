from pathlib import Path
import copy
import yaml

SOURCE = Path('content/EN-TO-RW/level1/animals.yaml')
OUTPUT = Path('content/ES-TO-RW/level1/animals.yaml')

TERMS = {
    'Animals': 'Animales', 'Animal': 'Animal', 'Cow': 'Vaca', 'Dog': 'Perro',
    'Cat': 'Gato', 'Goat': 'Cabra', 'Sheep': 'Oveja', 'Pig': 'Cerdo',
    'Chicken': 'Pollo', 'Rabbit': 'Conejo', 'Donkey': 'Burro', 'Pigeon': 'Paloma',
    'Bird': 'Pajaro', 'Lion': 'Leon', 'Elephant': 'Elefante', 'Mountain gorilla': 'Gorila de montana',
    'Leopard': 'Leopardo', 'Buffalo': 'Bufalo', 'Zebra': 'Cebra', 'Hippopotamus': 'Hipopotamo',
    'Giraffe': 'Jirafa', 'Hyena': 'Hiena', 'Monkey': 'Mono', 'Grey crowned crane': 'Grulla coronada gris',
    'Snake': 'Serpiente', 'Frog': 'Rana', 'Fish': 'Pez', 'Bee': 'Abeja',
    'eats': 'come', 'drinks': 'bebe', 'sleeps': 'duerme', 'runs': 'corre',
    'flies': 'vuela', 'swims': 'nada', 'plays': 'juega', 'grass': 'hierba',
    'water': 'agua', 'meat': 'carne', 'milk': 'leche', 'food': 'comida',
    'above': 'arriba', 'near': 'cerca de', 'field': 'campo', 'house': 'casa',
    'market': 'mercado', 'The cow eats grass': 'La vaca come hierba',
    'The dog drinks water': 'El perro bebe agua', 'The bird flies above': 'El pajaro vuela arriba',
    'A dog is an animal': 'Un perro es un animal', 'A cow flies': 'Una vaca vuela',
    'A fish swims': 'Un pez nada', 'The Cow and Friends': 'La vaca y sus amigos',
    'There is a cow in the field.': 'Hay una vaca en el campo.',
    'The cow eats grass.': 'La vaca come hierba.', 'A dog is near the cow.': 'Un perro esta cerca de la vaca.',
    'The dog drinks water.': 'El perro bebe agua.', 'A bird flies above.': 'Un pajaro vuela arriba.',
    'Learn the key vocabulary and practice exercises for this lesson.': 'Aprende el vocabulario clave y practica los ejercicios de esta leccion.',
    'Welcome to this lesson! In this unit, you will build vocabulary, sentence patterns, and practical speaking skills.': 'Bienvenido a esta leccion. En esta unidad aprenderas vocabulario, estructuras de frases y habilidades practicas para hablar.',
    'Core words': 'Palabras basicas', 'Key Phrases': 'Frases clave', 'Grammar Tips': 'Consejos de gramatica',
    'Sentence pattern': 'Estructura de la frase', 'Use the lesson vocabulary in short, clear sentences.': 'Usa el vocabulario de la leccion en frases cortas y claras.',
    'Unit Goal': 'Objetivo de la unidad', 'Quick Reference': 'Referencia rapida', 'Example Sentences': 'Frases de ejemplo',
    'Learn the main vocabulary of this lesson': 'Aprende el vocabulario principal de esta leccion',
    'Use the new words in simple sentences': 'Usa las palabras nuevas en frases sencillas',
    'Practice listening, speaking, and translation': 'Practica la comprension auditiva, la expresion oral y la traduccion',
    'Total XP Available: 500 XP': 'XP total disponible: 500 XP',
    'By the end of this lesson, you will be able to use the main vocabulary and simple sentence patterns for this topic with confidence.': 'Al final de esta leccion, podras usar con confianza el vocabulario principal y estructuras sencillas de frases sobre este tema.',
    'Good luck! Continue with the exercises to reinforce your learning. 🔥': 'Buena suerte. Continua con los ejercicios para reforzar tu aprendizaje. 🔥',
    'How do you say': 'Como se dice', 'in Kinyarwanda?': 'en kinyarwanda?',
    'What is': 'Que es', 'What does': 'Que significa', 'mean?': 'en esta frase?',
    'Listen and type the animal name': 'Escucha y escribe el nombre del animal',
    'Listen and choose the correct animal': 'Escucha y elige el animal correcto',
    'Say this word': 'Di esta palabra', 'Say this sentence': 'Di esta frase',
    'Build the Kinyarwanda sentence for': 'Construye la frase en kinyarwanda para',
    'Arrange these words to form a correct Kinyarwanda sentence': 'Ordena estas palabras para formar una frase correcta en kinyarwanda',
    'Choose the correct word to complete the sentence': 'Elige la palabra correcta para completar la frase',
    'What animal is this?': 'Que animal es este?', 'Match the animals': 'Relaciona los animales',
    'Tap the words you hear in order': 'Toca las palabras que escuchas en el orden correcto',
    'Choose the missing word': 'Elige la palabra que falta', 'Where is the cow?': 'Donde esta la vaca?',
    'What does the lion eat?': 'Que come el leon?', 'Is this statement true or false?': 'Esta afirmacion es verdadera o falsa?',
    'Respond to the question by building a sentence': 'Responde a la pregunta formando una frase',
    'Respond to the question': 'Responde a la pregunta', 'Read about the animals. Then build the sentences.': 'Lee sobre los animales. Luego forma las frases.',
    'Flashcards': 'Tarjetas de memoria', 'Match Madness: Animals': 'Desafio de relaciones: Animales',
    'Lightning Round: Animals': 'Ronda relampago: Animales',
}


def translate_text(text):
    if not isinstance(text, str):
        return text
    if text in TERMS:
        return TERMS[text]
    result = text
    for source, target in sorted(TERMS.items(), key=lambda item: len(item[0]), reverse=True):
        result = result.replace(source, target)
    return result


def walk(value, key=''):
    if isinstance(value, dict):
        result = {}
        entry_type = value.get('type')
        for child_key, child in value.items():
            if child_key in {'id', 'type', 'xp_reward', 'time_limit_seconds', 'image_url', 'audio', 'audio_url', 'audio_file', 'tts_audio'}:
                result[child_key] = copy.deepcopy(child)
            elif child_key == 'answer' and entry_type not in {'multiple_choice', 'listen_and_choose', 'choose_missing', 'fill_blank', 'read_answer', 'identify_meaning'}:
                result[child_key] = copy.deepcopy(child)
            elif child_key == 'correct_answer' and entry_type not in {'multiple_choice', 'listen_and_choose', 'choose_missing', 'fill_blank', 'read_answer', 'identify_meaning'}:
                result[child_key] = copy.deepcopy(child)
            elif child_key in {'correct_sentence', 'correct_response', 'tts_text', 'prompt', 'statement', 'context_sentence', 'bot_message', 'text_with_blank', 'sentence', 'word', 'front'}:
                result[child_key] = copy.deepcopy(child)
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


def main():
    source = yaml.safe_load(SOURCE.read_text(encoding='utf-8'))
    translated = walk(source)
    OUTPUT.parent.mkdir(parents=True, exist_ok=True)
    OUTPUT.write_text(yaml.safe_dump(translated, allow_unicode=True, sort_keys=False, width=120), encoding='utf-8')
    print(f'Wrote {OUTPUT}')


if __name__ == '__main__':
    main()
