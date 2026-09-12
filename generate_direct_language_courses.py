from __future__ import annotations

import copy
import re
from pathlib import Path

import yaml

SOURCE_ROOT = Path('content/EN-TO-RW/EN-TO-RW')

LANGUAGES = {
    'pt': {'folder': 'PT-TO-RW', 'map': {
        'Translate this lesson term': 'Traduza o termo desta lição', 'Answer': 'Resposta', 'How do you say': 'Como se diz', 'in Kinyarwanda?': 'em Kinyarwanda?', 'What is': 'O que é', 'What does': 'O que significa', 'mean?': 'nesta frase?',
        'Learn the key vocabulary and practice exercises for this lesson.': 'Aprenda o vocabulário principal e pratique os exercícios desta lição.',
        'Welcome to this lesson! In this unit, you will build vocabulary, sentence patterns, and practical speaking skills.': 'Bem-vindo a esta lição! Nesta unidade, você aprenderá vocabulário, estruturas de frases e habilidades práticas de conversação.',
        'Core words': 'Palavras principais', 'Key Phrases': 'Frases principais', 'Grammar Tips': 'Dicas de gramática', 'Sentence pattern': 'Estrutura da frase',
        'Unit Goal': 'Objetivo da unidade', 'Quick Reference': 'Referência rápida', 'Example Sentences': 'Frases de exemplo', 'Practice': 'Pratique', 'Learn': 'Aprenda',
        'Vocabulary': 'Vocabulário', 'Grammar': 'Gramática', 'Sentence': 'Frase', 'Examples': 'Exemplos', 'Listen': 'Ouça', 'Read': 'Leia', 'Write': 'Escreva',
        'Speak': 'Fale', 'Choose': 'Escolha', 'Match': 'Associe', 'Answer': 'Resposta', 'Question': 'Pergunta', 'Level': 'Nível', 'Flashcards': 'Cartões de memória',
        'Greetings': 'Saudações', 'Numbers': 'Números', 'Family': 'Família', 'Colors': 'Cores', 'Animals': 'Animais', 'Food': 'Comida', 'Daily Routines': 'Rotinas diárias',
        'Weather': 'Clima', 'Clothing': 'Roupas', 'House': 'Casa', 'Travel': 'Viagens', 'Shopping': 'Compras', 'Restaurant': 'Restaurante', 'Directions': 'Direções',
        'Emotions': 'Emoções', 'Past Tense': 'Passado', 'Future Tense': 'Futuro', 'Conditionals': 'Condicionais', 'Opinions': 'Opiniões', 'Storytelling': 'Contação de histórias',
        'Culture': 'Cultura', 'Days': 'Dias', 'Business': 'Negócios', 'Debates': 'Debates', 'Negotiations': 'Negociações', 'Fluency': 'Fluência',
        'Cow': 'Vaca', 'Dog': 'Cachorro', 'Cat': 'Gato', 'Goat': 'Cabra', 'Sheep': 'Ovelha', 'Pig': 'Porco', 'Chicken': 'Galinha', 'Rabbit': 'Coelho', 'Donkey': 'Burro',
        'Pigeon': 'Pombo', 'Bird': 'Pássaro', 'Lion': 'Leão', 'Elephant': 'Elefante', 'Mountain gorilla': 'Gorila da montanha', 'Leopard': 'Leopardo', 'Buffalo': 'Búfalo',
        'Zebra': 'Zebra', 'Hippopotamus': 'Hipopótamo', 'Giraffe': 'Girafa', 'Hyena': 'Hiena', 'Monkey': 'Macaco', 'Grey crowned crane': ' grou-coroada-cinza', 'Snake': 'Cobra', 'Frog': 'Sapo', 'Fish': 'Peixe', 'Bee': 'Abelha',
        'eats': 'come', 'drinks': 'bebe', 'sleeps': 'dorme', 'runs': 'corre', 'flies': 'voa', 'swims': 'nada', 'plays': 'brinca', 'grass': 'grama', 'water': 'água', 'meat': 'carne', 'milk': 'leite',
        'Left': 'Esquerda', 'Right': 'Direita', 'Straight': 'Em frente', 'Front': 'Frente', 'Behind': 'Atrás', 'Back': 'Voltar', 'Near': 'Perto', 'Far': 'Longe', 'Around': 'Ao redor',
        'Turn left': 'Vire à esquerda', 'Turn right': 'Vire à direita', 'Go straight': 'Siga em frente', 'Go back': 'Volte', 'Cross the street': 'Atravesse a rua',
        'Hello': 'Olá', 'Goodbye': 'Adeus', 'Good morning': 'Bom dia', 'Good evening': 'Boa noite', 'How are you?': 'Como você está?', 'I am fine': 'Estou bem',
        'What is your name?': 'Como você se chama?', 'My name is': 'Meu nome é', 'This is my family': 'Esta é minha família', 'How old are you?': 'Quantos anos você tem?',
        'I am happy': 'Estou feliz', 'I am sad': 'Estou triste', 'I am tired': 'Estou cansado', 'I am hungry': 'Estou com fome', 'I am thirsty': 'Estou com sede',
        'Aprenda Comida items in Kinyarwanda and how to talk about them': 'Aprenda alimentos comuns em Kinyarwanda e como falar sobre eles', 'Aprenda common Saudações and expressions in Kinyarwanda': 'Aprenda saudações e expressões comuns em Kinyarwanda',
        'I ate': 'Eu comi', 'You ate': 'Você comeu', 'He ate': 'Ele comeu', 'She ate': 'Ela comeu', 'We ate': 'Nós comemos', 'They ate': 'Eles comeram', 'I drank': 'Eu bebi', 'You drank': 'Você bebeu', 'We drank': 'Nós bebemos', 'They drank': 'Eles beberam', 'I went': 'Eu fui', 'We went': 'Nós fomos', 'They went': 'Eles foram', 'He came': 'Ele veio', 'She came': 'Ela veio', 'I saw': 'Eu vi', 'We saw': 'Nós vimos', 'I bought': 'Eu comprei', 'We bought': 'Nós compramos', 'I played': 'Eu brinquei', 'We played': 'Nós brincamos', 'I read': 'Eu li', 'I wrote': 'Eu escrevi', 'I gave': 'Eu dei', 'I found': 'Eu encontrei', 'I lost': 'Eu perdi', 'I flew': 'Eu voei', 'drank': 'bebeu', 'ate': 'comeu', 'went': 'foi', 'came': 'veio', 'saw': 'viu', 'met': 'encontrou', 'bought': 'comprou', 'played': 'brincou', 'read': 'leu', 'wrote': 'escreveu', 'gave': 'deu', 'took': 'levou', 'found': 'encontrou', 'lost': 'perdeu', 'flew': 'voou', 'failed': 'falhou', 'won': 'venceu', 'Roof': 'Telhado', 'Wall': 'Parede', 'Floor': 'Chão', 'Key': 'Chave', 'Bedroom': 'Quarto', 'Living room': 'Sala de estar', 'Kitchen': 'Cozinha', 'Bathroom': 'Banheiro', 'Dining room': 'Sala de jantar', 'Bed': 'Cama', 'Chair': 'Cadeira', 'Table': 'Mesa', 'Sofa': 'Sofá',
        'business': 'negócios', 'company': 'empresa', 'employee': 'funcionário', 'employer': 'empregador', 'manager': 'gerente', 'client': 'cliente', 'customer': 'cliente', 'meeting': 'reunião', 'project': 'projeto', 'product': 'produto', 'service': 'serviço', 'office': 'escritório', 'price': 'preço', 'cost': 'custo', 'offer': 'oferta', 'agreement': 'acordo', 'contract': 'contrato', 'market': 'mercado', 'school': 'escola', 'hospital': 'hospital', 'bank': 'banco', 'street': 'rua', 'house': 'casa', 'room': 'quarto', 'door': 'porta', 'window': 'janela', 'apple': 'maçã', 'sky': 'céu', 'grass': 'grama', 'carrot': 'cenoura', 'happy': 'feliz', 'sad': 'triste', 'angry': 'zangado', 'scared': 'assustado', 'tomorrow': 'amanhã', 'yesterday': 'ontem', 'always': 'sempre', 'never': 'nunca', 'before': 'antes', 'after': 'depois', 'If': 'Se', 'When': 'Quando', 'Then': 'Então', 'Blue': 'Azul', 'Red': 'Vermelho', 'Green': 'Verde', 'Yellow': 'Amarelo', 'Black': 'Preto', 'White': 'Branco', 'Orange': 'Laranja', 'Brown': 'Marrom', 'Purple': 'Roxo', 'Name': 'Nome', 'Date': 'Data', 'Time': 'Hora', 'Friend': 'Amigo', 'Mother': 'Mãe', 'Father': 'Pai', 'Brother': 'Irmão', 'Sister': 'Irmã', 'Child': 'Criança', 'Children': 'Crianças', 'Home': 'Casa', 'Work': 'Trabalho', 'Breakfast': 'Café da manhã', 'Lunch': 'Almoço', 'Dinner': 'Jantar', 'Day': 'Dia', 'Night': 'Noite', 'Rain': 'Chuva', 'Sun': 'Sol', 'Cloud': 'Nuvem', 'Wind': 'Vento', 'Snow': 'Neve', 'Storm': 'Tempestade',
    }},
    'ja': {'folder': 'JA-TO-RW', 'map': {}},
    'ko': {'folder': 'KO-TO-RW', 'map': {}},
    'zh': {'folder': 'ZH-TO-RW', 'map': {}},
    'ar': {'folder': 'AR-TO-RW', 'map': {}},
}

# Shared high-frequency terms. Language-specific maps can be expanded without changing traversal.
COMMON = {
    'ja': {'Translate this lesson term': 'このレッスンの用語を翻訳してください', 'Learn the key vocabulary and practice exercises for this lesson.': 'このレッスンの重要な語彙を学び、練習問題に取り組みましょう。', 'Learn the main vocabulary of this lesson': 'このレッスンの主な語彙を学びましょう', 'Use the new words in simple sentences': '新しい単語を簡単な文で使いましょう', 'Practice listening, speaking, and translation': '聞く、話す、翻訳する練習をしましょう', 'Answer': '答え', 'Question': '質問', 'Hello': 'こんにちは', 'Goodbye': 'さようなら', 'Good morning': 'おはようございます', 'Thank you': 'ありがとうございます', 'Yes': 'はい', 'No': 'いいえ', 'Animals': '動物', 'Family': '家族', 'Food': '食べ物', 'Water': '水', 'House': '家', 'School': '学校', 'Market': '市場', 'Left': '左', 'Right': '右', 'Straight': 'まっすぐ', 'Tomorrow': '明日', 'Yesterday': '昨日', 'Happy': '嬉しい', 'Sad': '悲しい', 'Dog': '犬', 'Cat': '猫', 'Cow': '牛', 'Bird': '鳥', 'Fish': '魚', 'Lion': 'ライオン', 'Practice': '練習', 'Learn': '学ぶ', 'Choose': '選ぶ', 'Correct': '正しい', 'Word': '単語', 'Sentence': '文', 'Listen': '聞く', 'Read': '読む', 'Write': '書く', 'Speak': '話す'},
    'ko': {'Translate this lesson term': '이 수업 용어를 번역하세요', 'Learn the key vocabulary and practice exercises for this lesson.': '이 수업의 주요 어휘를 배우고 연습 문제를 풀어 보세요.', 'Learn the main vocabulary of this lesson': '이 수업의 주요 어휘를 배우세요', 'Use the new words in simple sentences': '새 단어를 간단한 문장에 사용하세요', 'Practice listening, speaking, and translation': '듣기, 말하기, 번역을 연습하세요', 'Answer': '답', 'Question': '질문', 'Hello': '안녕하세요', 'Goodbye': '안녕히 가세요', 'Good morning': '좋은 아침입니다', 'Thank you': '감사합니다', 'Yes': '네', 'No': '아니요', 'Animals': '동물', 'Family': '가족', 'Food': '음식', 'Water': '물', 'House': '집', 'School': '학교', 'Market': '시장', 'Left': '왼쪽', 'Right': '오른쪽', 'Straight': '직진', 'Tomorrow': '내일', 'Yesterday': '어제', 'Happy': '행복한', 'Sad': '슬픈', 'Dog': '개', 'Cat': '고양이', 'Cow': '소', 'Bird': '새', 'Fish': '물고기', 'Lion': '사자', 'Practice': '연습', 'Learn': '배우다', 'Choose': '선택하다', 'Correct': '맞는', 'Word': '단어', 'Sentence': '문장', 'Listen': '듣다', 'Read': '읽다', 'Write': '쓰다', 'Speak': '말하다'},
    'zh': {'Translate this lesson term': '翻译本课术语', 'Learn the key vocabulary and practice exercises for this lesson.': '学习本课重点词汇并练习相关题目。', 'Learn the main vocabulary of this lesson': '学习本课的主要词汇', 'Use the new words in simple sentences': '在简单句子中使用新词', 'Practice listening, speaking, and translation': '练习听力、口语和翻译', 'Answer': '答案', 'Question': '问题', 'Hello': '你好', 'Goodbye': '再见', 'Good morning': '早上好', 'Thank you': '谢谢', 'Yes': '是', 'No': '不', 'Animals': '动物', 'Family': '家庭', 'Food': '食物', 'Water': '水', 'House': '房子', 'School': '学校', 'Market': '市场', 'Left': '左', 'Right': '右', 'Straight': '直走', 'Tomorrow': '明天', 'Yesterday': '昨天', 'Happy': '开心', 'Sad': '难过', 'Dog': '狗', 'Cat': '猫', 'Cow': '牛', 'Bird': '鸟', 'Fish': '鱼', 'Lion': '狮子', 'Practice': '练习', 'Learn': '学习', 'Choose': '选择', 'Correct': '正确', 'Word': '单词', 'Sentence': '句子', 'Listen': '听', 'Read': '读', 'Write': '写', 'Speak': '说'},
    'ar': {'Translate this lesson term': 'ترجم مصطلح هذا الدرس', 'Learn the key vocabulary and practice exercises for this lesson.': 'تعلم المفردات الأساسية لهذا الدرس وتدرب على التمارين.', 'Learn the main vocabulary of this lesson': 'تعلم المفردات الرئيسية لهذا الدرس', 'Use the new words in simple sentences': 'استخدم الكلمات الجديدة في جمل بسيطة', 'Practice listening, speaking, and translation': 'تدرب على الاستماع والتحدث والترجمة', 'Answer': 'إجابة', 'Question': 'سؤال', 'Hello': 'مرحبا', 'Goodbye': 'وداعا', 'Good morning': 'صباح الخير', 'Thank you': 'شكرا', 'Yes': 'نعم', 'No': 'لا', 'Animals': 'الحيوانات', 'Family': 'العائلة', 'Food': 'الطعام', 'Water': 'الماء', 'House': 'البيت', 'School': 'المدرسة', 'Market': 'السوق', 'Left': 'يسار', 'Right': 'يمين', 'Straight': 'مباشرة', 'Tomorrow': 'غدا', 'Yesterday': 'أمس', 'Happy': 'سعيد', 'Sad': 'حزين', 'Dog': 'كلب', 'Cat': 'قطة', 'Cow': 'بقرة', 'Bird': 'طائر', 'Fish': 'سمكة', 'Lion': 'أسد', 'Practice': 'تدريب', 'Learn': 'تعلم', 'Choose': 'اختر', 'Correct': 'صحيح', 'Word': 'كلمة', 'Sentence': 'جملة', 'Listen': 'استمع', 'Read': 'اقرأ', 'Write': 'اكتب', 'Speak': 'تحدث'},
}
for code, values in COMMON.items():
    LANGUAGES[code]['map'].update(values)

SHARED_PROMPTS = {
    'pt': {'How do you say': 'Como se diz', 'in Kinyarwanda?': 'em Kinyarwanda?', 'What is': 'O que é', 'What does': 'O que significa', 'mean?': 'nesta frase?', 'Build': 'Forme', 'Arrange': 'Organize', 'Listen and type the animal name': 'Ouça e escreva o nome do animal', 'Listen and choose the correct animal': 'Ouça e escolha o animal correto', 'Choose the missing word': 'Escolha a palavra que falta', 'Select the correct option': 'Selecione a opção correta', 'Read the sentence and choose the correct answer': 'Leia a frase e escolha a resposta correta', 'Translate the following sentence into Kinyarwanda:': 'Traduza a frase seguinte para Kinyarwanda:', 'Is this statement true or false?': 'Esta afirmação é verdadeira ou falsa?', 'the': 'o', 'The': 'O', ' a ': ' um ', 'A ': 'Um ', ' is ': ' é ', ' near ': ' perto de ', ' around ': ' ao redor de ', ' the ': ' o ', ' and ': ' e ', ' in ': ' em ', ' for ': ' para ', ' by ': ' por ', ' fire': ' fogo'},
    'ja': {'How do you say': '何と言いますか', 'in Kinyarwanda?': 'キニャルワンダ語で？', 'What is': 'これは何ですか', 'What does': '何を意味しますか', 'mean?': 'という意味ですか', 'Build': '作りましょう', 'Arrange': '並べましょう', 'Listen and type the animal name': '聞いて動物の名前を入力してください', 'Listen and choose the correct animal': '聞いて正しい動物を選んでください', 'Choose the missing word': '抜けている単語を選んでください', 'Select the correct option': '正しい選択肢を選んでください', 'Read the sentence and choose the correct answer': '文を読んで正しい答えを選んでください', 'Translate the following sentence into Kinyarwanda:': '次の文をキニャルワンダ語に翻訳してください：', 'Is this statement true or false?': 'この文は正しいですか、間違いですか？', 'the': '', 'The': '', ' a ': ' ', 'A ': '', ' is ': ' は ', ' near ': ' の近くに ', ' around ': ' の周りに ', ' and ': ' と ', ' in ': ' で ', ' for ': ' のために ', ' fire': ' 火'},
    'ko': {'How do you say': '어떻게 말합니까', 'in Kinyarwanda?': '키냐르완다어로?', 'What is': '무엇입니까', 'What does': '무슨 뜻입니까', 'mean?': '라는 뜻입니까', 'Build': '만드세요', 'Arrange': '배열하세요', 'Listen and type the animal name': '듣고 동물 이름을 입력하세요', 'Listen and choose the correct animal': '듣고 올바른 동물을 선택하세요', 'Choose the missing word': '빠진 단어를 선택하세요', 'Select the correct option': '올바른 선택지를 선택하세요', 'Read the sentence and choose the correct answer': '문장을 읽고 올바른 답을 선택하세요', 'Translate the following sentence into Kinyarwanda:': '다음 문장을 키냐르완다어로 번역하세요:', 'Is this statement true or false?': '이 문장은 참입니까 거짓입니까?', 'the': '', 'The': '', ' a ': ' ', 'A ': '', ' is ': ' 은 ', ' near ': ' 근처에 ', ' around ': ' 주변에 ', ' and ': ' 그리고 ', ' in ': ' 에 ', ' for ': ' 을 위해 ', ' fire': ' 불'},
    'zh': {'How do you say': '怎么说', 'in Kinyarwanda?': '用基尼阿万达语？', 'What is': '什么是', 'What does': '是什么意思', 'mean?': '是什么意思', 'Build': '组成', 'Arrange': '排列', 'Listen and type the animal name': '听音并输入动物名称', 'Listen and choose the correct animal': '听音并选择正确的动物', 'Choose the missing word': '选择缺少的单词', 'Select the correct option': '选择正确选项', 'Read the sentence and choose the correct answer': '阅读句子并选择正确答案', 'Translate the following sentence into Kinyarwanda:': '将下面的句子翻译成基尼阿万达语：', 'Is this statement true or false?': '这句话是真是假？', 'the': '', 'The': '', ' a ': ' ', 'A ': '', ' is ': ' 是 ', ' near ': ' 附近 ', ' around ': ' 周围 ', ' and ': ' 和 ', ' in ': ' 在 ', ' for ': ' 为了 ', ' fire': ' 火'},
    'ar': {'How do you say': 'كيف تقول', 'in Kinyarwanda?': 'بالكينيارواندية؟', 'What is': 'ما هو', 'What does': 'ماذا يعني', 'mean?': 'ماذا يعني؟', 'Build': 'كوّن', 'Arrange': 'رتّب', 'Listen and type the animal name': 'استمع واكتب اسم الحيوان', 'Listen and choose the correct animal': 'استمع واختر الحيوان الصحيح', 'Choose the missing word': 'اختر الكلمة المفقودة', 'Select the correct option': 'اختر الخيار الصحيح', 'Read the sentence and choose the correct answer': 'اقرأ الجملة واختر الإجابة الصحيحة', 'Translate the following sentence into Kinyarwanda:': 'ترجم الجملة التالية إلى الكينيارواندية:', 'Is this statement true or false?': 'هل هذه العبارة صحيحة أم خاطئة؟', 'the': '', 'The': '', ' a ': ' ', 'A ': '', ' is ': ' هو ', ' near ': ' بالقرب من ', ' around ': ' حول ', ' and ': ' و', ' in ': ' في ', ' for ': ' من أجل ', ' fire': ' النار'},
}
for code, values in SHARED_PROMPTS.items():
    LANGUAGES[code]['map'].update(values)

TOPIC_NAMES = {
    'Greetings': {'pt': 'Saudações', 'ja': 'あいさつ', 'ko': '인사', 'zh': '问候', 'ar': 'التحيات'},
    'Numbers': {'pt': 'Números', 'ja': '数字', 'ko': '숫자', 'zh': '数字', 'ar': 'الأرقام'},
    'Family': {'pt': 'Família', 'ja': '家族', 'ko': '가족', 'zh': '家庭', 'ar': 'العائلة'},
    'Colors': {'pt': 'Cores', 'ja': '色', 'ko': '색깔', 'zh': '颜色', 'ar': 'الألوان'},
    'Animals': {'pt': 'Animais', 'ja': '動物', 'ko': '동물', 'zh': '动物', 'ar': 'الحيوانات'},
    'Food': {'pt': 'Comida', 'ja': '食べ物', 'ko': '음식', 'zh': '食物', 'ar': 'الطعام'},
    'Weather': {'pt': 'Clima', 'ja': '天気', 'ko': '날씨', 'zh': '天气', 'ar': 'الطقس'},
    'House': {'pt': 'Casa', 'ja': '家', 'ko': '집', 'zh': '房子', 'ar': 'البيت'},
    'Travel': {'pt': 'Viagens', 'ja': '旅行', 'ko': '여행', 'zh': '旅行', 'ar': 'السفر'},
    'Shopping': {'pt': 'Compras', 'ja': '買い物', 'ko': '쇼핑', 'zh': '购物', 'ar': 'التسوق'},
    'Restaurant': {'pt': 'Restaurante', 'ja': 'レストラン', 'ko': '식당', 'zh': '餐厅', 'ar': 'المطعم'},
    'Directions': {'pt': 'Direções', 'ja': '道案内', 'ko': '길 안내', 'zh': '方向', 'ar': 'الاتجاهات'},
    'Emotions': {'pt': 'Emoções', 'ja': '感情', 'ko': '감정', 'zh': '情感', 'ar': 'المشاعر'},
    'Business': {'pt': 'Negócios', 'ja': 'ビジネス', 'ko': '비즈니스', 'zh': '商务', 'ar': 'الأعمال'},
    'Culture': {'pt': 'Cultura', 'ja': '文化', 'ko': '문화', 'zh': '文化', 'ar': 'الثقافة'},
}
for topic, translations in TOPIC_NAMES.items():
    for code, translation in translations.items():
        LANGUAGES[code]['map'][topic] = translation

for code, translation in {
    'pt': {'Learn common greetings and expressions in Kinyarwanda': 'Aprenda saudações e expressões comuns em Kinyarwanda', 'Learn common food items in Kinyarwanda and how to talk about them': 'Aprenda alimentos comuns em Kinyarwanda e como falar sobre eles'},
    'ja': {'Learn common greetings and expressions in Kinyarwanda': 'キニャルワンダ語の一般的なあいさつと表現を学びます', 'Learn common food items in Kinyarwanda and how to talk about them': 'キニャルワンダ語の一般的な食べ物とその話し方を学びます'},
    'ko': {'Learn common greetings and expressions in Kinyarwanda': '키냐르완다어의 일반적인 인사와 표현을 배웁니다', 'Learn common food items in Kinyarwanda and how to talk about them': '키냐르완다어의 일반적인 음식과 말하는 방법을 배웁니다'},
    'zh': {'Learn common greetings and expressions in Kinyarwanda': '学习基尼阿万达语中常见的问候语和表达', 'Learn common food items in Kinyarwanda and how to talk about them': '学习基尼阿万达语中的常见食物及其表达方式'},
    'ar': {'Learn common greetings and expressions in Kinyarwanda': 'تعلم التحيات والتعبيرات الشائعة بلغة كينيارواندا', 'Learn common food items in Kinyarwanda and how to talk about them': 'تعلم أسماء الأطعمة الشائعة وكيفية التحدث عنها بلغة كينيارواندا'},
}.items():
    LANGUAGES[code]['map'].update(translation)

PROTECTED = {'id', 'type', 'xp_reward', 'difficulty', 'time_limit_seconds', 'topic', 'audio', 'audio_url', 'audio_file', 'tts_audio', 'image', 'image_url', 'link', 'answer', 'correct_answer', 'correct_sentence', 'correct_response', 'tts_text', 'prompt', 'statement', 'context_sentence', 'bot_message', 'text_with_blank', 'sentence', 'word', 'front', 'back', 'left', 'right'}

_TRANSLATION_CACHE = {}
_PATTERN_CACHE = {}


def translate_text(value: str, lang: str) -> str:
    if not isinstance(value, str) or value.startswith(('http://', 'https://')):
        return value
    cache_key = (lang, value)
    if cache_key in _TRANSLATION_CACHE:
        return _TRANSLATION_CACHE[cache_key]
    if lang not in _PATTERN_CACHE:
        replacements = LANGUAGES[lang]['map']
        lookup = {source.casefold(): target for source, target in replacements.items()}
        pattern = re.compile(r'(?<!\w)(?:' + '|'.join(re.escape(source) for source in sorted(replacements, key=len, reverse=True)) + r')(?!\w)', re.IGNORECASE)
        _PATTERN_CACHE[lang] = (pattern, lookup)
    pattern, lookup = _PATTERN_CACHE[lang]
    result = pattern.sub(lambda match: lookup.get(match.group(0).casefold(), match.group(0)), value)
    _TRANSLATION_CACHE[cache_key] = result
    return result


def should_protect(value, key):
    if key not in PROTECTED:
        return False
    # A few source placeholders are English UI text, not Kinyarwanda answers.
    return not isinstance(value, str) or value not in {'Answer', 'Question', 'Translate this lesson term', 'Correct answer', 'Choose an answer'}


def walk(value, key='', lang='pt'):
    if isinstance(value, dict):
        return {child_key: copy.deepcopy(child) if should_protect(child, child_key) else walk(child, child_key, lang) for child_key, child in value.items()}
    if isinstance(value, list):
        return [walk(item, key, lang) for item in value]
    if isinstance(value, str):
        return translate_text(value, lang)
    return value


def generate(lang: str):
    folder = Path('content') / LANGUAGES[lang]['folder']
    for source_file in sorted(SOURCE_ROOT.rglob('*.yaml')):
        output_file = folder / source_file.relative_to(SOURCE_ROOT)
        output_file.parent.mkdir(parents=True, exist_ok=True)
        source = yaml.safe_load(source_file.read_text(encoding='utf-8').replace("\\'", "'")) or {}
        output_file.write_text(yaml.safe_dump(walk(source, lang=lang), allow_unicode=True, sort_keys=False, width=120), encoding='utf-8')
        print(f'Wrote {output_file}')


def validate(lang: str):
    source_files = sorted(SOURCE_ROOT.rglob('*.yaml'))
    output_files = sorted((Path('content') / LANGUAGES[lang]['folder']).rglob('*.yaml'))
    assert len(source_files) == len(output_files), (lang, len(source_files), len(output_files))
    for source_file, output_file in zip(source_files, output_files):
        source = yaml.safe_load(source_file.read_text(encoding='utf-8').replace("\\'", "'")) or {}
        output = yaml.safe_load(output_file.read_text(encoding='utf-8')) or {}
        assert [e.get('id') for e in source.get('exercises', [])] == [e.get('id') for e in output.get('exercises', [])]
        assert [e.get('xp_reward') for e in source.get('exercises', [])] == [e.get('xp_reward') for e in output.get('exercises', [])]
    print(f'VALID: {lang.upper()} course generated with matching exercise IDs and XP values')


if __name__ == '__main__':
    for language in LANGUAGES:
        generate(language)
        validate(language)
