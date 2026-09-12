from __future__ import annotations

import copy
import re
from pathlib import Path

import yaml

SOURCE_ROOT = Path('content/EN-TO-RW/EN-TO-RW')

BASE = {
    'How do you say': {'da': 'Hvordan siger man', 'fi': 'Kuinka sanotaan', 'cs': 'Jak se řekne', 'id': 'Bagaimana mengatakan', 'uk': 'Як сказати', 'sw': 'Unasemaje', 'vi': 'Nói thế nào', 'zu': 'Usho kanjani', 'gd': 'Ciamar a chanas tu', 'la': 'Quomodo dicitur'},
    'in Kinyarwanda?': {'da': 'på kinyarwanda?', 'fi': 'kinyarwandaksi?', 'cs': 'v kinyarwandštině?', 'id': 'dalam bahasa Kinyarwanda?', 'uk': 'кіньяруанда?', 'sw': 'kwa Kinyarwanda?', 'vi': 'bằng tiếng Kinyarwanda?', 'zu': 'ngesiKinyarwanda?', 'gd': 'ann an Kinyarwanda?', 'la': 'Kinyarwanda?'}
}

LANGUAGES = {
    'da': ('DA-TO-RW', {'What is': 'Hvad er', 'What does': 'Hvad betyder', 'mean?': 'betyder?', 'Translate this lesson term': 'Oversæt dette lektionsord', 'Answer': 'Svar', 'Question': 'Spørgsmål', 'Practice': 'Øvelse', 'Learn': 'Lær', 'Listen': 'Lyt', 'Read': 'Læs', 'Write': 'Skriv', 'Speak': 'Tal', 'Choose': 'Vælg', 'Correct': 'Korrekt', 'Word': 'Ord', 'Sentence': 'Sætning', 'Greetings': 'Hilsner', 'Numbers': 'Tal', 'Family': 'Familie', 'Colors': 'Farver', 'Animals': 'Dyr', 'Food': 'Mad', 'Weather': 'Vejr', 'House': 'Hus', 'Travel': 'Rejser', 'Shopping': 'Indkøb', 'Restaurant': 'Restaurant', 'Directions': 'Retninger', 'Emotions': 'Følelser', 'Culture': 'Kultur', 'Business': 'Forretning', 'Dog': 'Hund', 'Cat': 'Kat', 'Cow': 'Ko', 'Goat': 'Ged', 'Sheep': 'Får', 'Bird': 'Fugl', 'Lion': 'Løve', 'Fish': 'Fisk', 'Water': 'Vand', 'Left': 'Venstre', 'Right': 'Højre', 'Straight': 'Ligeud', 'Tomorrow': 'I morgen', 'Yesterday': 'I går', 'Hello': 'Hej', 'Goodbye': 'Farvel', 'Happy': 'Glad', 'Sad': 'Trist', 'Red': 'Rød', 'Blue': 'Blå', 'Green': 'Grøn', 'Yellow': 'Gul', 'Black': 'Sort', 'White': 'Hvid', 'business': 'forretning', 'company': 'virksomhed', 'employee': 'medarbejder', 'client': 'kunde', 'meeting': 'møde', 'project': 'projekt', 'market': 'marked', 'school': 'skole', 'house': 'hus', 'water': 'vand'}),
    'fi': ('FI-TO-RW', {'What is': 'Mikä on', 'What does': 'Mitä tarkoittaa', 'mean?': 'tarkoittaa?', 'Translate this lesson term': 'Käännä tämän oppitunnin termi', 'Answer': 'Vastaus', 'Question': 'Kysymys', 'Practice': 'Harjoittele', 'Learn': 'Opi', 'Listen': 'Kuuntele', 'Read': 'Lue', 'Write': 'Kirjoita', 'Speak': 'Puhu', 'Choose': 'Valitse', 'Correct': 'Oikea', 'Word': 'Sana', 'Sentence': 'Lause', 'Greetings': 'Tervehdykset', 'Numbers': 'Numerot', 'Family': 'Perhe', 'Colors': 'Värit', 'Animals': 'Eläimet', 'Food': 'Ruoka', 'Weather': 'Sää', 'House': 'Talo', 'Travel': 'Matkustaminen', 'Shopping': 'Ostokset', 'Restaurant': 'Ravintola', 'Directions': 'Ohjeet', 'Emotions': 'Tunteet', 'Culture': 'Kulttuuri', 'Business': 'Liiketoiminta', 'Dog': 'Koira', 'Cat': 'Kissa', 'Cow': 'Lehmä', 'Goat': 'Vuohi', 'Sheep': 'Lammas', 'Bird': 'Lintu', 'Lion': 'Leijona', 'Fish': 'Kala', 'Water': 'Vesi', 'Left': 'Vasen', 'Right': 'Oikea', 'Straight': 'Suoraan', 'Tomorrow': 'Huomenna', 'Yesterday': 'Eilen', 'Hello': 'Hei', 'Goodbye': 'Näkemiin', 'Happy': 'Onnellinen', 'Sad': 'Surullinen', 'Red': 'Punainen', 'Blue': 'Sininen', 'Green': 'Vihreä', 'Yellow': 'Keltainen', 'Black': 'Musta', 'White': 'Valkoinen', 'business': 'liiketoiminta', 'company': 'yritys', 'employee': 'työntekijä', 'client': 'asiakas', 'meeting': 'kokous', 'project': 'projekti', 'market': 'markkina', 'school': 'koulu', 'house': 'talo', 'water': 'vesi'}),
    'cs': ('CS-TO-RW', {'What is': 'Co je', 'What does': 'Co znamená', 'mean?': 'znamená?', 'Translate this lesson term': 'Přeložte tento termín lekce', 'Answer': 'Odpověď', 'Question': 'Otázka', 'Practice': 'Procvičujte', 'Learn': 'Učte se', 'Listen': 'Poslouchejte', 'Read': 'Čtěte', 'Write': 'Pište', 'Speak': 'Mluvte', 'Choose': 'Vyberte', 'Correct': 'Správně', 'Word': 'Slovo', 'Sentence': 'Věta', 'Greetings': 'Pozdravy', 'Numbers': 'Čísla', 'Family': 'Rodina', 'Colors': 'Barvy', 'Animals': 'Zvířata', 'Food': 'Jídlo', 'Weather': 'Počasí', 'House': 'Dům', 'Travel': 'Cestování', 'Shopping': 'Nakupování', 'Restaurant': 'Restaurace', 'Directions': 'Směry', 'Emotions': 'Emoce', 'Culture': 'Kultura', 'Business': 'Obchod', 'Dog': 'Pes', 'Cat': 'Kočka', 'Cow': 'Krava', 'Goat': 'Koza', 'Sheep': 'Ovce', 'Bird': 'Pták', 'Lion': 'Lev', 'Fish': 'Ryba', 'Water': 'Voda', 'Left': 'Vlevo', 'Right': 'Vpravo', 'Straight': 'Rovně', 'Tomorrow': 'Zítra', 'Yesterday': 'Včera', 'Hello': 'Ahoj', 'Goodbye': 'Na shledanou', 'Happy': 'Šťastný', 'Sad': 'Smutný', 'Red': 'Červená', 'Blue': 'Modrá', 'Green': 'Zelená', 'Yellow': 'Žlutá', 'Black': 'Černá', 'White': 'Bílá', 'business': 'obchod', 'company': 'společnost', 'employee': 'zaměstnanec', 'client': 'klient', 'meeting': 'schůzka', 'project': 'projekt', 'market': 'trh', 'school': 'škola', 'house': 'dům', 'water': 'voda'}),
    'id': ('ID-TO-RW', {'What is': 'Apa itu', 'What does': 'Apa arti', 'mean?': 'artinya?', 'Translate this lesson term': 'Terjemahkan istilah pelajaran ini', 'Answer': 'Jawaban', 'Question': 'Pertanyaan', 'Practice': 'Latihan', 'Learn': 'Belajar', 'Listen': 'Dengarkan', 'Read': 'Baca', 'Write': 'Tulis', 'Speak': 'Bicara', 'Choose': 'Pilih', 'Correct': 'Benar', 'Word': 'Kata', 'Sentence': 'Kalimat', 'Greetings': 'Salam', 'Numbers': 'Angka', 'Family': 'Keluarga', 'Colors': 'Warna', 'Animals': 'Hewan', 'Food': 'Makanan', 'Weather': 'Cuaca', 'House': 'Rumah', 'Travel': 'Perjalanan', 'Shopping': 'Belanja', 'Restaurant': 'Restoran', 'Directions': 'Arah', 'Emotions': 'Emosi', 'Culture': 'Budaya', 'Business': 'Bisnis', 'Dog': 'Anjing', 'Cat': 'Kucing', 'Cow': 'Sapi', 'Goat': 'Kambing', 'Sheep': 'Domba', 'Bird': 'Burung', 'Lion': 'Singa', 'Fish': 'Ikan', 'Pig': 'Babi', 'Chicken': 'Ayam', 'Rabbit': 'Kelinci', 'Donkey': 'Keledai', 'Pigeon': 'Merpati', 'Elephant': 'Gajah', 'Leopard': 'Macan tutul', 'Buffalo': 'Kerbau', 'Zebra': 'Zebra', 'Hippopotamus': 'Kuda nil', 'Giraffe': 'Jerapah', 'Hyena': 'Hyena', 'Monkey': 'Monyet', 'Grey crowned crane': 'Kuntul perak', 'Snake': 'Ular', 'Frog': 'Katak', 'Bee': 'Lebah', 'Water': 'Air', 'Left': 'Kiri', 'Right': 'Kanan', 'Straight': 'Lurus', 'Tomorrow': 'Besok', 'Yesterday': 'Kemarin', 'Hello': 'Halo', 'Goodbye': 'Sampai jumpa', 'Happy': 'Senang', 'Sad': 'Sedih', 'Red': 'Merah', 'Blue': 'Biru', 'Green': 'Hijau', 'Yellow': 'Kuning', 'Black': 'Hitam', 'White': 'Putih', 'business': 'bisnis', 'company': 'perusahaan', 'employee': 'karyawan', 'client': 'klien', 'meeting': 'rapat', 'project': 'proyek', 'market': 'pasar', 'school': 'sekolah', 'house': 'rumah', 'water': 'air', 'How do you ask': 'Bagaimana cara bertanya', 'How do you say': 'Bagaimana mengatakan', 'in Kinyarwanda?': 'dalam bahasa Kinyarwanda?', 'How much is this shirt?': 'Berapa harga baju ini?', 'How much is this banana?': 'Berapa harga pisang ini?', 'How much does this cost?': 'Berapa harga ini?', 'Where is the airport?': 'Di mana bandara itu?', 'Where is the cashier?': 'Di mana kasir?', 'How do I get to the hotel?': 'Bagaimana saya bisa sampai ke hotel?', 'What time does the train leave?': 'Jam berapa kereta berangkat?', 'Can you show me the map?': 'Bisakah Anda menunjukkan peta kepada saya?', 'What day is it today?': 'Hari apa hari ini?', 'What time is it?': 'Jam berapa sekarang?', 'What do you think?': 'Apa pendapat Anda?', 'Can you repeat that?': 'Bisakah Anda mengulanginya?', 'Could you explain that?': 'Bisakah Anda menjelaskan itu?', 'Can you correct me if I\'m wrong?': 'Bisakah Anda membetulkan saya jika saya salah?', 'What are the conditions?': 'Apa syarat-syaratnya?', 'What are the risks and benefits?': 'Apa risiko dan manfaatnya?', 'Can you reduce the price?': 'Bisakah Anda menurunkan harganya?', 'Can we discuss the terms?': 'Bisakah kita membahas syarat-syaratnya?', 'What color is the apple?': 'Warna apa apel itu?', 'What color is the sky?': 'Warna apa langit itu?', 'Is the cat black?': 'Apakah kucing itu hitam?', 'How is the family?': 'Bagaimana keluarga itu?', 'How are you?': 'Bagaimana kabarmu?', 'What\'s up?': 'Ada apa?', 'How are you today?': 'Bagaimana kabar Anda hari ini?', 'How much is this bag?': 'Berapa harga tas ini?', 'Can I see the menu?': 'Bisakah saya melihat menunya?', 'Can I have the bill?': 'Bisakah saya meminta tagihannya?', 'Can I pay with card?': 'Bisakah saya membayar dengan kartu?', 'Do you have this in another size?': 'Apakah Anda punya ini dalam ukuran lain?', 'Can I try it on?': 'Bisakah saya mencobanya?', 'Can I sit here?': 'Bisakan saya duduk di sini?', 'What do you do in the morning?': 'Apa yang Anda lakukan di pagi hari?', 'How much is a ticket?': 'Berapa harga tiket?', 'Is there a bus to the airport?': 'Apakah ada bus ke bandara?', 'Can I have the bill?': 'Bisakah saya meminta tagihannya?', 'What are the conditions?': 'Apa syarat-syaratnya?', 'Can you give me a bag?': 'Bisakah Anda memberi saya tas?', 'What day is it today?': 'Hari apa hari ini?', 'What time is it?': 'Jam berapa sekarang?', 'What do you think?': 'Apa pendapat Anda?', 'Can you explain why?': 'Bisakah Anda menjelaskan alasannya?', 'Do you have vegetarian food?': 'Apakah Anda punya makanan vegetarian?', 'What color is the apple?': 'Warna apa apel itu?', 'What color is the sky?': 'Warna apa langit itu?', 'Is the cat black?': 'Apakah kucing itu hitam?', 'How is the family?': 'Bagaimana keluarga itu?'}),
    'uk': ('UK-TO-RW', {'What is': 'Що таке', 'What does': 'Що означає', 'mean?': 'означає?', 'Translate this lesson term': 'Перекладіть термін цього уроку', 'Answer': 'Відповідь', 'Question': 'Питання', 'Practice': 'Практика', 'Learn': 'Вчити', 'Listen': 'Слухати', 'Read': 'Читати', 'Write': 'Писати', 'Speak': 'Говорити', 'Choose': 'Оберіть', 'Correct': 'Правильно', 'Word': 'Слово', 'Sentence': 'Речення', 'Greetings': 'Вітання', 'Numbers': 'Числа', 'Family': 'Сім’я', 'Colors': 'Кольори', 'Animals': 'Тварини', 'Food': 'Їжа', 'Weather': 'Погода', 'House': 'Будинок', 'Travel': 'Подорожі', 'Shopping': 'Покупки', 'Restaurant': 'Ресторан', 'Directions': 'Напрямки', 'Emotions': 'Емоції', 'Culture': 'Культура', 'Business': 'Бізнес', 'Dog': 'Собака', 'Cat': 'Кішка', 'Cow': 'Корова', 'Goat': 'Коза', 'Sheep': 'Вівця', 'Bird': 'Птах', 'Lion': 'Лев', 'Fish': 'Риба', 'Water': 'Вода', 'Left': 'Ліворуч', 'Right': 'Праворуч', 'Straight': 'Прямо', 'Tomorrow': 'Завтра', 'Yesterday': 'Вчора', 'Hello': 'Привіт', 'Goodbye': 'До побачення', 'Happy': 'Щасливий', 'Sad': 'Сумний', 'Red': 'Червоний', 'Blue': 'Синій', 'Green': 'Зелений', 'Yellow': 'Жовтий', 'Black': 'Чорний', 'White': 'Білий', 'business': 'бізнес', 'company': 'компанія', 'employee': 'працівник', 'client': 'клієнт', 'meeting': 'зустріч', 'project': 'проєкт', 'market': 'ринок', 'school': 'школа', 'house': 'будинок', 'water': 'вода'}),
    'sw': ('SW-TO-RW', {'What is': 'Ni nini', 'What does': 'Inamaanisha nini', 'mean?': 'inamaanisha?', 'Translate this lesson term': 'Tafsiri neno la somo hili', 'Answer': 'Jibu', 'Question': 'Swali', 'Practice': 'Mazoezi', 'Learn': 'Jifunze', 'Listen': 'Sikiliza', 'Read': 'Soma', 'Write': 'Andika', 'Speak': 'Ongea', 'Choose': 'Chagua', 'Correct': 'Sahihi', 'Word': 'Neno', 'Sentence': 'Sentensi', 'Greetings': 'Salamu', 'Numbers': 'Nambari', 'Family': 'Familia', 'Colors': 'Rangi', 'Animals': 'Wanyama', 'Food': 'Chakula', 'Weather': 'Hali ya hewa', 'House': 'Nyumba', 'Travel': 'Safari', 'Shopping': 'Ununuzi', 'Restaurant': 'Mkahawa', 'Directions': 'Maelekezo', 'Emotions': 'Hisia', 'Culture': 'Utamaduni', 'Business': 'Biashara', 'Dog': 'Mbwa', 'Cat': 'Paka', 'Cow': 'Ng’ombe', 'Goat': 'Mbuzi', 'Sheep': 'Kondoo', 'Bird': 'Ndege', 'Lion': 'Simba', 'Fish': 'Samaki', 'Water': 'Maji', 'Left': 'Kushoto', 'Right': 'Kulia', 'Straight': 'Moja kwa moja', 'Tomorrow': 'Kesho', 'Yesterday': 'Jana', 'Hello': 'Habari', 'Goodbye': 'Kwaheri', 'Happy': 'Furaha', 'Sad': 'Huzuni', 'Red': 'Nyekundu', 'Blue': 'Bluu', 'Green': 'Kijani', 'Yellow': 'Njano', 'Black': 'Nyeusi', 'White': 'Nyeupe', 'business': 'biashara', 'company': 'kampuni', 'employee': 'mfanyakazi', 'client': 'mteja', 'meeting': 'mkutano', 'project': 'mradi', 'market': 'soko', 'school': 'shule', 'house': 'nyumba', 'water': 'maji'}),
    'vi': ('VI-TO-RW', {'What is': 'Là gì', 'What does': 'Có nghĩa là gì', 'mean?': 'có nghĩa là gì?', 'Translate this lesson term': 'Dịch thuật ngữ bài học này', 'Answer': 'Câu trả lời', 'Question': 'Câu hỏi', 'Practice': 'Luyện tập', 'Learn': 'Học', 'Listen': 'Nghe', 'Read': 'Đọc', 'Write': 'Viết', 'Speak': 'Nói', 'Choose': 'Chọn', 'Correct': 'Đúng', 'Word': 'Từ', 'Sentence': 'Câu', 'Greetings': 'Lời chào', 'Numbers': 'Số', 'Family': 'Gia đình', 'Colors': 'Màu sắc', 'Animals': 'Động vật', 'Food': 'Thức ăn', 'Weather': 'Thời tiết', 'House': 'Nhà', 'Travel': 'Du lịch', 'Shopping': 'Mua sắm', 'Restaurant': 'Nhà hàng', 'Directions': 'Chỉ đường', 'Emotions': 'Cảm xúc', 'Culture': 'Văn hóa', 'Business': 'Kinh doanh', 'Dog': 'Con chó', 'Cat': 'Con mèo', 'Cow': 'Con bò', 'Goat': 'Con dê', 'Sheep': 'Con cừu', 'Bird': 'Con chim', 'Lion': 'Sư tử', 'Fish': 'Cá', 'Water': 'Nước', 'Left': 'Trái', 'Right': 'Phải', 'Straight': 'Thẳng', 'Tomorrow': 'Ngày mai', 'Yesterday': 'Hôm qua', 'Hello': 'Xin chào', 'Goodbye': 'Tạm biệt', 'Happy': 'Vui', 'Sad': 'Buồn', 'Red': 'Đỏ', 'Blue': 'Xanh dương', 'Green': 'Xanh lá', 'Yellow': 'Vàng', 'Black': 'Đen', 'White': 'Trắng', 'business': 'kinh doanh', 'company': 'công ty', 'employee': 'nhân viên', 'client': 'khách hàng', 'meeting': 'cuộc họp', 'project': 'dự án', 'market': 'chợ', 'school': 'trường học', 'house': 'nhà', 'water': 'nước'}),
    'zu': ('ZU-TO-RW', {'What is': 'Yini', 'What does': 'Kusho ukuthini', 'mean?': 'kusho ukuthini?', 'Translate this lesson term': 'Humusha leli gama lesifundo', 'Answer': 'Impendulo', 'Question': 'Umbuzo', 'Practice': 'Zijwayeze', 'Learn': 'Funda', 'Listen': 'Lalela', 'Read': 'Funda', 'Write': 'Bhala', 'Speak': 'Khuluma', 'Choose': 'Khetha', 'Correct': 'Lungile', 'Word': 'Igama', 'Sentence': 'Umusho', 'Greetings': 'Ukubingelela', 'Numbers': 'Izinombolo', 'Family': 'Umndeni', 'Colors': 'Imibala', 'Animals': 'Izilwane', 'Food': 'Ukudla', 'Weather': 'Isimo sezulu', 'House': 'Indlu', 'Travel': 'Uhambo', 'Shopping': 'Ukuthenga', 'Restaurant': 'Indawo yokudlela', 'Directions': 'Izikhombisi-ndlela', 'Emotions': 'Imizwa', 'Culture': 'Amasiko', 'Business': 'Ibhizinisi', 'Dog': 'Inja', 'Cat': 'Ikati', 'Cow': 'Inkomo', 'Goat': 'Imbuzi', 'Sheep': 'Imvu', 'Bird': 'Inyoni', 'Lion': 'Ibhubesi', 'Fish': 'Inhlanzi', 'Water': 'Amanzi', 'Left': 'Kwesokunxele', 'Right': 'Kwesokudla', 'Straight': 'Qonda', 'Tomorrow': 'Kusasa', 'Yesterday': 'Izolo', 'Hello': 'Sawubona', 'Goodbye': 'Sala kahle', 'Happy': 'Jabula', 'Sad': 'Dabuka', 'Red': 'Bomvu', 'Blue': 'Luhlaza okwesibhakabhaka', 'Green': 'Luhlaza', 'Yellow': 'Phuzi', 'Black': 'Mnyama', 'White': 'Mhlophe', 'business': 'ibhizinisi', 'company': 'inkampani', 'employee': 'umsebenzi', 'client': 'ikhasimende', 'meeting': 'umhlangano', 'project': 'iphrojekthi', 'market': 'imakethe', 'school': 'isikole', 'house': 'indlu', 'water': 'amanzi'}),
    'gd': ('GD-TO-RW', {'What is': 'Dè th’ ann', 'What does': 'Dè tha', 'mean?': 'a’ ciallachadh?', 'Translate this lesson term': 'Eadar-theangaich teirm na leasan seo', 'Answer': 'Freagairt', 'Question': 'Ceist', 'Practice': 'Cleachdadh', 'Learn': 'Ionnsaich', 'Listen': 'Èist', 'Read': 'Leugh', 'Write': 'Sgrìobh', 'Speak': 'Bruidhinn', 'Choose': 'Tagh', 'Correct': 'Ceart', 'Word': 'Facal', 'Sentence': 'Seantans', 'Greetings': 'Beannachdan', 'Numbers': 'Àireamhan', 'Family': 'Teaghlach', 'Colors': 'Dathan', 'Animals': 'Beathaichean', 'Food': 'Biadh', 'Weather': 'Aimsir', 'House': 'Taigh', 'Travel': 'Siubhal', 'Shopping': 'Bùthan', 'Restaurant': 'Taigh-bìdh', 'Directions': 'Stiùiridhean', 'Emotions': 'Faireachdainnean', 'Culture': 'Cultar', 'Business': 'Gnìomhachas', 'Dog': 'Cù', 'Cat': 'Cat', 'Cow': 'Bò', 'Goat': 'Gobhar', 'Sheep': 'Caora', 'Bird': 'Eun', 'Lion': 'Leòmhann', 'Fish': 'Iasg', 'Water': 'Uisge', 'Left': 'Clì', 'Right': 'Deas', 'Straight': 'Dìreach', 'Tomorrow': 'A-màireach', 'Yesterday': 'An-dè', 'Hello': 'Halò', 'Goodbye': 'Mar sin leat', 'Happy': 'Toilichte', 'Sad': 'Brònach', 'Red': 'Dearg', 'Blue': 'Gorm', 'Green': 'Uaine', 'Yellow': 'Buidhe', 'Black': 'Dubh', 'White': 'Geal', 'business': 'gnìomhachas', 'company': 'companaidh', 'employee': 'neach-obrach', 'client': 'cliant', 'meeting': 'coinneamh', 'project': 'pròiseact', 'market': 'margaidh', 'school': 'sgoil', 'house': 'taigh', 'water': 'uisge'}),
    'la': ('LA-TO-RW', {'What is': 'Quid est', 'What does': 'Quid significat', 'mean?': 'significat?', 'Translate this lesson term': 'Verte hoc vocabulum lectionis', 'Answer': 'Responsum', 'Question': 'Quaestio', 'Practice': 'Exercitium', 'Learn': 'Disce', 'Listen': 'Audi', 'Read': 'Lege', 'Write': 'Scribe', 'Speak': 'Loquere', 'Choose': 'Elige', 'Correct': 'Rectum', 'Word': 'Verbum', 'Sentence': 'Sententia', 'Greetings': 'Salutatio', 'Numbers': 'Numeri', 'Family': 'Familia', 'Colors': 'Colores', 'Animals': 'Animalia', 'Food': 'Cibus', 'Weather': 'Tempestas', 'House': 'Domus', 'Travel': 'Itinera', 'Shopping': 'Emptio', 'Restaurant': 'Caupona', 'Directions': 'Itinera', 'Emotions': 'Affectus', 'Culture': 'Cultura', 'Business': 'Negotium', 'Dog': 'Canis', 'Cat': 'Feles', 'Cow': 'Vacca', 'Goat': 'Capra', 'Sheep': 'Ovis', 'Bird': 'Avis', 'Lion': 'Leo', 'Fish': 'Piscis', 'Water': 'Aqua', 'Left': 'Sinistra', 'Right': 'Dextera', 'Straight': 'Recta', 'Tomorrow': 'Cras', 'Yesterday': 'Heri', 'Hello': 'Salve', 'Goodbye': 'Vale', 'Happy': 'Laetus', 'Sad': 'Tristis', 'Red': 'Ruber', 'Blue': 'Caeruleus', 'Green': 'Viridis', 'Yellow': 'Flavus', 'Black': 'Niger', 'White': 'Albus', 'business': 'negotium', 'company': 'societas', 'employee': 'operarius', 'client': 'cliens', 'meeting': 'conventus', 'project': 'proiectum', 'market': 'forum', 'school': 'schola', 'house': 'domus', 'water': 'aqua'}),
}

EXTRA_TRANSLATIONS = {
    'da': {
        'How do you ask': 'Hvordan spørger man',
        'What do you think?': 'Hvad tænker du?',
        'Can you explain why?': 'Kan du forklare hvorfor?',
        'I agree with you': 'Jeg er enig med dig',
        'I disagree': 'Jeg er uenig',
        'In my opinion...': 'Min mening er...',
        'I think that...': 'Jeg synes, at...',
        'I have evidence that...': 'Jeg har beviser, der viser...',
        'That is a strong argument': 'Det er et stærkt argument',
        'I need more facts': 'Jeg har brug for flere fakta',
        "Let's discuss this point": 'Lad os diskutere dette punkt',
        'What did the speaker provide?': 'Hvad gav taleren?',
        'important according to the speaker': 'vigtigt ifølge taleren',
        'Speaker A:': 'Taler A:',
        'Speaker B:': 'Taler B:',
        'Listen and type the word': 'Lyt og skriv ordet',
        'Listen and type the phrase': 'Lyt og skriv sætningen',
        'Listen and type the question': 'Lyt og skriv spørgsmålet',
        'Listen and type the response': 'Lyt og skriv svaret',
        'Listen and choose the correct word': 'Lyt og vælg det rigtige ord',
        'Listen and choose the correct phrase': 'Lyt og vælg den rigtige sætning',
        'Listen and choose the correct meaning': 'Lyt og vælg den rigtige betydning',
        'Listen and choose the correct term': 'Lyt og vælg det rigtige begreb',
        'Listen and choose the correct direction': 'Lyt og vælg den rigtige retning',
        'Build the Kinyarwanda sentence for': 'Byg den kinyarwanda sætning for',
        'Build the Kinyarwanda phrase for': 'Byg den kinyarwanda frase for',
        'Build the Kinyarwanda question for': 'Byg det kinyarwanda spørgsmål for',
        'Build the Kinyarwanda question': 'Byg det kinyarwanda spørgsmål',
        'Build the Kinyarwanda expression for': 'Byg den kinyarwanda udtryksform for',
        'Build the Kinyarwanda greeting for': 'Byg den kinyarwanda hilsen for',
        'Build the Kinyarwanda request': 'Byg den kinyarwanda anmodning',
        'Build the Kinyarwanda invitation for': 'Byg den kinyarwanda invitation for',
        'Arrange these words to form a correct sentence': 'Arranger disse ord for at danne en korrekt sætning',
        'Arrange these words to form a correct phrase': 'Arranger disse ord for at danne en korrekt frase',
        'Say this word': 'Sig dette ord',
        'Say this phrase': 'Sig denne sætning',
        'Can you repeat that?': 'Kan du gentage det?',
        "Can you correct me if I'm wrong?": 'Kan du rette mig, hvis jeg tager fejl?',
        "I don't understand": 'Jeg forstår det ikke',
        'Please': 'Vær så venlig',
        'slowly': 'langsomt',
        'Could you explain that?': 'Kunne du forklare det?',
        'I want to practice speaking': 'Jeg vil øve at tale',
        'Repeat after me': 'Gentag efter mig',
        'What day is it today?': 'Hvad er det for en dag i dag?',
        'What time is it?': 'Hvad er klokken?',
    },
    'cs': {
        'How do you ask': 'Jak se ptáte',
        'What do you think?': 'Co si myslíte?',
        'Can you explain why?': 'Můžete mi vysvětlit proč?',
        'I agree with you': 'Souhlasím s vámi',
        'I disagree': 'Nesouhlasím',
        'In my opinion...': 'Můj názor je...',
        'I think that...': 'Myslím, že...',
        'I have evidence that...': 'Mám důkazy, které ukazují, že...',
        'That is a strong argument': 'To je silný argument',
        'I need more facts': 'Potřebuji více faktů',
        "Let's discuss this point": 'Pojďme diskutovat o tomto bodu',
        'What did the speaker provide?': 'Co mluvčí poskytl?',
        'important according to the speaker': 'důležité podle mluvčího',
        'Speaker A:': 'Mluvčí A:',
        'Speaker B:': 'Mluvčí B:',
        'Listen and type the word': 'Poslechněte si a napište slovo',
        'Listen and type the phrase': 'Poslechněte si a napište větu',
        'Listen and type the question': 'Poslechněte si a napište otázku',
        'Listen and type the response': 'Poslechněte si a napište odpověď',
        'Listen and choose the correct word': 'Poslechněte si a vyberte správné slovo',
        'Listen and choose the correct phrase': 'Poslechněte si a vyberte správnou větu',
        'Listen and choose the correct meaning': 'Poslechněte si a vyberte správný význam',
        'Listen and choose the correct term': 'Poslechněte si a vyberte správný termín',
        'Listen and choose the correct direction': 'Poslechněte si a vyberte správný směr',
        'Build the Kinyarwanda sentence for': 'Vytvořte kinyarwandskou větu pro',
        'Build the Kinyarwanda phrase for': 'Vytvořte kinyarwandskou frázi pro',
        'Build the Kinyarwanda question for': 'Vytvořte kinyarwandskou otázku pro',
        'Build the Kinyarwanda question': 'Vytvořte kinyarwandskou otázku',
        'Build the Kinyarwanda expression for': 'Vytvořte kinyarwandský výraz pro',
        'Build the Kinyarwanda greeting for': 'Vytvořte kinyarwandské pozdravení pro',
        'Build the Kinyarwanda request': 'Vytvořte kinyarwandskou žádost',
        'Build the Kinyarwanda invitation for': 'Vytvořte kinyarwandské pozvání pro',
        'Arrange these words to form a correct sentence': 'Uspořádejte tato slova do správné věty',
        'Arrange these words to form a correct phrase': 'Uspořádejte tato slova do správné fráze',
        'Say this word': 'Řekněte toto slovo',
        'Say this phrase': 'Řekněte tuto větu',
        'Can you repeat that?': 'Můžete to zopakovat?',
        "Can you correct me if I'm wrong?": 'Můžete mě opravit, pokud se mýlím?',
        "I don't understand": 'Nerozumím',
        'Please': 'Prosím',
        'slowly': 'pomalu',
        'Could you explain that?': 'Můžete to vysvětlit?',
        'I want to practice speaking': 'Chci si procvičit mluvení',
        'Repeat after me': 'Opakujte po mně',
        'What day is it today?': 'Jaký je dnes den?',
        'What time is it?': 'Kolik je hodin?',
    }
}

for code, (folder, values) in LANGUAGES.items():
    for source, target_by_language in BASE.items():
        values[source] = target_by_language[code]
    values.update(EXTRA_TRANSLATIONS.get(code, {}))

PROTECTED = {'id', 'type', 'xp_reward', 'difficulty', 'time_limit_seconds', 'topic', 'audio', 'audio_url', 'audio_file', 'tts_audio', 'image', 'image_url', 'link'}


def should_protect(value, key):
    return key in PROTECTED and not isinstance(value, str)


def translate_text(value: str, lang: str) -> str:
    if not isinstance(value, str) or value.startswith(('http://', 'https://')):
        return value
    result = value
    for source, target in sorted(LANGUAGES[lang][1].items(), key=lambda item: len(item[0]), reverse=True):
        result = re.sub(rf'(?<!\w){re.escape(source)}(?!\w)', target, result, flags=re.IGNORECASE)
    return result


def walk(value, key='', lang='da'):
    if isinstance(value, dict):
        return {child_key: copy.deepcopy(child) if should_protect(child, child_key) else walk(child, child_key, lang) for child_key, child in value.items()}
    if isinstance(value, list):
        return [walk(item, key, lang) for item in value]
    if isinstance(value, str):
        return translate_text(value, lang)
    return value


def generate(lang: str):
    folder = Path('content') / LANGUAGES[lang][0]
    for source_file in sorted(SOURCE_ROOT.rglob('*.yaml')):
        output_file = folder / source_file.relative_to(SOURCE_ROOT)
        output_file.parent.mkdir(parents=True, exist_ok=True)
        source = yaml.safe_load(source_file.read_text(encoding='utf-8').replace("\\'", "'")) or {}
        output_file.write_text(yaml.safe_dump(walk(source, lang=lang), allow_unicode=True, sort_keys=False, width=120), encoding='utf-8')
        print(f'Wrote {output_file}')


def validate(lang: str):
    source_files = sorted(SOURCE_ROOT.rglob('*.yaml'))
    output_files = sorted((Path('content') / LANGUAGES[lang][0]).rglob('*.yaml'))
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
