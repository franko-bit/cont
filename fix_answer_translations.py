from __future__ import annotations

import importlib
import re
import sys
from pathlib import Path
import yaml

LANGUAGE_MODULES = {
    'de': 'generate_additional_language_course', 'it': 'generate_additional_language_course',
    'pt': 'generate_direct_language_courses', 'ja': 'generate_direct_language_courses', 'ko': 'generate_direct_language_courses',
    'zh': 'generate_direct_language_courses', 'ar': 'generate_direct_language_courses',
    'ru': 'generate_ru_hi_tr_nl_sv_courses', 'hi': 'generate_ru_hi_tr_nl_sv_courses', 'tr': 'generate_ru_hi_tr_nl_sv_courses',
    'nl': 'generate_ru_hi_tr_nl_sv_courses', 'sv': 'generate_ru_hi_tr_nl_sv_courses',
    'ga': 'generate_ga_el_he_pl_no_courses', 'el': 'generate_ga_el_he_pl_no_courses', 'he': 'generate_ga_el_he_pl_no_courses',
    'pl': 'generate_ga_el_he_pl_no_courses', 'no': 'generate_ga_el_he_pl_no_courses',
    'da': 'generate_da_fi_cs_id_uk_sw_vi_zu_gd_la_courses', 'fi': 'generate_da_fi_cs_id_uk_sw_vi_zu_gd_la_courses',
    'cs': 'generate_da_fi_cs_id_uk_sw_vi_zu_gd_la_courses', 'id': 'generate_da_fi_cs_id_uk_sw_vi_zu_gd_la_courses',
    'uk': 'generate_da_fi_cs_id_uk_sw_vi_zu_gd_la_courses', 'sw': 'generate_da_fi_cs_id_uk_sw_vi_zu_gd_la_courses',
    'vi': 'generate_da_fi_cs_id_uk_sw_vi_zu_gd_la_courses', 'zu': 'generate_da_fi_cs_id_uk_sw_vi_zu_gd_la_courses',
    'gd': 'generate_da_fi_cs_id_uk_sw_vi_zu_gd_la_courses', 'la': 'generate_da_fi_cs_id_uk_sw_vi_zu_gd_la_courses',
}

COMMON = {
    'da': {'Rabbit': 'Kanin', 'Donkey': 'Æsel', 'Pigeon': 'Due', 'Elephant': 'Elefant', 'Mountain gorilla': 'Bjerggorilla', 'Leopard': 'Leopard', 'Buffalo': 'Bøffel', 'Zebra': 'Zebra', 'Hippopotamus': 'Flodhest', 'Giraffe': 'Giraf', 'Hyena': 'Hyæne', 'Monkey': 'Abe', 'Grey crowned crane': 'Grå krontrane', 'Snake': 'Slange', 'Frog': 'Frø', 'Bee': 'Bi', 'eats': 'spiser', 'drinks': 'drikker', 'sleeps': 'sover', 'runs': 'løber', 'flies': 'flyver', 'swims': 'svømmer', 'plays': 'leger', 'grass': 'græs', 'meat': 'kød', 'milk': 'mælk', 'above': 'over', 'field': 'mark', 'The': 'Den', 'A': 'En'},
    'fi': {'Rabbit': 'Kani', 'Donkey': 'Aasi', 'Pigeon': 'Kyyhkynen', 'Elephant': 'Elefantti', 'Mountain gorilla': 'Vuorigorilla', 'Leopard': 'Leopardi', 'Buffalo': 'Buffalo', 'Zebra': 'Seeprа', 'Hippopotamus': 'Virtahepo', 'Giraffe': 'Kirahvi', 'Hyena': 'Hyeena', 'Monkey': 'Apina', 'Grey crowned crane': 'Harakkakurkikruunu', 'Snake': 'Käärme', 'Frog': 'Sammakko', 'Bee': 'Mehiläinen', 'eats': 'syö', 'drinks': 'juo', 'sleeps': 'nukkuu', 'runs': 'juoksee', 'flies': 'lentää', 'swims': 'ui', 'plays': 'leikkii', 'grass': 'ruoho', 'meat': 'liha', 'milk': 'maito', 'above': 'yläpuolella', 'field': 'pelto', 'The': 'Se', 'A': 'Eräs'},
    'id': {'Rabbit': 'Kelinci', 'Donkey': 'Keledai', 'Pigeon': 'Merpati', 'Elephant': 'Gajah', 'Mountain gorilla': 'Gorila gunung', 'Leopard': 'Macan tutul', 'Buffalo': 'Kerbau', 'Zebra': 'Zebra', 'Hippopotamus': 'Kuda nil', 'Giraffe': 'Jerapah', 'Hyena': 'Hiena', 'Monkey': 'Monyet', 'Grey crowned crane': 'Bangau mahkota abu-abu', 'Snake': 'Ular', 'Frog': 'Katak', 'Bee': 'Lebah', 'eats': 'makan', 'drinks': 'minum', 'sleeps': 'tidur', 'runs': 'berlari', 'flies': 'terbang', 'swims': 'berenang', 'plays': 'bermain', 'grass': 'rumput', 'meat': 'daging', 'milk': 'susu', 'above': 'di atas', 'field': 'ladang', 'The': 'Itu', 'A': 'Seekor'},
    'uk': {'Rabbit': 'Кролик', 'Donkey': 'Віслюк', 'Pigeon': 'Голуб', 'Elephant': 'Слон', 'Mountain gorilla': 'Гірська горила', 'Leopard': 'Леопард', 'Buffalo': 'Буйвол', 'Zebra': 'Зебра', 'Hippopotamus': 'Бегемот', 'Giraffe': 'Жирафа', 'Hyena': 'Гієна', 'Monkey': 'Мавпа', 'Grey crowned crane': 'Сірий журавель', 'Snake': 'Змія', 'Frog': 'Жаба', 'Bee': 'Бджола', 'eats': 'їсть', 'drinks': 'п’є', 'sleeps': 'спить', 'runs': 'бігає', 'flies': 'літає', 'swims': 'плаває', 'plays': 'грається', 'grass': 'трава', 'meat': 'м’ясо', 'milk': 'молоко', 'above': 'над', 'field': 'поле', 'The': 'Ця', 'A': 'Одна'},
    'sw': {'Rabbit': 'Sungura', 'Donkey': 'Punda', 'Pigeon': 'Njiwa', 'Elephant': 'Tembo', 'Mountain gorilla': 'Gorila wa milimani', 'Leopard': 'Chui', 'Buffalo': 'Nyati', 'Zebra': 'Pundamilia', 'Hippopotamus': 'Kiboko', 'Giraffe': 'Twiga', 'Hyena': 'Fisi', 'Monkey': 'Tumbili', 'Grey crowned crane': 'Korongo mwenye taji la kijivu', 'Snake': 'Nyoka', 'Frog': 'Chura', 'Bee': 'Nyuki', 'eats': 'anakula', 'drinks': 'anakunywa', 'sleeps': 'analala', 'runs': 'anakimbia', 'flies': 'anaruka', 'swims': 'anaogelea', 'plays': 'anacheza', 'grass': 'nyasi', 'meat': 'nyama', 'milk': 'maziwa', 'above': 'juu ya', 'field': 'shamba', 'The': 'Yule', 'A': 'Mmoja'},
    'vi': {'Rabbit': 'Con thỏ', 'Donkey': 'Con lừa', 'Pigeon': 'Chim bồ câu', 'Elephant': 'Con voi', 'Mountain gorilla': 'Khỉ đột núi', 'Leopard': 'Báo', 'Buffalo': 'Trâu', 'Zebra': 'Ngựa vằn', 'Hippopotamus': 'Hà mã', 'Giraffe': 'Hươu cao cổ', 'Hyena': 'Linh cẩu', 'Monkey': 'Khỉ', 'Grey crowned crane': 'Sếu đầu xám', 'Snake': 'Rắn', 'Frog': 'Ếch', 'Bee': 'Ong', 'eats': 'ăn', 'drinks': 'uống', 'sleeps': 'ngủ', 'runs': 'chạy', 'flies': 'bay', 'swims': 'bơi', 'plays': 'chơi', 'grass': 'cỏ', 'meat': 'thịt', 'milk': 'sữa', 'above': 'ở trên', 'field': 'cánh đồng', 'The': 'Con', 'A': 'Một'},
    'zu': {'Rabbit': 'Unogwaja', 'Donkey': 'Imbongolo', 'Pigeon': 'Ijuba', 'Elephant': 'Indlovu', 'Mountain gorilla': 'Igorila yasentabeni', 'Leopard': 'Ingwe', 'Buffalo': 'Inyathi', 'Zebra': 'Idube', 'Hippopotamus': 'Imvubu', 'Giraffe': 'Indlulamithi', 'Hyena': 'Impisi', 'Monkey': 'Inkawu', 'Grey crowned crane': 'Indwe enomqhele ompunga', 'Snake': 'Inyoka', 'Frog': 'Ixoxo', 'Bee': 'Inyosi', 'eats': 'iyadla', 'drinks': 'iyaphuza', 'sleeps': 'iyalala', 'runs': 'iyagijima', 'flies': 'iyandiza', 'swims': 'iyabhukuda', 'plays': 'iyadlala', 'grass': 'utshani', 'meat': 'inyama', 'milk': 'ubisi', 'above': 'ngenhla', 'field': 'insimu', 'The': 'Le', 'A': 'I'},
    'gd': {'Rabbit': 'Coineanach', 'Donkey': 'Asal', 'Pigeon': 'Calman', 'Elephant': 'Ailbhean', 'Mountain gorilla': 'Gorilla beinne', 'Leopard': 'Leopard', 'Buffalo': 'Buffalo', 'Zebra': 'Seabra', 'Hippopotamus': 'Each-uisge', 'Giraffe': 'Sioraf', 'Hyena': 'Hyena', 'Monkey': 'Moncaidh', 'Grey crowned crane': 'Crann-garbh liath', 'Snake': 'Nathair', 'Frog': 'Losgann', 'Bee': 'Seillean', 'eats': 'ag ithe', 'drinks': 'ag òl', 'sleeps': 'a’ cadal', 'runs': 'a’ ruith', 'flies': 'ag itealaich', 'swims': 'a’ snàmh', 'plays': 'a’ cluich', 'grass': 'feur', 'meat': 'feòil', 'milk': 'bainne', 'above': 'os cionn', 'field': 'achadh', 'The': 'An', 'A': 'A'},
    'la': {'Rabbit': ' cuniculus', 'Donkey': 'Asinus', 'Pigeon': 'Columba', 'Elephant': 'Elephas', 'Mountain gorilla': 'Gorilla montana', 'Leopard': 'Pardus', 'Buffalo': 'Bubalus', 'Zebra': 'Zebra', 'Hippopotamus': 'Hippopotamus', 'Giraffe': 'Camelopardalis', 'Hyena': 'Hyaena', 'Monkey': 'Simia', 'Grey crowned crane': 'Grus coronata cinerea', 'Snake': 'Serpens', 'Frog': 'Rana', 'Bee': 'Apis', 'eats': 'edit', 'drinks': 'bibit', 'sleeps': 'dormit', 'runs': 'currit', 'flies': 'volat', 'swims': 'natat', 'plays': 'ludit', 'grass': 'herba', 'meat': 'caro', 'milk': 'lac', 'above': 'supra', 'field': 'ager', 'The': 'Il', 'A': 'Un'},
    'pt': {'Cow': 'Vaca', 'Lion': 'Leão', 'Dog': 'Cachorro', 'Cat': 'Gato', 'eats': 'come', 'drinks': 'bebe', 'sleeps': 'dorme', 'runs': 'corre', 'flies': 'voa', 'swims': 'nada', 'plays': 'brinca', 'grass': 'grama', 'water': 'água', 'meat': 'carne', 'milk': 'leite', 'Chicken': 'Galinha', 'Goat': 'Cabra', 'Sheep': 'Ovelha', 'Fish': 'Peixe', 'I ate': 'Eu comi', 'You ate': 'Você comeu', 'He ate': 'Ele comeu', 'She ate': 'Ela comeu', 'We ate': 'Nós comemos', 'They ate': 'Eles comeram', 'I drank': 'Eu bebi', 'You drank': 'Você bebeu', 'We drank': 'Nós bebemos', 'They drank': 'Eles beberam', 'I went': 'Eu fui', 'We went': 'Nós fomos', 'They went': 'Eles foram', 'He came': 'Ele veio', 'She came': 'Ela veio', 'I saw': 'Eu vi', 'We saw': 'Nós vimos', 'I bought': 'Eu comprei', 'We bought': 'Nós compramos', 'I played': 'Eu brinquei', 'We played': 'Nós brincamos', 'I read': 'Eu li', 'I wrote': 'Eu escrevi', 'I gave': 'Eu dei', 'I found': 'Eu encontrei', 'I lost': 'Eu perdi', 'I flew': 'Eu voei', 'drank': 'bebeu', 'ate': 'comeu', 'went': 'foi', 'came': 'veio', 'saw': 'viu', 'met': 'encontrou', 'bought': 'comprou', 'played': 'brincou', 'read': 'leu', 'wrote': 'escreveu', 'gave': 'deu', 'took': 'levou', 'found': 'encontrou', 'lost': 'perdeu', 'flew': 'voou', 'failed': 'falhou', 'won': 'venceu', 'Roof': 'Telhado', 'Wall': 'Parede', 'Floor': 'Chão', 'Key': 'Chave', 'Bedroom': 'Quarto', 'Living room': 'Sala de estar', 'Kitchen': 'Cozinha', 'Bathroom': 'Banheiro', 'Dining room': 'Sala de jantar', 'Bed': 'Cama', 'Chair': 'Cadeira', 'Table': 'Mesa', 'Sofa': 'Sofá'},
    'ja': {'Cow': '牛', 'Lion': 'ライオン', 'Dog': '犬', 'Cat': '猫', 'eats': '食べる', 'drinks': '飲む', 'sleeps': '寝る', 'runs': '走る', 'flies': '飛ぶ', 'swims': '泳ぐ', 'plays': '遊ぶ', 'grass': '草', 'water': '水', 'meat': '肉', 'milk': '牛乳', 'Chicken': '鶏', 'Goat': 'ヤギ', 'Sheep': '羊', 'Fish': '魚'},
    'ko': {'Cow': '소', 'Lion': '사자', 'Dog': '개', 'Cat': '고양이', 'eats': '먹다', 'drinks': '마시다', 'sleeps': '자다', 'runs': '달리다', 'flies': '날다', 'swims': '수영하다', 'plays': '놀다', 'grass': '풀', 'water': '물', 'meat': '고기', 'milk': '우유', 'Chicken': '닭', 'Goat': '염소', 'Sheep': '양', 'Fish': '물고기'},
    'zh': {'Cow': '牛', 'Lion': '狮子', 'Dog': '狗', 'Cat': '猫', 'eats': '吃', 'drinks': '喝', 'sleeps': '睡觉', 'runs': '跑', 'flies': '飞', 'swims': '游泳', 'plays': '玩', 'grass': '草', 'water': '水', 'meat': '肉', 'milk': '牛奶', 'Chicken': '鸡', 'Goat': '山羊', 'Sheep': '羊', 'Fish': '鱼'},
    'ar': {'Cow': 'بقرة', 'Lion': 'أسد', 'Dog': 'كلب', 'Cat': 'قطة', 'eats': 'يأكل', 'drinks': 'يشرب', 'sleeps': 'ينام', 'runs': 'يجري', 'flies': 'يطير', 'swims': 'يسبح', 'plays': 'يلعب', 'grass': 'عشب', 'water': 'ماء', 'meat': 'لحم', 'milk': 'حليب', 'Chicken': 'دجاجة', 'Goat': 'ماعز', 'Sheep': 'خروف', 'Fish': 'سمكة'},
}

EXTRA_ANIMALS = {
    'cs': {'Rabbit':'Králík','Donkey':'Osel','Pigeon':'Holub','Elephant':'Slon','Mountain gorilla':'Horská gorila','Hippopotamus':'Hroch','Giraffe':'Žirafa','Hyena':'Hyena','Monkey':'Opice','Snake':'Had','Frog':'Žába','Bee':'Včela','eats':'jí','drinks':'pije','sleeps':'spí','runs':'běží','flies':'létá','swims':'plave','plays':'hraje','grass':'tráva','meat':'maso','above':'nad','field':'pole','The':'Ten','A':'Jeden'},
    'el': {'Donkey':'Γάιδαρος','Pigeon':'Περιστέρι','Mountain gorilla':'Γορίλα του βουνού','Hippopotamus':'Ιπποπόταμος','Giraffe':'Καμηλοπάρδαλη','Hyena':'Ύαινα','Monkey':'Πίθηκος','Snake':'Φίδι','Frog':'Βάτραχος','eats':'τρώει','drinks':'πίνει','sleeps':'κοιμάται','runs':'τρέχει','flies':'πετάει','swims':'κολυμπάει','plays':'παίζει','grass':'γρασίδι','meat':'κρέας','above':'πάνω από','field':'χωράφι','The':'Το','A':'Ένα'},
    'ga': {'Donkey':'Asal','Pigeon':'Colm','Mountain gorilla':'Gorilla sléibhe','Hippopotamus':'Dobhareach','Giraffe':'Sioráf','Hyena':'Héine','Monkey':'Moncaí','Snake':'Nathair','Frog':'Frog','eats':'itheann','drinks':'ólann','sleeps':'codlaíonn','runs':'ritheann','flies':'eitlíonn','swims':'snámhann','plays':'imríonn','grass':'féar','meat':'feoil','above':'os cionn','field':'páirc','The':'An','A':'A'},
    'he': {'Donkey':'חמור','Pigeon':'יונה','Mountain gorilla':'גורילת הרים','Hippopotamus':'היפופוטם','Giraffe':'ג׳ירפה','Hyena':'צבוע','Monkey':'קוף','Snake':'נחש','Frog':'צפרדע','eats':'אוכל','drinks':'שותה','sleeps':'ישן','runs':'רץ','flies':'עף','swims':'שוחה','plays':'משחק','grass':'עשב','meat':'בשר','above':'מעל','field':'שדה','The':'ה','A':'אחד'},
    'nl': {'Donkey':'Ezel','Pigeon':'Duif','Mountain gorilla':'Berggorilla','Hippopotamus':'Nijlpaard','Giraffe':'Giraf','Hyena':'Hyena','Monkey':'Aap','Snake':'Slang','Frog':'Kikker','eats':'eet','drinks':'drinkt','sleeps':'slaapt','runs':'rent','flies':'vliegt','swims':'zwemt','plays':'speelt','grass':'gras','meat':'vlees','above':'boven','field':'veld','The':'De','A':'Een'},
    'no': {'Donkey':'Esel','Pigeon':'Due','Mountain gorilla':'Fjellgorilla','Hippopotamus':'Flodhest','Giraffe':'Sjiraff','Hyena':'Hyene','Monkey':'Ape','Snake':'Slange','Frog':'Frosk','eats':'spiser','drinks':'drikker','sleeps':'sover','runs':'løper','flies':'flyr','swims':'svømmer','plays':'leker','grass':'gress','meat':'kjøtt','above':'over','field':'mark','The':'Den','A':'En'},
    'pl': {'Donkey':'Osioł','Pigeon':'Gołąb','Mountain gorilla':'Goryl górski','Hippopotamus':'Hipopotam','Giraffe':'Żyrafa','Hyena':'Hiena','Monkey':'Małpa','Snake':'Wąż','Frog':'Żaba','eats':'je','drinks':'pije','sleeps':'śpi','runs':'biega','flies':'lata','swims':'pływa','plays':'bawi się','grass':'trawa','meat':'mięso','above':'nad','field':'pole','The':'Ten','A':'Jeden'},
    'ru': {'Donkey':'Осел','Pigeon':'Голубь','Mountain gorilla':'Горная горилла','Hippopotamus':'Бегемот','Giraffe':'Жираф','Hyena':'Гиена','Monkey':'Обезьяна','Snake':'Змея','Frog':'Лягушка','eats':'ест','drinks':'пьёт','sleeps':'спит','runs':'бегает','flies':'летает','swims':'плавает','plays':'играет','grass':'трава','meat':'мясо','above':'над','field':'поле','The':'Этот','A':'Один'},
    'sv': {'Donkey':'Åsna','Pigeon':'Duva','Mountain gorilla':'Bergsgorilla','Hippopotamus':'Flodhäst','Giraffe':'Giraff','Hyena':'Hyena','Monkey':'Apa','Snake':'Orm','Frog':'Groda','eats':'äter','drinks':'dricker','sleeps':'sover','runs':'springer','flies':'flyger','swims':'simmar','plays':'leker','grass':'gräs','meat':'kött','above':'ovanför','field':'fält','The':'Den','A':'En'},
    'tr': {'Donkey':'Eşek','Pigeon':'Güvercin','Mountain gorilla':'Dağ gorili','Hippopotamus':'Su aygırı','Giraffe':'Zürafa','Hyena':'Sırtlan','Monkey':'Maymun','Snake':'Yılan','Frog':'Kurbağa','eats':'yer','drinks':'içer','sleeps':'uyur','runs':'koşar','flies':'uçar','swims':'yüzer','plays':'oynar','grass':'çimen','meat':'et','above':'üstünde','field':'tarla','The':'Bu','A':'Bir'},
    'ar': {'Rabbit':'أرنب','Donkey':'حمار','Pigeon':'حمامة','Elephant':'فيل','Mountain gorilla':'غوريلا جبلية','Hippopotamus':'فرس النهر','Giraffe':'زرافة','Hyena':'ضبع','Monkey':'قرد','Snake':'ثعبان','Frog':'ضفدع','Bee':'نحلة','eats':'يأكل','drinks':'يشرب','sleeps':'ينام','runs':'يركض','flies':'يطير','swims':'يسبح','plays':'يلعب','grass':'عشب','meat':'لحم','above':'فوق','field':'حقل','The':'ال','A':'واحد'},
    'ja': {'Rabbit':'ウサギ','Donkey':'ロバ','Pigeon':'ハト','Elephant':'ゾウ','Mountain gorilla':'マウンテンゴリラ','Hippopotamus':'カバ','Giraffe':'キリン','Hyena':'ハイエナ','Monkey':'サル','Snake':'ヘビ','Frog':'カエル','Bee':'ハチ','eats':'食べる','drinks':'飲む','sleeps':'寝る','runs':'走る','flies':'飛ぶ','swims':'泳ぐ','plays':'遊ぶ','grass':'草','meat':'肉','above':'上に','field':'野原','The':'その','A':'一匹の'},
    'ko': {'Rabbit':'토끼','Donkey':'당나귀','Pigeon':'비둘기','Elephant':'코끼리','Mountain gorilla':'산고릴라','Hippopotamus':'하마','Giraffe':'기린','Hyena':'하이에나','Monkey':'원숭이','Snake':'뱀','Frog':'개구리','Bee':'벌','eats':'먹다','drinks':'마시다','sleeps':'자다','runs':'달리다','flies':'날다','swims':'수영하다','plays':'놀다','grass':'풀','meat':'고기','above':'위에','field':'들판','The':'그','A':'한'},
    'zh': {'Rabbit':'兔子','Donkey':'驴','Pigeon':'鸽子','Elephant':'大象','Mountain gorilla':'山地大猩猩','Hippopotamus':'河马','Giraffe':'长颈鹿','Hyena':'鬣狗','Monkey':'猴子','Snake':'蛇','Frog':'青蛙','Bee':'蜜蜂','eats':'吃','drinks':'喝','sleeps':'睡觉','runs':'跑','flies':'飞','swims':'游泳','plays':'玩','grass':'草','meat':'肉','above':'上方','field':'田野','The':'这只','A':'一只'},
}
for code, values in EXTRA_ANIMALS.items():
    COMMON.setdefault(code, {}).update(values)
COMMON['cs']['Hyena'] = 'Hyena skvrnitá'
COMMON['ga']['Frog'] = 'Losgann'
COMMON['gd']['Hyena'] = 'Hiena'
COMMON['la']['Hippopotamus'] = 'Hippopotamus fluviatilis'
COMMON['nl']['Hyena'] = 'Gevlekte hyena'
COMMON['sv']['Hyena'] = 'Fläckig hyena'

TEMPORAL = {
    'de': {'Next week': 'Nächste Woche', 'Next month': 'Nächsten Monat', 'Next year': 'Nächstes Jahr', 'Soon': 'Bald', 'Later': 'Später', 'Next': 'Nächste', 'Tomorrow': 'Morgen', 'Yesterday': 'Gestern', 'Today': 'Heute', 'This week': 'Diese Woche', 'Last week': 'Letzte Woche', 'Every week': 'Jede Woche', 'During': 'Während', 'Plan': 'Plan', 'Intend': 'Beabsichtigen', 'Promise': 'Versprechen', 'Expect': 'Erwarten'},
    'it': {'Next week': 'La prossima settimana', 'Next month': 'Il prossimo mese', 'Next year': 'Il prossimo anno', 'Soon': 'Presto', 'Later': 'Più tardi', 'Next': 'Prossimo', 'Tomorrow': 'Domani', 'Yesterday': 'Ieri', 'Today': 'Oggi', 'This week': 'Questa settimana', 'Last week': 'La settimana scorsa', 'Every week': 'Ogni settimana', 'During': 'Durante', 'Plan': 'Piano', 'Intend': 'Intendere', 'Promise': 'Promessa', 'Expect': 'Aspettarsi'},
    'pt': {'Will': 'Vai', 'Next week': 'Próxima semana', 'Next month': 'Próximo mês', 'Next year': 'Próximo ano', 'Soon': 'Em breve', 'Later': 'Mais tarde', 'Next': 'Próximo', 'Tomorrow': 'Amanhã', 'Yesterday': 'Ontem', 'Today': 'Hoje', 'This week': 'Esta semana', 'Last week': 'Semana passada', 'Every week': 'Toda semana', 'During': 'Durante', 'Plan': 'Plano', 'Intend': 'Pretender', 'Promise': 'Promessa', 'Expect': 'Esperar'},
    'ja': {'Will': '〜するでしょう', 'Next week': '来週', 'Next month': '来月', 'Next year': '来年', 'Soon': 'すぐに', 'Later': '後で', 'Next': '次の', 'Tomorrow': '明日', 'Yesterday': '昨日', 'Today': '今日', 'This week': '今週', 'Last week': '先週', 'Every week': '毎週', 'During': '〜の間', 'Plan': '計画', 'Intend': '意図する', 'Promise': '約束', 'Expect': '期待する'},
    'ko': {'Will': '할 것입니다', 'Next week': '다음 주', 'Next month': '다음 달', 'Next year': '내년', 'Soon': '곧', 'Later': '나중에', 'Next': '다음', 'Tomorrow': '내일', 'Yesterday': '어제', 'Today': '오늘', 'This week': '이번 주', 'Last week': '지난주', 'Every week': '매주', 'During': '동안', 'Plan': '계획', 'Intend': '의도하다', 'Promise': '약속', 'Expect': '기대하다'},
    'zh': {'Will': '将会', 'Next week': '下周', 'Next month': '下个月', 'Next year': '明年', 'Soon': '很快', 'Later': '稍后', 'Next': '下一个', 'Tomorrow': '明天', 'Yesterday': '昨天', 'Today': '今天', 'This week': '本周', 'Last week': '上周', 'Every week': '每周', 'During': '期间', 'Plan': '计划', 'Intend': '打算', 'Promise': '承诺', 'Expect': '期待'},
    'ar': {'Will': 'سوف', 'Next week': 'الأسبوع القادم', 'Next month': 'الشهر القادم', 'Next year': 'العام القادم', 'Soon': 'قريبا', 'Later': 'لاحقا', 'Next': 'التالي', 'Tomorrow': 'غدا', 'Yesterday': 'أمس', 'Today': 'اليوم', 'This week': 'هذا الأسبوع', 'Last week': 'الأسبوع الماضي', 'Every week': 'كل أسبوع', 'During': 'خلال', 'Plan': 'خطة', 'Intend': 'ينوي', 'Promise': 'وعد', 'Expect': 'يتوقع'},
    'ru': {'Will': 'Будет', 'Next week': 'На следующей неделе', 'Next month': 'В следующем месяце', 'Next year': 'В следующем году', 'Soon': 'Скоро', 'Later': 'Позже', 'Next': 'Следующий', 'Tomorrow': 'Завтра', 'Yesterday': 'Вчера', 'Today': 'Сегодня', 'This week': 'На этой неделе', 'Last week': 'На прошлой неделе', 'Every week': 'Каждую неделю', 'During': 'Во время', 'Plan': 'План', 'Intend': 'Намереваться', 'Promise': 'Обещание', 'Expect': 'Ожидать'},
    'hi': {'Will': 'होगा', 'Next week': 'अगले सप्ताह', 'Next month': 'अगले महीने', 'Next year': 'अगले वर्ष', 'Soon': 'जल्द', 'Later': 'बाद में', 'Next': 'अगला', 'Tomorrow': 'कल', 'Yesterday': 'कल', 'Today': 'आज', 'This week': 'इस सप्ताह', 'Last week': 'पिछले सप्ताह', 'Every week': 'हर सप्ताह', 'During': 'दौरान', 'Plan': 'योजना', 'Intend': 'इरादा रखना', 'Promise': 'वादा', 'Expect': 'उम्मीद करना'},
    'tr': {'Will': 'Gidecek', 'Next week': 'Gelecek hafta', 'Next month': 'Gelecek ay', 'Next year': 'Gelecek yıl', 'Soon': 'Yakında', 'Later': 'Daha sonra', 'Next': 'Sonraki', 'Tomorrow': 'Yarın', 'Yesterday': 'Dün', 'Today': 'Bugün', 'This week': 'Bu hafta', 'Last week': 'Geçen hafta', 'Every week': 'Her hafta', 'During': 'Sırasında', 'Plan': 'Plan', 'Intend': 'Niyet etmek', 'Promise': 'Söz', 'Expect': 'Beklemek'},
    'nl': {'Will': 'Zal', 'Next week': 'Volgende week', 'Next month': 'Volgende maand', 'Next year': 'Volgend jaar', 'Soon': 'Binnenkort', 'Later': 'Later', 'Next': 'Volgende', 'Tomorrow': 'Morgen', 'Yesterday': 'Gisteren', 'Today': 'Vandaag', 'This week': 'Deze week', 'Last week': 'Vorige week', 'Every week': 'Elke week', 'During': 'Tijdens', 'Plan': 'Plan', 'Intend': 'Van plan zijn', 'Promise': 'Belofte', 'Expect': 'Verwachten'},
    'sv': {'Will': 'Kommer att', 'Next week': 'Nästa vecka', 'Next month': 'Nästa månad', 'Next year': 'Nästa år', 'Soon': 'Snart', 'Later': 'Senare', 'Next': 'Nästa', 'Tomorrow': 'Imorgon', 'Yesterday': 'Igår', 'Today': 'Idag', 'This week': 'Den här veckan', 'Last week': 'Förra veckan', 'Every week': 'Varje vecka', 'During': 'Under', 'Plan': 'Plan', 'Intend': 'Avse', 'Promise': 'Löfte', 'Expect': 'Förvänta sig'},
    'ga': {'Will': 'Déanfaidh', 'Next week': 'An tseachtain seo chugainn', 'Next month': 'An mhí seo chugainn', 'Next year': 'An bhliain seo chugainn', 'Soon': 'Go luath', 'Later': 'Níos déanaí', 'Next': 'Ar aghaidh', 'Tomorrow': 'Amárach', 'Yesterday': 'Inné', 'Today': 'Inniu', 'This week': 'An tseachtain seo', 'Last week': 'An tseachtain seo caite', 'Every week': 'Gach seachtain', 'During': 'Le linn', 'Plan': 'Plean', 'Intend': 'Tá sé ar intinn', 'Promise': 'Gealltanas', 'Expect': 'Bí ag súil'},
    'el': {'Will': 'Θα', 'Next week': 'Την επόμενη εβδομάδα', 'Next month': 'Τον επόμενο μήνα', 'Next year': 'Του χρόνου', 'Soon': 'Σύντομα', 'Later': 'Αργότερα', 'Next': 'Επόμενος', 'Tomorrow': 'Αύριο', 'Yesterday': 'Χθες', 'Today': 'Σήμερα', 'This week': 'Αυτή την εβδομάδα', 'Last week': 'Την περασμένη εβδομάδα', 'Every week': 'Κάθε εβδομάδα', 'During': 'Κατά τη διάρκεια', 'Plan': 'Σχέδιο', 'Intend': 'Σκοπεύω', 'Promise': 'Υπόσχεση', 'Expect': 'Περιμένω'},
    'he': {'Will': 'יהיה', 'Next week': 'בשבוע הבא', 'Next month': 'בחודש הבא', 'Next year': 'בשנה הבאה', 'Soon': 'בקרוב', 'Later': 'מאוחר יותר', 'Next': 'הבא', 'Tomorrow': 'מחר', 'Yesterday': 'אתמול', 'Today': 'היום', 'This week': 'השבוע', 'Last week': 'בשבוע שעבר', 'Every week': 'כל שבוע', 'During': 'במהלך', 'Plan': 'תוכנית', 'Intend': 'מתכוון', 'Promise': 'הבטחה', 'Expect': 'מצפה'},
    'pl': {'Will': 'Będzie', 'Next week': 'W przyszłym tygodniu', 'Next month': 'W przyszłym miesiącu', 'Next year': 'W przyszłym roku', 'Soon': 'Wkrótce', 'Later': 'Później', 'Next': 'Następny', 'Tomorrow': 'Jutro', 'Yesterday': 'Wczoraj', 'Today': 'Dzisiaj', 'This week': 'W tym tygodniu', 'Last week': 'W zeszłym tygodniu', 'Every week': 'Co tydzień', 'During': 'Podczas', 'Plan': 'Plan', 'Intend': 'Zamierzać', 'Promise': 'Obietnica', 'Expect': 'Oczekiwać'},
    'no': {'Will': 'Vil', 'Next week': 'Neste uke', 'Next month': 'Neste måned', 'Next year': 'Neste år', 'Soon': 'Snart', 'Later': 'Senere', 'Next': 'Neste', 'Tomorrow': 'I morgen', 'Yesterday': 'I går', 'Today': 'I dag', 'This week': 'Denne uken', 'Last week': 'Forrige uke', 'Every week': 'Hver uke', 'During': 'I løpet av', 'Plan': 'Plan', 'Intend': 'Ha til hensikt', 'Promise': 'Løfte', 'Expect': 'Forvente'},
    'da': {'Will': 'Vil', 'Next week': 'Næste uge', 'Next month': 'Næste måned', 'Next year': 'Næste år', 'Soon': 'Snart', 'Later': 'Senere', 'Next': 'Næste', 'Tomorrow': 'I morgen', 'Yesterday': 'I går', 'Today': 'I dag', 'This week': 'Denne uge', 'Last week': 'Sidste uge', 'Every week': 'Hver uge', 'During': 'Under', 'Plan': 'Plan', 'Intend': 'Have til hensigt', 'Promise': 'Løfte', 'Expect': 'Forvente'},
    'fi': {'Will': 'Aikoo', 'Next week': 'Ensi viikolla', 'Next month': 'Ensi kuussa', 'Next year': 'Ensi vuonna', 'Soon': 'Pian', 'Later': 'Myöhemmin', 'Next': 'Seuraava', 'Tomorrow': 'Huomenna', 'Yesterday': 'Eilen', 'Today': 'Tänään', 'This week': 'Tällä viikolla', 'Last week': 'Viime viikolla', 'Every week': 'Joka viikko', 'During': 'Aikana', 'Plan': 'Suunnitelma', 'Intend': 'Aikoa', 'Promise': 'Lupaus', 'Expect': 'Odottaa'},
    'cs': {'Will': 'Bude', 'Next week': 'Příští týden', 'Next month': 'Příští měsíc', 'Next year': 'Příští rok', 'Soon': 'Brzy', 'Later': 'Později', 'Next': 'Další', 'Tomorrow': 'Zítra', 'Yesterday': 'Včera', 'Today': 'Dnes', 'This week': 'Tento týden', 'Last week': 'Minulý týden', 'Every week': 'Každý týden', 'During': 'Během', 'Plan': 'Plán', 'Intend': 'Mít v úmyslu', 'Promise': 'Slib', 'Expect': 'Očekávat'},
    'id': {'Will': 'Akan', 'Next week': 'Minggu depan', 'Next month': 'Bulan depan', 'Next year': 'Tahun depan', 'Soon': 'Segera', 'Later': 'Nanti', 'Next': 'Berikutnya', 'Tomorrow': 'Besok', 'Yesterday': 'Kemarin', 'Today': 'Hari ini', 'This week': 'Minggu ini', 'Last week': 'Minggu lalu', 'Every week': 'Setiap minggu', 'During': 'Selama', 'Plan': 'Rencana', 'Intend': 'Berniat', 'Promise': 'Janji', 'Expect': 'Mengharapkan'},
    'uk': {'Will': 'Буде', 'Next week': 'Наступного тижня', 'Next month': 'Наступного місяця', 'Next year': 'Наступного року', 'Soon': 'Скоро', 'Later': 'Пізніше', 'Next': 'Наступний', 'Tomorrow': 'Завтра', 'Yesterday': 'Вчора', 'Today': 'Сьогодні', 'This week': 'Цього тижня', 'Last week': 'Минулого тижня', 'Every week': 'Щотижня', 'During': 'Під час', 'Plan': 'План', 'Intend': 'Мати намір', 'Promise': 'Обіцянка', 'Expect': 'Очікувати'},
    'sw': {'Will': 'Ata', 'Next week': 'Wiki ijayo', 'Next month': 'Mwezi ujao', 'Next year': 'Mwaka ujao', 'Soon': 'Hivi karibuni', 'Later': 'Baadaye', 'Next': 'Ifuatayo', 'Tomorrow': 'Kesho', 'Yesterday': 'Jana', 'Today': 'Leo', 'This week': 'Wiki hii', 'Last week': 'Wiki iliyopita', 'Every week': 'Kila wiki', 'During': 'Wakati wa', 'Plan': 'Mpango', 'Intend': 'Kukusudia', 'Promise': 'Ahadi', 'Expect': 'Kutarajia'},
    'vi': {'Will': 'Sẽ', 'Next week': 'Tuần tới', 'Next month': 'Tháng tới', 'Next year': 'Năm tới', 'Soon': 'Sớm', 'Later': 'Sau', 'Next': 'Tiếp theo', 'Tomorrow': 'Ngày mai', 'Yesterday': 'Hôm qua', 'Today': 'Hôm nay', 'This week': 'Tuần này', 'Last week': 'Tuần trước', 'Every week': 'Mỗi tuần', 'During': 'Trong', 'Plan': 'Kế hoạch', 'Intend': 'Dự định', 'Promise': 'Lời hứa', 'Expect': 'Mong đợi'},
    'zu': {'Will': 'Uzokwenza', 'Next week': 'Ngesonto elizayo', 'Next month': 'Ngenyanga ezayo', 'Next year': 'Ngonyaka ozayo', 'Soon': 'Maduze', 'Later': 'Kamuva', 'Next': 'Okulandelayo', 'Tomorrow': 'Kusasa', 'Yesterday': 'Izolo', 'Today': 'Namuhla', 'This week': 'Kuleli sonto', 'Last week': 'Ngesonto eledlule', 'Every week': 'Njalo ngesonto', 'During': 'Ngesikhathi', 'Plan': 'Uhlelo', 'Intend': 'Ukuhlose', 'Promise': 'Isithembiso', 'Expect': 'Ukulindela'},
    'gd': {'Will': 'Nì', 'Next week': 'An ath-sheachdain sa tighinn', 'Next month': 'An ath-mhìos', 'Next year': 'An ath-bhliadhna', 'Soon': 'A dh’aithghearr', 'Later': 'Nas fhaide air adhart', 'Next': 'An ath-', 'Tomorrow': 'A-màireach', 'Yesterday': 'An-dè', 'Today': 'An-diugh', 'This week': 'An t-seachdain seo', 'Last week': 'An t-seachdain sa chaidh', 'Every week': 'Gach seachdain', 'During': 'Rè', 'Plan': 'Plana', 'Intend': 'Tha dùil', 'Promise': 'Gealladh', 'Expect': 'Sùileachadh'},
    'la': {'Will': 'Vult', 'Next week': 'Proxima hebdomade', 'Next month': 'Proximo mense', 'Next year': 'Proximo anno', 'Soon': 'Mox', 'Later': 'Postea', 'Next': 'Proximus', 'Tomorrow': 'Cras', 'Yesterday': 'Heri', 'Today': 'Hodie', 'This week': 'Hac hebdomade', 'Last week': 'Ultima hebdomade', 'Every week': 'Quaque hebdomade', 'During': 'Durante', 'Plan': 'Consilium', 'Intend': 'Intendere', 'Promise': 'Promissum', 'Expect': 'Exspectare'},
}

# Avoid replacing the auxiliary inside full English sentences (for example,
# "I will go"), which would create mixed-language text. Full-sentence maps
# should handle those phrases explicitly.
for temporal_values in TEMPORAL.values():
    temporal_values.pop('Will', None)

for code in ['ru', 'uk']:
    COMMON.setdefault(code, {}).update({'Cow': 'Корова', 'Lion': 'Лев', 'Dog': 'Собака', 'Cat': 'Кошка', 'eats': 'ест', 'drinks': 'пьёт', 'sleeps': 'спит', 'runs': 'бегает', 'flies': 'летает', 'swims': 'плавает', 'plays': 'играет', 'grass': 'трава', 'water': 'вода', 'meat': 'мясо', 'milk': 'молоко', 'Chicken': 'Курица', 'Goat': 'Коза', 'Sheep': 'Овца', 'Fish': 'Рыба'})
for code in ['hi']:
    COMMON.setdefault(code, {}).update({'Cow': 'गाय', 'Lion': 'शेर', 'Dog': 'कुत्ता', 'Cat': 'बिल्ली', 'eats': 'खाता है', 'drinks': 'पीता है', 'sleeps': 'सोता है', 'runs': 'दौड़ता है', 'flies': 'उड़ता है', 'swims': 'तैरता है', 'plays': 'खेलता है', 'grass': 'घास', 'water': 'पानी', 'meat': 'मांस', 'milk': 'दूध', 'Chicken': 'मुर्गी', 'Goat': 'बकरी', 'Sheep': 'भेड़', 'Fish': 'मछली', 'Donkey': 'गधा', 'Pigeon': 'कबूतर', 'Mountain gorilla': 'पर्वतीय गोरिल्ला', 'Leopard': 'तेंदुआ', 'Buffalo': 'भैंस', 'Zebra': 'ज़ेब्रा', 'Hippopotamus': 'दरियाई घोड़ा', 'Giraffe': 'जिराफ़', 'Hyena': 'लकड़बग्घा', 'Monkey': 'बंदर', 'Grey crowned crane': 'ग्रे क्राउन क्रेन', 'Snake': 'साँप', 'Frog': 'मेंढक', 'Duck': 'बत्तख'})
for code in ['tr', 'az']:
    COMMON.setdefault(code, {}).update({'Cow': 'İnek', 'Lion': 'Aslan', 'Dog': 'Köpek', 'Cat': 'Kedi', 'eats': 'yer', 'drinks': 'içer', 'sleeps': 'uyur', 'runs': 'koşar', 'flies': 'uçar', 'swims': 'yüzer', 'plays': 'oynar', 'grass': 'çimen', 'water': 'su', 'meat': 'et', 'milk': 'süt', 'Chicken': 'Tavuk', 'Goat': 'Keçi', 'Sheep': 'Koyun', 'Fish': 'Balık'})
for code, values in {
    'nl': ('Koe', 'Leeuw', 'Hond', 'Kat', 'eet', 'drinkt', 'slaapt', 'rent', 'vliegt', 'zwemt', 'speelt', 'gras', 'water', 'vlees', 'melk', 'Kip', 'Geit', 'Schaap', 'Vis'),
    'sv': ('Ko', 'Lejon', 'Hund', 'Katt', 'äter', 'dricker', 'sover', 'springer', 'flyger', 'simmar', 'leker', 'gräs', 'vatten', 'kött', 'mjölk', 'Kyckling', 'Get', 'Får', 'Fisk'),
    'da': ('Ko', 'Løve', 'Hund', 'Kat', 'spiser', 'drikker', 'sover', 'løber', 'flyver', 'svømmer', 'leger', 'græs', 'vand', 'kød', 'mælk', 'Kylling', 'Ged', 'Får', 'Fisk'),
    'no': ('Ku', 'Løve', 'Hund', 'Katt', 'spiser', 'drikker', 'sover', 'løper', 'flyr', 'svømmer', 'leker', 'gress', 'vann', 'kjøtt', 'melk', 'Kylling', 'Geit', 'Sau', 'Fisk'),
}.items():
    COMMON.setdefault(code, {}).update(dict(zip(['Cow','Lion','Dog','Cat','eats','drinks','sleeps','runs','flies','swims','plays','grass','water','meat','milk','Chicken','Goat','Sheep','Fish'], values)))

for code, values in EXTRA_ANIMALS.items():
    COMMON.setdefault(code, {}).update(values)

requested_codes = [code.lower() for code in sys.argv[1:]] or list(LANGUAGE_MODULES)
SOURCE_KEYS = {
    'id', 'type', 'xp_reward', 'difficulty', 'time_limit_seconds', 'topic',
    'audio', 'audio_url', 'audio_file', 'tts_audio', 'image', 'image_url',
    'link', 'prompt', 'statement', 'context_sentence',
    'bot_message', 'word',
}
UNTRANSLATED_TTS_WORDS = {
    'the', 'a', 'an', 'is', 'are', 'was', 'were', 'above', 'below', 'near',
    'apple', 'apples', 'sky', 'red', 'blue', 'green', 'yellow', 'black', 'white',
    'orange', 'eats', 'drinks', 'sleeps', 'runs', 'flies', 'swims', 'plays',
    'grass', 'water', 'meat', 'milk', 'field', 'carrot', 'birds', 'chickens',
    'cows', 'dogs', 'rabbits', 'fish', 'frog', 'bird', 'chicken',
}
for code in requested_codes:
    folder = f'RW-TO-{code.upper()}'
    module = importlib.import_module(LANGUAGE_MODULES[code])
    language_config = getattr(module, 'LANGUAGES').get(code, {})
    if isinstance(language_config, tuple):
        mapping = dict(language_config[1])
    else:
        mapping = dict(language_config.get('map', language_config.get('replacements', {})))
    mapping.update(COMMON.get(code, {}))
    mapping.update(TEMPORAL.get(code, {}))
    lookup = {source.casefold(): target for source, target in mapping.items()}
    pattern = re.compile(
        r'(?<!\w)(?:' + '|'.join(re.escape(source) for source in sorted(mapping, key=len, reverse=True)) + r')(?!\w)',
        re.IGNORECASE,
    )
    root = Path('content') / folder
    for path in root.rglob('*.yaml'):
        data = yaml.safe_load(path.read_text(encoding='utf-8')) or {}
        def walk(value, key=''):
            if isinstance(value, dict):
                for child_key, child in value.items():
                    if child_key in SOURCE_KEYS:
                        continue
                    translated = walk(child, child_key)
                    if translated is not None:
                        value[child_key] = translated
            elif isinstance(value, list):
                for index, child in enumerate(value):
                    translated = walk(child, key)
                    if translated is not None:
                        value[index] = translated
            elif isinstance(value, str):
                result = pattern.sub(lambda match: lookup.get(match.group(0).casefold(), match.group(0)), value)
                if key == 'tts_text':
                    for word in sorted(UNTRANSLATED_TTS_WORDS, key=len, reverse=True):
                        result = re.sub(r'(?<!\w)' + re.escape(word) + r'(?!\w)', '', result, flags=re.IGNORECASE)
                    result = re.sub(r'\s+', ' ', result).strip()
                return result if result != value else None
        walk(data)
        path.write_text(yaml.safe_dump(data, allow_unicode=True, sort_keys=False, width=120), encoding='utf-8')
    print(f'Fixed answer values in {folder}')
