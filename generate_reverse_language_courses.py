from __future__ import annotations

import copy
import importlib
import re
from pathlib import Path

import yaml

SOURCE_ROOT = Path('content/RW-TO-EN')
LANGUAGE_CODES = 'pt ja ko zh ar ru hi tr nl sv ga el he pl no da fi cs id uk sw vi zu gd la'.split()
KINYARWANDA_LANGUAGE_NAMES = {
    'pt': 'mu Giporutigali', 'ja': 'mu Kiyapani', 'ko': 'mu Kiyakoreya', 'zh': 'mu Gishinwa', 'ar': 'mu Cyarabu', 'ru': 'mu Kirusiya', 'hi': 'mu Gihindi', 'tr': 'mu Gituruki', 'nl': 'mu Giholandi', 'sv': 'mu Gisuwede', 'ga': 'mu Gisirlande', 'el': 'mu Kigereki', 'he': 'mu Giheburayo', 'pl': 'mu Gipolonye', 'no': 'mu Munoruveji', 'da': 'mu Kidanishi', 'fi': 'mu Gifinilande', 'cs': 'mu Cekiya', 'id': 'mu Indoneziya', 'uk': 'mu Gikirayiniya', 'sw': 'mu Kiswahili', 'vi': 'mu Viyetinamu', 'zu': 'mu Zulu', 'gd': 'mu Gisilike', 'la': 'mu Kilatini',
}
MODULES = {
    'pt': 'generate_direct_language_courses', 'ja': 'generate_direct_language_courses', 'ko': 'generate_direct_language_courses', 'zh': 'generate_direct_language_courses', 'ar': 'generate_direct_language_courses',
    'ru': 'generate_ru_hi_tr_nl_sv_courses', 'hi': 'generate_ru_hi_tr_nl_sv_courses', 'tr': 'generate_ru_hi_tr_nl_sv_courses', 'nl': 'generate_ru_hi_tr_nl_sv_courses', 'sv': 'generate_ru_hi_tr_nl_sv_courses',
    'ga': 'generate_ga_el_he_pl_no_courses', 'el': 'generate_ga_el_he_pl_no_courses', 'he': 'generate_ga_el_he_pl_no_courses', 'pl': 'generate_ga_el_he_pl_no_courses', 'no': 'generate_ga_el_he_pl_no_courses',
    'da': 'generate_da_fi_cs_id_uk_sw_vi_zu_gd_la_courses', 'fi': 'generate_da_fi_cs_id_uk_sw_vi_zu_gd_la_courses', 'cs': 'generate_da_fi_cs_id_uk_sw_vi_zu_gd_la_courses', 'id': 'generate_da_fi_cs_id_uk_sw_vi_zu_gd_la_courses', 'uk': 'generate_da_fi_cs_id_uk_sw_vi_zu_gd_la_courses', 'sw': 'generate_da_fi_cs_id_uk_sw_vi_zu_gd_la_courses', 'vi': 'generate_da_fi_cs_id_uk_sw_vi_zu_gd_la_courses', 'zu': 'generate_da_fi_cs_id_uk_sw_vi_zu_gd_la_courses', 'gd': 'generate_da_fi_cs_id_uk_sw_vi_zu_gd_la_courses', 'la': 'generate_da_fi_cs_id_uk_sw_vi_zu_gd_la_courses',
}

PROTECTED_KEYS = {
    'id', 'type', 'xp_reward', 'difficulty', 'time_limit_seconds', 'topic',
    'audio', 'audio_url', 'audio_file', 'tts_audio', 'image', 'image_url',
    'link', 'prompt', 'statement', 'context_sentence', 'bot_message',
    'word',
}

LANGUAGE_NAMES = {
    'pt': 'em português', 'ja': '日本語で', 'ko': '한국어로', 'zh': '用中文', 'ar': 'بالعربية', 'ru': 'по-русски', 'hi': 'हिंदी में', 'tr': 'Türkçe olarak', 'nl': 'in het Nederlands', 'sv': 'på svenska', 'ga': 'i nGaeilge', 'el': 'στα ελληνικά', 'he': 'בעברית', 'pl': 'po polsku', 'no': 'på norsk', 'da': 'på dansk', 'fi': 'suomeksi', 'cs': 'česky', 'id': 'dalam bahasa Indonesia', 'uk': 'українською', 'sw': 'kwa Kiswahili', 'vi': 'bằng tiếng Việt', 'zu': 'ngesiZulu', 'gd': 'ann an Gàidhlig', 'la': 'Latine',
}

REVERSE_COMMON = {
    'pt': {'Wake up': 'Acordar', 'Sleep': 'Dormir', 'Soon': 'Em breve', 'ate': 'comeu', 'drank': 'bebeu', 'work': 'trabalho', 'study': 'estudar', 'walk': 'caminhar', 'run': 'correr', 'cook': 'cozinhar', 'clean': 'limpar'},
    'ja': {'Wake up': '起きる', 'Sleep': '寝る', 'Soon': 'すぐに', 'ate': '食べた', 'drank': '飲んだ', 'work': '仕事', 'study': '勉強する', 'walk': '歩く', 'run': '走る', 'cook': '料理する', 'clean': '掃除する'},
    'ko': {'Wake up': '일어나다', 'Sleep': '자다', 'Soon': '곧', 'ate': '먹었다', 'drank': '마셨다', 'work': '일', 'study': '공부하다', 'walk': '걷다', 'run': '달리다', 'cook': '요리하다', 'clean': '청소하다'},
    'zh': {'Wake up': '起床', 'Sleep': '睡觉', 'Soon': '很快', 'ate': '吃了', 'drank': '喝了', 'work': '工作', 'study': '学习', 'walk': '走路', 'run': '跑', 'cook': '做饭', 'clean': '打扫'},
    'ar': {'Wake up': 'استيقظ', 'Sleep': 'ينام', 'Soon': 'قريبا', 'ate': 'أكل', 'drank': 'شرب', 'work': 'عمل', 'study': 'يدرس', 'walk': 'يمشي', 'run': 'يركض', 'cook': 'يطبخ', 'clean': 'ينظف'},
    'ru': {'Wake up': 'Просыпаться', 'Sleep': 'Спать', 'Soon': 'Скоро', 'ate': 'ел', 'drank': 'пил', 'work': 'работа', 'study': 'учиться', 'walk': 'ходить', 'run': 'бегать', 'cook': 'готовить', 'clean': 'убирать'},
    'hi': {'Wake up': 'जागना', 'Sleep': 'सोना', 'Soon': 'जल्द', 'ate': 'खाया', 'drank': 'पिया', 'work': 'काम', 'study': 'पढ़ना', 'walk': 'चलना', 'run': 'दौड़ना', 'cook': 'खाना पकाना', 'clean': 'साफ करना'},
    'tr': {'Wake up': 'Uyanmak', 'Sleep': 'Uyumak', 'Soon': 'Yakında', 'ate': 'yedi', 'drank': 'içti', 'work': 'iş', 'study': 'çalışmak', 'walk': 'yürümek', 'run': 'koşmak', 'cook': 'pişirmek', 'clean': 'temizlemek'},
    'nl': {'Wake up': 'Wakker worden', 'Sleep': 'Slapen', 'Soon': 'Binnenkort', 'ate': 'at', 'drank': 'dronk', 'work': 'werk', 'study': 'studeren', 'walk': 'lopen', 'run': 'rennen', 'cook': 'koken', 'clean': 'schoonmaken'},
    'sv': {'Wake up': 'Vakna', 'Sleep': 'Sova', 'Soon': 'Snart', 'ate': 'åt', 'drank': 'drack', 'work': 'arbete', 'study': 'studera', 'walk': 'gå', 'run': 'springa', 'cook': 'laga mat', 'clean': 'städa'},
    'ga': {'Wake up': 'Dúiseacht', 'Sleep': 'Codladh', 'Soon': 'Go luath', 'ate': 'd’ith', 'drank': 'd’ól', 'work': 'obair', 'study': 'staidéar', 'walk': 'siúl', 'run': 'rith', 'cook': 'cócaireacht', 'clean': 'glanadh'},
    'el': {'Wake up': 'Ξυπνάω', 'Sleep': 'Κοιμάμαι', 'Soon': 'Σύντομα', 'ate': 'έφαγε', 'drank': 'ήπιε', 'work': 'εργασία', 'study': 'μελετώ', 'walk': 'περπατώ', 'run': 'τρέχω', 'cook': 'μαγειρεύω', 'clean': 'καθαρίζω'},
    'he': {'Wake up': 'להתעורר', 'Sleep': 'לישון', 'Soon': 'בקרוב', 'ate': 'אכל', 'drank': 'שתה', 'work': 'עבודה', 'study': 'ללמוד', 'walk': 'ללכת', 'run': 'לרוץ', 'cook': 'לבשל', 'clean': 'לנקות'},
    'pl': {'Wake up': 'Budzić się', 'Sleep': 'Spać', 'Soon': 'Wkrótce', 'ate': 'jadł', 'drank': 'pił', 'work': 'praca', 'study': 'uczyć się', 'walk': 'chodzić', 'run': 'biegać', 'cook': 'gotować', 'clean': 'sprzątać'},
    'no': {'Wake up': 'Våkne', 'Sleep': 'Sove', 'Soon': 'Snart', 'ate': 'spiste', 'drank': 'drakk', 'work': 'arbeid', 'study': 'studere', 'walk': 'gå', 'run': 'løpe', 'cook': 'lage mat', 'clean': 'rengjøre'},
    'da': {'Wake up': 'Vågne', 'Sleep': 'Sove', 'Soon': 'Snart', 'ate': 'spiste', 'drank': 'drak', 'work': 'arbejde', 'study': 'studere', 'walk': 'gå', 'run': 'løbe', 'cook': 'lave mad', 'clean': 'rengøre'},
    'fi': {'Wake up': 'Herätä', 'Sleep': 'Nukkua', 'Soon': 'Pian', 'ate': 'söi', 'drank': 'joi', 'work': 'työ', 'study': 'opiskella', 'walk': 'kävellä', 'run': 'juosta', 'cook': 'laittaa ruokaa', 'clean': 'siivota'},
    'cs': {'Wake up': 'Probudit se', 'Sleep': 'Spát', 'Soon': 'Brzy', 'ate': 'jedl', 'drank': 'pil', 'work': 'práce', 'study': 'studovat', 'walk': 'chodit', 'run': 'běhat', 'cook': 'vařit', 'clean': 'uklízet'},
    'id': {'Wake up': 'Bangun', 'Sleep': 'Tidur', 'Soon': 'Segera', 'ate': 'makan', 'drank': 'minum', 'work': 'bekerja', 'study': 'belajar', 'walk': 'berjalan', 'run': 'berlari', 'cook': 'memasak', 'clean': 'membersihkan'},
    'uk': {'Wake up': 'Прокидатися', 'Sleep': 'Спати', 'Soon': 'Скоро', 'ate': 'їв', 'drank': 'пив', 'work': 'робота', 'study': 'вчитися', 'walk': 'ходити', 'run': 'бігати', 'cook': 'готувати', 'clean': 'прибирати'},
    'sw': {'Wake up': 'Kuamka', 'Sleep': 'Kulala', 'Soon': 'Hivi karibuni', 'ate': 'alikula', 'drank': 'alikunywa', 'work': 'kazi', 'study': 'kusoma', 'walk': 'kutembea', 'run': 'kukimbia', 'cook': 'kupika', 'clean': 'kusafisha'},
    'vi': {'Wake up': 'Thức dậy', 'Sleep': 'Ngủ', 'Soon': 'Sớm', 'ate': 'đã ăn', 'drank': 'đã uống', 'work': 'làm việc', 'study': 'học', 'walk': 'đi bộ', 'run': 'chạy', 'cook': 'nấu ăn', 'clean': 'dọn dẹp'},
    'zu': {'Wake up': 'Vuka', 'Sleep': 'Lala', 'Soon': 'Maduze', 'ate': 'wadla', 'drank': 'waphuza', 'work': 'umsebenzi', 'study': 'funda', 'walk': 'hamba', 'run': 'gijima', 'cook': 'pheka', 'clean': 'hlanza'},
    'gd': {'Wake up': 'Dùisg', 'Sleep': 'Cadal', 'Soon': 'A dh’aithghearr', 'ate': 'dh’ith', 'drank': 'dh’òl', 'work': 'obair', 'study': 'sgrùdadh', 'walk': 'coiseachd', 'run': 'ruith', 'cook': 'còcaireachd', 'clean': 'glanadh'},
    'la': {'Wake up': 'Expergisci', 'Sleep': 'Dormire', 'Soon': 'Mox', 'ate': 'edit', 'drank': 'bibit', 'work': 'labor', 'study': 'studere', 'walk': 'ambulare', 'run': 'currere', 'cook': 'coquere', 'clean': 'mundare'},
}

ANIMALS = {
    'Cow': {'pt':'Vaca','ja':'牛','ko':'소','zh':'牛','ar':'بقرة','ru':'Корова','hi':'गाय','tr':'İnek','nl':'Koe','sv':'Ko','ga':'Bó','el':'Αγελάδα','he':'פרה','pl':'Krowa','no':'Ku','da':'Ko','fi':'Lehmä','cs':'Kráva','id':'Sapi','uk':'Корова','sw':'Ng’ombe','vi':'Con bò','zu':'Inkomo','gd':'Bò','la':'Vacca'},
    'Dog': {'pt':'Cachorro','ja':'犬','ko':'개','zh':'狗','ar':'كلب','ru':'Собака','hi':'कुत्ता','tr':'Köpek','nl':'Hond','sv':'Hund','ga':'Madra','el':'Σκύλος','he':'כלב','pl':'Pies','no':'Hund','da':'Hund','fi':'Koira','cs':'Pes','id':'Anjing','uk':'Собака','sw':'Mbwa','vi':'Con chó','zu':'Inja','gd':'Cù','la':'Canis'},
    'Cat': {'pt':'Gato','ja':'猫','ko':'고양이','zh':'猫','ar':'قطة','ru':'Кошка','hi':'बिल्ली','tr':'Kedi','nl':'Kat','sv':'Katt','ga':'Cat','el':'Γάτα','he':'חתול','pl':'Kot','no':'Katt','da':'Kat','fi':'Kissa','cs':'Kočka','id':'Kucing','uk':'Кішка','sw':'Paka','vi':'Con mèo','zu':'Ikati','gd':'Cat','la':'Feles'},
    'Pig': {'pt':'Porco','ja':'豚','ko':'돼지','zh':'猪','ar':'خنزير','ru':'Свинья','hi':'सूअर','tr':'Domuz','nl':'Varken','sv':'Gris','ga':'Muc','el':'Γουρούνι','he':'חזיר','pl':'Świnia','no':'Gris','da':'Gris','fi':'Sika','cs':'Prase','id':'Babi','uk':'Свиня','sw':'Nguruwe','vi':'Con lợn','zu':'Ingulube','gd':'Muc','la':'Porcus'},
    'Chicken': {'pt':'Galinha','ja':'鶏','ko':'닭','zh':'鸡','ar':'دجاجة','ru':'Курица','hi':'मुर्गी','tr':'Tavuk','nl':'Kip','sv':'Kyckling','ga':'Sicín','el':'Κοτόπουλο','he':'תרנגולת','pl':'Kurczak','no':'Kylling','da':'Kylling','fi':'Kana','cs':'Kuře','id':'Ayam','uk':'Курка','sw':'Kuku','vi':'Con gà','zu':'Inkukhu','gd':'Sicín','la':'Gallina'},
    'Lion': {'pt':'Leão','ja':'ライオン','ko':'사자','zh':'狮子','ar':'أسد','ru':'Лев','hi':'शेर','tr':'Aslan','nl':'Leeuw','sv':'Lejon','ga':'Leon','el':'Λιοντάρι','he':'אריה','pl':'Lew','no':'Løve','da':'Løve','fi':'Leijona','cs':'Lev','id':'Singa','uk':'Лев','sw':'Simba','vi':'Sư tử','zu':'Ibhubesi','gd':'Leòmhann','la':'Leo'},
}
for english, translations in ANIMALS.items():
    for code, translated in translations.items():
        REVERSE_COMMON.setdefault(code, {})[english] = translated

for code, translations in {
    'pt': {'Zero':'Zero','One':'Um','Two':'Dois','Three':'Três','Four':'Quatro','Five':'Cinco','Six':'Seis','Seven':'Sete','Eight':'Oito','Nine':'Nove','Ten':'Dez'},
    'ja': {'Zero':'ゼロ','One':'一','Two':'二','Three':'三','Four':'四','Five':'五','Six':'六','Seven':'七','Eight':'八','Nine':'九','Ten':'十'},
    'ko': {'Zero':'영','One':'하나','Two':'둘','Three':'셋','Four':'넷','Five':'다섯','Six':'여섯','Seven':'일곱','Eight':'여덟','Nine':'아홉','Ten':'열'},
    'zh': {'Zero':'零','One':'一','Two':'二','Three':'三','Four':'四','Five':'五','Six':'六','Seven':'七','Eight':'八','Nine':'九','Ten':'十'},
    'ar': {'Zero':'صفر','One':'واحد','Two':'اثنان','Three':'ثلاثة','Four':'أربعة','Five':'خمسة','Six':'ستة','Seven':'سبعة','Eight':'ثمانية','Nine':'تسعة','Ten':'عشرة'},
}.items():
    REVERSE_COMMON[code].update(translations)

for code, values in {
    'pt': {'Zero':'Zero','One':'Um','Two':'Dois','Three':'Três','Four':'Quatro','Five':'Cinco','Six':'Seis','Seven':'Sete','Eight':'Oito','Nine':'Nove','Ten':'Dez','Eleven':'Onze','Twelve':'Doze','Thirteen':'Treze','Fourteen':'Catorze','Fifteen':'Quinze','Sixteen':'Dezasseis','Seventeen':'Dezassete','Eighteen':'Dezoito','Nineteen':'Dezanove','Twenty':'Vinte','Thirty':'Trinta','Forty':'Quarenta','Fifty':'Cinquenta','Sixty':'Sessenta','Seventy':'Setenta','Eighty':'Oitenta','Ninety':'Noventa','One hundred':'Cem','One thousand':'Mil','One million':'Um milhão','Food':'Comida','Water':'Água','Wake up':'Acordar','Sleep':'Dormir','Eat':'Comer','Drink':'Beber','Work':'Trabalhar','Study':'Estudar','Walk':'Caminhar','Run':'Correr','Cook':'Cozinhar','Clean':'Limpar'},
    'ja': {'Zero':'ゼロ','One':'一','Two':'二','Three':'三','Four':'四','Five':'五','Six':'六','Seven':'七','Eight':'八','Nine':'九','Ten':'十','Eleven':'十一','Twelve':'十二','Thirteen':'十三','Fourteen':'十四','Fifteen':'十五','Sixteen':'十六','Seventeen':'十七','Eighteen':'十八','Nineteen':'十九','Twenty':'二十','Thirty':'三十','Forty':'四十','Fifty':'五十','Sixty':'六十','Seventy':'七十','Eighty':'八十','Ninety':'九十','One hundred':'百','One thousand':'千','One million':'百万','Food':'食べ物','Water':'水','Wake up':'起きる','Sleep':'寝る','Eat':'食べる','Drink':'飲む','Work':'働く','Study':'勉強する','Walk':'歩く','Run':'走る','Cook':'料理する','Clean':'掃除する'},
    'ko': {'Zero':'영','One':'하나','Two':'둘','Three':'셋','Four':'넷','Five':'다섯','Six':'여섯','Seven':'일곱','Eight':'여덟','Nine':'아홉','Ten':'열','Eleven':'열하나','Twelve':'열둘','Thirteen':'열셋','Fourteen':'열넷','Fifteen':'열다섯','Sixteen':'열여섯','Seventeen':'열일곱','Eighteen':'열여덟','Nineteen':'열아홉','Twenty':'스물','Thirty':'서른','Forty':'마흔','Fifty':'쉰','Sixty':'예순','Seventy':'일흔','Eighty':'여든','Ninety':'아흔','One hundred':'백','One thousand':'천','One million':'백만','Food':'음식','Water':'물','Wake up':'일어나다','Sleep':'자다','Eat':'먹다','Drink':'마시다','Work':'일하다','Study':'공부하다','Walk':'걷다','Run':'달리다','Cook':'요리하다','Clean':'청소하다'},
    'zh': {'Zero':'零','One':'一','Two':'二','Three':'三','Four':'四','Five':'五','Six':'六','Seven':'七','Eight':'八','Nine':'九','Ten':'十','Eleven':'十一','Twelve':'十二','Thirteen':'十三','Fourteen':'十四','Fifteen':'十五','Sixteen':'十六','Seventeen':'十七','Eighteen':'十八','Nineteen':'十九','Twenty':'二十','Thirty':'三十','Forty':'四十','Fifty':'五十','Sixty':'六十','Seventy':'七十','Eighty':'八十','Ninety':'九十','One hundred':'一百','One thousand':'一千','One million':'一百万','Food':'食物','Water':'水','Wake up':'起床','Sleep':'睡觉','Eat':'吃','Drink':'喝','Work':'工作','Study':'学习','Walk':'走路','Run':'跑步','Cook':'做饭','Clean':'打扫'},
}.items():
    REVERSE_COMMON[code].update(values)


def load_map(code: str):
    module = importlib.import_module(MODULES[code])
    config = getattr(module, 'LANGUAGES').get(code, {})
    if isinstance(config, tuple):
        mapping = dict(config[1])
    else:
        mapping = dict(config.get('map', config.get('replacements', {})))
    mapping.update(getattr(module, 'COMMON', {}).get(code, {}))
    mapping.update(getattr(module, 'TEMPORAL', {}).get(code, {}))
    mapping.update(REVERSE_COMMON.get(code, {}))
    mapping['mu Cyongereza'] = LANGUAGE_NAMES[code]
    mapping['Cyongereza'] = LANGUAGE_NAMES[code]
    mapping['på dansk'] = 'mu Kidanishi'
    return mapping


PATTERNS = {}
UNTRANSLATED_TTS_WORDS = {
    'the', 'a', 'an', 'is', 'are', 'was', 'were', 'above', 'below', 'near',
    'apple', 'apples', 'sky', 'red', 'blue', 'green', 'yellow', 'black', 'white',
    'orange', 'eats', 'drinks', 'sleeps', 'runs', 'flies', 'swims', 'plays',
    'grass', 'water', 'meat', 'milk', 'field', 'carrot', 'birds', 'chickens',
    'cows', 'dogs', 'cats', 'rabbits', 'fish', 'frog', 'bird', 'chicken',
}

def translate_text(value: str, code: str) -> str:
    if not isinstance(value, str) or value.startswith(('http://', 'https://')):
        return value
    if code not in PATTERNS:
        mapping = load_map(code)
        lookup = {source.casefold(): target for source, target in mapping.items()}
        pattern = re.compile(r'(?<!\w)(?:' + '|'.join(re.escape(source) for source in sorted(mapping, key=len, reverse=True)) + r')(?!\w)', re.IGNORECASE) if mapping else None
        PATTERNS[code] = (pattern, lookup)
    pattern, lookup = PATTERNS[code]
    if not pattern:
        return value
    return pattern.sub(lambda match: lookup.get(match.group(0).casefold(), match.group(0)), value)


def walk(value, key='', code='pt'):
    if isinstance(value, dict):
        result = {}
        for child_key, child in value.items():
            if child_key == 'question' and isinstance(child, str):
                result[child_key] = translate_text(child, code)
            elif child_key in PROTECTED_KEYS:
                result[child_key] = copy.deepcopy(child)
            else:
                result[child_key] = walk(child, child_key, code)
        return result
    if isinstance(value, list):
        return [walk(item, key, code) for item in value]
    if isinstance(value, str):
        return translate_text(value, code)
    return value


def collect_translation_pairs(source, translated, pairs):
    if isinstance(source, dict) and isinstance(translated, dict):
        for key in source.keys() & translated.keys():
            collect_translation_pairs(source[key], translated[key], pairs)
    elif isinstance(source, list) and isinstance(translated, list):
        for source_item, translated_item in zip(source, translated):
            collect_translation_pairs(source_item, translated_item, pairs)
    elif isinstance(source, str) and isinstance(translated, str) and source != translated:
        pairs[source] = translated


def translate_tts_fields(value, pairs):
    if isinstance(value, dict):
        return {
            key: translate_tts_fields(child, pairs) if key != 'tts_text' else replace_tts_text(child, pairs)
            for key, child in value.items()
        }
    if isinstance(value, list):
        return [translate_tts_fields(item, pairs) for item in value]
    return value


def replace_tts_text(value, pairs):
    if not isinstance(value, str):
        return value
    result = value
    for source, translated in sorted(pairs.items(), key=lambda item: len(item[0]), reverse=True):
        result = re.sub(r'(?<!\w)' + re.escape(source) + r'(?!\w)', translated, result, flags=re.IGNORECASE)
    for word in sorted(UNTRANSLATED_TTS_WORDS, key=len, reverse=True):
        result = re.sub(r'(?<!\w)' + re.escape(word) + r'(?!\w)', '', result, flags=re.IGNORECASE)
    return re.sub(r'\s+', ' ', result).strip()


def load_source(path: Path):
    return yaml.safe_load(path.read_text(encoding='utf-8').replace("\\'", "'")) or {}


def generate(code: str):
    folder = Path('content') / f'RW-TO-{code.upper()}'
    for source_file in sorted(SOURCE_ROOT.rglob('*.yaml')):
        output_file = folder / source_file.relative_to(SOURCE_ROOT)
        output_file.parent.mkdir(parents=True, exist_ok=True)
        source = load_source(source_file)
        translated = walk(source, code=code)
        translation_pairs = {}
        collect_translation_pairs(source, translated, translation_pairs)
        translated = translate_tts_fields(translated, translation_pairs)
        output_file.write_text(yaml.safe_dump(translated, allow_unicode=True, sort_keys=False, width=120), encoding='utf-8')
        print(f'Wrote {output_file}')


def validate(code: str):
    source_files = sorted(SOURCE_ROOT.rglob('*.yaml'))
    output_files = sorted((Path('content') / f'RW-TO-{code.upper()}').rglob('*.yaml'))
    assert len(source_files) == len(output_files), (code, len(source_files), len(output_files))
    for source_file, output_file in zip(source_files, output_files):
        source = load_source(source_file)
        output = yaml.safe_load(output_file.read_text(encoding='utf-8')) or {}
        assert [item.get('id') for item in source.get('exercises', [])] == [item.get('id') for item in output.get('exercises', [])]
        assert [item.get('xp_reward') for item in source.get('exercises', [])] == [item.get('xp_reward') for item in output.get('exercises', [])]
    print(f'VALID: RW-{code.upper()} course generated with matching exercise IDs and XP values')


if __name__ == '__main__':
    for code in LANGUAGE_CODES:
        generate(code)
        validate(code)
