// Lesson Player with 7 Exercise Types
class LessonPlayer {
    constructor(lessonData, lang, level) {
        // Initialize properties
        this.lessonData = lessonData;
        this.lang = lang;
        this.level = level;
        this.currentExerciseIndex = 0;
        this.exercises = lessonData.exercises || [];
        this.totalExercises = this.exercises.length;
        this.completedExercises = 0;
        this.score = 0;
        this.totalXP = 0;
        this.synth = window.speechSynthesis;
        this.voices = [];
        this.matchingSelections = { left: null, right: null };
        
        // Calculate total XP
        this.calculateTotalXP();
        
        // Load voices if available
        this.loadVoices();
        
        // Show first exercise
        this.showExercise();
    }
    
    loadVoices() {
        const load = () => {
            this.voices = this.synth.getVoices();
            console.log('Voices loaded:', this.voices.length);
            
            // Map lesson language to voice language codes
            const langMap = {
                'en-to-sw': 'sw',      // English to Kiswahili - use Swahili voice
                'en-to-rw': 'rw',      // English to Kinyarwanda - use Kinyarwanda voice
                'en-sw:en': 'sw',      // New pair format
                'en-sw:sw': 'sw',
                'fr-sw:sw': 'sw',
                'sw-to-en': 'sw',      // Kiswahili to English - use Swahili voice
                'sw-to-fr': 'sw',
                'en-rw:en': 'en',
                'en-rw:rw': 'rw',
                'fr-rw:fr': 'fr',
                'fr-rw:rw': 'rw',
                'fr-to-rw': 'rw',
                'rw-to-en': 'rw',
                'fr-to-sw': 'fr',
                'sw': 'sw',            // Direct language code
                'rw': 'rw',
                'fr': 'fr',
                'en': 'en'
            };
            
            // Determine target language from lesson language or pair
            let targetLang = langMap[this.lang] || langMap[this.lang.replace(/-/g, '_')] || 'en';
            
            // First, try to find a voice matching the target language with preference for female
            this.preferredVoice = this.voices.find(voice => {
                const langMatch = voice.lang.startsWith(targetLang);
                const isFemale = voice.name.toLowerCase().includes('female') ||
                    voice.name.toLowerCase().includes('zira') ||
                    voice.name.toLowerCase().includes('hazel') ||
                    voice.name.toLowerCase().includes('samantha') ||
                    voice.name.toLowerCase().includes('susan') ||
                    voice.name.toLowerCase().includes('karen') ||
                    voice.name.toLowerCase().includes('anna') ||
                    voice.name.toLowerCase().includes('victoria') ||
                    !voice.name.toLowerCase().includes('male');
                return langMatch && isFemale;
            });
            
            // Fallback: any voice matching target language
            if (!this.preferredVoice) {
                this.preferredVoice = this.voices.find(voice => 
                    voice.lang.startsWith(targetLang)
                );
            }
            
            // Fallback: use any female voice
            if (!this.preferredVoice) {
                this.preferredVoice = this.voices.find(voice => 
                    voice.name.toLowerCase().includes('female') ||
                    voice.name.toLowerCase().includes('zira') ||
                    voice.name.toLowerCase().includes('hazel') ||
                    voice.name.toLowerCase().includes('samantha') ||
                    voice.name.toLowerCase().includes('susan') ||
                    voice.name.toLowerCase().includes('karen') ||
                    voice.name.toLowerCase().includes('anna') ||
                    voice.name.toLowerCase().includes('victoria') ||
                    voice.name.toLowerCase().includes('alex') ||
                    voice.name.toLowerCase().includes('google us english')
                );
            }
            
            // Final fallback: first available voice
            if (!this.preferredVoice && this.voices.length > 0) {
                this.preferredVoice = this.voices[0];
            }
            
            console.log('Target language:', targetLang, 'Preferred voice:', this.preferredVoice?.name, 'Lang:', this.preferredVoice?.lang);
        };
        
        if (this.synth.getVoices().length > 0) {
            load();
        } else if (this.synth.onvoiceschanged !== undefined) {
            this.synth.onvoiceschanged = load;
        }
    }
    
    calculateTotalXP() {
        this.totalXP = this.exercises.reduce((sum, ex) => sum + (ex.xp_reward || 10), 0);
    }
    
    showExercise() {
        const container = document.getElementById('exercise-container');
        if (!container) return;
        
        const exercise = this.exercises[this.currentExerciseIndex];
        
        if (!exercise) {
            this.showCompletionScreen();
            return;
        }
        
        let progress = ((this.currentExerciseIndex) / this.totalExercises) * 100;
        
        let html = `
            <div class="bg-white rounded-2xl shadow-lg p-8 exercise-card">
                <!-- Progress bar -->
                <div class="mb-6">
                    <div class="flex justify-between text-sm mb-2">
                        <span>Exercise ${this.currentExerciseIndex + 1} of ${this.totalExercises}</span>
                        <span class="text-yellow-600 font-bold">+${exercise.xp_reward || 10} XP</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full transition-all duration-500" style="width: ${progress}%"></div>
                    </div>
                </div>
                
                <!-- Exercise question -->
                <h2 class="text-2xl font-bold mb-6">${exercise.question || 'What is the answer?'}</h2>
        `;
        
        // Render based on exercise type
        switch(exercise.type) {
            case 'multiple_choice':
                html += this.renderMultipleChoice(exercise);
                break;
            case 'translation':
                html += this.renderTranslation(exercise);
                break;
            case 'typing':
                html += this.renderTyping(exercise);
                break;
            case 'listening':
                html += this.renderListening(exercise);
                break;
            case 'matching':
                html += this.renderMatching(exercise);
                break;
            case 'sentence_building':
                html += this.renderSentenceBuilding(exercise);
                break;
            case 'flashcards':
                html += this.renderFlashcards(exercise);
                break;
            default:
                html += this.renderDefault(exercise);
        }
        
        html += `</div>`;
        container.innerHTML = html;
        
        // Initialize special interactions
        if (exercise.type === 'matching') {
            setTimeout(() => this.initMatchingGame(exercise), 100);
        } else if (exercise.type === 'flashcards') {
            setTimeout(() => this.initFlashcards(exercise), 100);
        }
    }
    
    // 1. MULTIPLE CHOICE
    renderMultipleChoice(exercise) {
        let options = exercise.options || [];
        let html = '<div class="space-y-3">';
        options.forEach((option, index) => {
            html += `
                <label class="block p-4 border-2 rounded-xl hover:bg-gray-50 cursor-pointer transition">
                    <input type="radio" name="mc-option" value="${option}" class="mr-3">
                    ${option}
                    <button onclick="event.preventDefault(); window.player.speak('${option}')" 
                            class="ml-2 text-purple-600 hover:text-purple-800 text-sm">
                        🔊
                    </button>
                </label>
            `;
        });
        html += `
            <button onclick="window.player.checkMultipleChoice()" 
                    class="w-full bg-green-600 text-white px-6 py-4 rounded-xl hover:bg-green-700 transition mt-4">
                Check Answer
            </button>
        </div>`;
        return html;
    }
    
    checkMultipleChoice() {
        const exercise = this.exercises[this.currentExerciseIndex];
        const selected = document.querySelector('input[name="mc-option"]:checked');
        
        if (!selected) {
            alert('Please select an answer');
            return;
        }
        
        const userAnswer = selected.value;
        let correctAnswer = exercise.answer || '';
        
        // Handle if answer is index
        if (typeof correctAnswer === 'number') {
            correctAnswer = exercise.options[correctAnswer] || '';
        }
        
        if (this.normalizeString(userAnswer) === this.normalizeString(correctAnswer)) {
            this.handleCorrectAnswer();
        } else {
            alert(`❌ Incorrect. The correct answer is: ${correctAnswer}`);
        }
    }
    
    // 2. TRANSLATION
    renderTranslation(exercise) {
        return `
            <div class="space-y-4">
                <p class="text-gray-600">Type the translation:</p>
                <input type="text" id="answer-input" 
                       class="w-full p-4 border-2 rounded-xl focus:ring-2 focus:ring-blue-500" 
                       placeholder="Type your answer here..."
                       onkeypress="if(event.key === 'Enter') window.player.checkTranslation()">
                <div class="flex space-x-4">
                    <button onclick="window.player.speak('${exercise.question}')" 
                            class="bg-purple-600 text-white px-6 py-3 rounded-xl hover:bg-purple-700 transition flex-1">
                        🔊 Listen
                    </button>
                    <button onclick="window.player.checkTranslation()" 
                            class="bg-green-600 text-white px-6 py-3 rounded-xl hover:bg-green-700 transition flex-1">
                        Check Answer
                    </button>
                </div>
            </div>
        `;
    }
    
    checkTranslation() {
        const exercise = this.exercises[this.currentExerciseIndex];
        const userAnswer = document.getElementById('answer-input')?.value || '';
        
        if (!userAnswer.trim()) {
            alert('Please enter an answer');
            return;
        }
        
        const correctAnswer = exercise.answer || '';
        
        if (this.normalizeString(userAnswer) === this.normalizeString(correctAnswer)) {
            this.handleCorrectAnswer();
        } else {
            alert(`❌ Incorrect.\n\nYour answer: ${userAnswer}\nCorrect: ${correctAnswer}`);
            document.getElementById('answer-input').value = '';
            document.getElementById('answer-input').focus();
        }
    }
    
    // 3. TYPING / TEXT INPUT
    renderTyping(exercise) {
        return `
            <div class="space-y-4">
                <p class="text-gray-600">Type the correct word:</p>
                <input type="text" id="answer-input" 
                       class="w-full p-4 border-2 rounded-xl focus:ring-2 focus:ring-blue-500" 
                       placeholder="Type here..."
                       onkeypress="if(event.key === 'Enter') window.player.checkTyping()">
                <button onclick="window.player.checkTyping()" 
                        class="w-full bg-green-600 text-white px-6 py-4 rounded-xl hover:bg-green-700 transition">
                    Check Answer
                </button>
            </div>
        `;
    }
    
    checkTyping() {
        const exercise = this.exercises[this.currentExerciseIndex];
        const userAnswer = document.getElementById('answer-input')?.value || '';
        
        if (!userAnswer.trim()) {
            alert('Please enter an answer');
            return;
        }
        
        const correctAnswer = exercise.answer || '';
        
        if (this.normalizeString(userAnswer) === this.normalizeString(correctAnswer)) {
            this.handleCorrectAnswer();
        } else {
            alert(`❌ Incorrect.\n\nYour answer: ${userAnswer}\nCorrect: ${correctAnswer}`);
            document.getElementById('answer-input').value = '';
            document.getElementById('answer-input').focus();
        }
    }
    
    // 4. LISTENING
    renderListening(exercise) {
        return `
            <div class="space-y-4 text-center">
                <button onclick="window.player.speak('${exercise.tts_text || exercise.question}')" 
                        class="bg-purple-600 text-white px-8 py-4 rounded-xl hover:bg-purple-700 transition text-lg">
                    🔊 Click to Listen
                </button>
                <p class="text-gray-600 mt-4">Type what you heard:</p>
                <input type="text" id="answer-input" 
                       class="w-full p-4 border-2 rounded-xl focus:ring-2 focus:ring-blue-500" 
                       placeholder="Type here..."
                       onkeypress="if(event.key === 'Enter') window.player.checkListening()">
                <button onclick="window.player.checkListening()" 
                        class="w-full bg-green-600 text-white px-6 py-4 rounded-xl hover:bg-green-700 transition">
                    Check Answer
                </button>
                <button onclick="window.player.speak('${exercise.tts_text || exercise.question}')" 
                        class="text-purple-600 hover:underline text-sm">
                    🔄 Listen Again
                </button>
            </div>
        `;
    }
    
    checkListening() {
        const exercise = this.exercises[this.currentExerciseIndex];
        const userAnswer = document.getElementById('answer-input')?.value || '';
        
        if (!userAnswer.trim()) {
            alert('Please enter an answer');
            return;
        }
        
        const correctAnswer = exercise.answer || '';
        
        if (this.normalizeString(userAnswer) === this.normalizeString(correctAnswer)) {
            this.handleCorrectAnswer();
        } else {
            alert(`❌ Not quite right.\n\nYou typed: ${userAnswer}\nCorrect: ${correctAnswer}`);
            document.getElementById('answer-input').value = '';
            document.getElementById('answer-input').focus();
        }
    }
    
    // 5. MATCHING
    renderMatching(exercise) {
        return `
            <div class="space-y-4">
                <p class="text-gray-600">Match the items by clicking on them:</p>
                <div id="matching-game" class="grid grid-cols-2 gap-4"></div>
                <button onclick="window.player.checkMatching()" 
                        class="w-full bg-green-600 text-white px-6 py-4 rounded-xl hover:bg-green-700 transition">
                    Check Matches
                </button>
            </div>
        `;
    }
    
    initMatchingGame(exercise) {
        const container = document.getElementById('matching-game');
        if (!container) return;
        
        let pairs = exercise.pairs || [];
        container.innerHTML = '';
        
        // Create left column (terms)
        const leftCol = document.createElement('div');
        leftCol.className = 'space-y-2';
        
        // Create right column (definitions) - shuffled
        const rightCol = document.createElement('div');
        rightCol.className = 'space-y-2';
        
        // Shuffle right items
        const rightItems = [...pairs].sort(() => Math.random() - 0.5);
        
        pairs.forEach((pair, index) => {
            const leftItem = this.createMatchingItem(pair.left, index, 'left');
            leftCol.appendChild(leftItem);
        });
        
        rightItems.forEach((pair) => {
            const rightItem = this.createMatchingItem(pair.right, pairs.indexOf(pair), 'right');
            rightCol.appendChild(rightItem);
        });
        
        container.appendChild(leftCol);
        container.appendChild(rightCol);
        
        // Reset selections
        this.matchingSelections = { left: null, right: null };
    }
    
    createMatchingItem(text, pairId, side) {
        const div = document.createElement('div');
        div.className = 'matching-item p-4 bg-blue-100 rounded-xl cursor-pointer hover:bg-blue-200 transition flex justify-between items-center';
        div.setAttribute('data-pair', pairId);
        div.setAttribute('data-side', side);
        div.setAttribute('data-matched', 'false');
        
        const textSpan = document.createElement('span');
        textSpan.textContent = text;
        
        const speakBtn = document.createElement('button');
        speakBtn.innerHTML = '🔊';
        speakBtn.className = 'ml-2 text-purple-600 hover:text-purple-800 text-sm';
        speakBtn.onclick = (e) => {
            e.stopPropagation();
            this.speak(text);
        };
        
        div.appendChild(textSpan);
        div.appendChild(speakBtn);
        
        div.addEventListener('click', () => this.handleMatchingClick(div));
        
        return div;
    }
    
    handleMatchingClick(element) {
        const side = element.getAttribute('data-side');
        const pairId = element.getAttribute('data-pair');
        
        // Clear previous selection on same side
        if (this.matchingSelections[side]) {
            this.matchingSelections[side].classList.remove('bg-green-200', 'border-2', 'border-green-500');
        }
        
        // Select this element
        element.classList.add('bg-green-200', 'border-2', 'border-green-500');
        this.matchingSelections[side] = element;
        
        // Check if we have both sides selected
        if (this.matchingSelections.left && this.matchingSelections.right) {
            const leftPair = this.matchingSelections.left.getAttribute('data-pair');
            const rightPair = this.matchingSelections.right.getAttribute('data-pair');
            
            if (leftPair === rightPair) {
                // Correct match
                this.matchingSelections.left.classList.add('bg-green-300');
                this.matchingSelections.right.classList.add('bg-green-300');
                this.matchingSelections.left.setAttribute('data-matched', 'true');
                this.matchingSelections.right.setAttribute('data-matched', 'true');
                
                // Disable further clicking on matched items
                this.matchingSelections.left.style.pointerEvents = 'none';
                this.matchingSelections.right.style.pointerEvents = 'none';
                
                // Play success sound or just beep
                this.speak('Correct match!');
            } else {
                // Incorrect match
                this.matchingSelections.left.classList.remove('bg-green-200', 'border-2', 'border-green-500');
                this.matchingSelections.right.classList.remove('bg-green-200', 'border-2', 'border-green-500');
                this.speak('Try again');
            }
            
            // Reset selections
            this.matchingSelections = { left: null, right: null };
        }
    }
    
    checkMatching() {
        const matchedItems = document.querySelectorAll('[data-matched="true"]');
        const totalPairs = document.querySelectorAll('[data-side="left"]').length;
        const matchedPairs = matchedItems.length / 2;
        
        if (matchedPairs === totalPairs) {
            this.handleCorrectAnswer();
        } else {
            if (matchedPairs > 0) {
                alert(`You've matched ${matchedPairs} out of ${totalPairs} pairs. Keep going!`);
            } else {
                alert('Match items by clicking on one from left and one from right');
            }
        }
    }
    
    // 6. SENTENCE BUILDING
    renderSentenceBuilding(exercise) {
        let words = exercise.words || [];
        let shuffled = [...words].sort(() => Math.random() - 0.5);
        
        let html = `
            <div class="space-y-4">
                <p class="text-gray-600">Arrange the words to form a sentence:</p>
                <div class="flex flex-wrap gap-2 p-4 bg-gray-100 rounded-xl min-h-20" id="word-bank">`;
        
        shuffled.forEach(word => {
            html += `<span class="word-chip px-4 py-2 bg-white border-2 rounded-lg cursor-pointer hover:bg-blue-50" 
                          onclick="window.player.addToSentence('${word}')">${word}</span>`;
        });
        
        html += `</div>
                <div class="min-h-20 border-2 border-dashed border-blue-300 rounded-xl p-4 bg-blue-50" id="sentence-area">
                    <p class="text-gray-400 text-center">Click words above to build your sentence</p>
                </div>
                <div class="flex space-x-4">
                    <button onclick="window.player.resetSentence()" 
                            class="bg-gray-500 text-white px-6 py-3 rounded-xl hover:bg-gray-600 transition flex-1">
                        Reset
                    </button>
                    <button onclick="window.player.checkSentence()" 
                            class="bg-green-600 text-white px-6 py-3 rounded-xl hover:bg-green-700 transition flex-1">
                        Check Sentence
                    </button>
                </div>
            </div>
        `;
        return html;
    }
    
    addToSentence(word) {
        const sentenceArea = document.getElementById('sentence-area');
        const wordBank = document.getElementById('word-bank');
        if (!sentenceArea || !wordBank) return;
        
        // Find and remove word from bank
        const wordElements = wordBank.querySelectorAll('.word-chip');
        for (let el of wordElements) {
            if (el.textContent === word) {
                el.remove();
                break;
            }
        }
        
        // Add to sentence area
        const wordSpan = document.createElement('span');
        wordSpan.className = 'word-chip px-4 py-2 bg-blue-200 rounded-lg m-1 cursor-pointer hover:bg-blue-300';
        wordSpan.textContent = word;
        wordSpan.onclick = () => {
            wordSpan.remove();
            // Add back to word bank
            const newChip = document.createElement('span');
            newChip.className = 'word-chip px-4 py-2 bg-white border-2 rounded-lg cursor-pointer hover:bg-blue-50';
            newChip.textContent = word;
            newChip.onclick = () => this.addToSentence(word);
            wordBank.appendChild(newChip);
        };
        
        // Remove placeholder if exists
        if (sentenceArea.children.length === 1 && sentenceArea.children[0].tagName === 'P') {
            sentenceArea.innerHTML = '';
        }
        
        sentenceArea.appendChild(wordSpan);
    }
    
    resetSentence() {
        const exercise = this.exercises[this.currentExerciseIndex];
        this.showExercise(); // Just re-render the whole thing
    }
    
    checkSentence() {
        const exercise = this.exercises[this.currentExerciseIndex];
        const sentenceArea = document.getElementById('sentence-area');
        if (!sentenceArea) return;
        
        const words = Array.from(sentenceArea.children).map(span => span.textContent);
        const userSentence = words.join(' ');
        const correctSentence = exercise.answer || exercise.words.join(' ');
        
        if (this.normalizeString(userSentence) === this.normalizeString(correctSentence)) {
            this.handleCorrectAnswer();
        } else {
            alert(`❌ Not quite right.\n\nYour sentence: ${userSentence}\nCorrect: ${correctSentence}`);
        }
    }
    
    // 7. FLASHCARDS
    renderFlashcards(exercise) {
        let flashcards = exercise.flashcards || exercise.cards || [];
        if (flashcards.length === 0) {
            // Default if no flashcards provided
            flashcards = [
                { front: exercise.question, back: exercise.answer }
            ];
        }
        
        let html = `
            <div class="space-y-6">
                <p class="text-gray-600">Study these flashcards. Click to flip:</p>
                <div id="flashcard-container" class="perspective-1000">
        `;
        
        flashcards.forEach((card, index) => {
            html += `
                <div class="flashcard mb-4 cursor-pointer" onclick="window.player.flipCard(${index})">
                    <div class="flashcard-inner relative w-full h-48 transition-transform duration-500 transform-style-3d ${index === 0 ? '' : 'hidden'}" id="card-${index}">
                        <div class="flashcard-front absolute w-full h-full bg-blue-500 text-white rounded-xl p-6 flex items-center justify-center text-center backface-hidden">
                            ${card.front}
                        </div>
                        <div class="flashcard-back absolute w-full h-full bg-green-500 text-white rounded-xl p-6 flex items-center justify-center text-center backface-hidden rotate-y-180">
                            ${card.back}
                            <button onclick="event.stopPropagation(); window.player.speak('${card.back}')" 
                                    class="absolute bottom-2 right-2 text-white hover:text-gray-200">
                                🔊
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += `
                </div>
                <div class="flex justify-between mt-4">
                    <button onclick="window.player.prevFlashcard()" 
                            class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
                        ← Previous
                    </button>
                    <span id="flashcard-counter" class="text-gray-600">1 / ${flashcards.length}</span>
                    <button onclick="window.player.nextFlashcard()" 
                            class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
                        Next →
                    </button>
                </div>
                <button onclick="window.player.completeFlashcards()" 
                        class="w-full bg-green-600 text-white px-6 py-4 rounded-xl hover:bg-green-700 transition mt-4">
                    I've Studied All Cards
                </button>
            </div>
            <style>
                .perspective-1000 { perspective: 1000px; }
                .transform-style-3d { transform-style: preserve-3d; }
                .backface-hidden { backface-visibility: hidden; }
                .rotate-y-180 { transform: rotateY(180deg); }
                .flashcard-inner { transition: transform 0.6s; }
                .flashcard-inner.flipped { transform: rotateY(180deg); }
            </style>
        `;
        
        this.flashcardIndex = 0;
        this.totalFlashcards = flashcards.length;
        
        return html;
    }
    
    flipCard(index) {
        const card = document.getElementById(`card-${index}`);
        if (card) {
            card.classList.toggle('flipped');
        }
    }
    
    prevFlashcard() {
        if (this.flashcardIndex > 0) {
            document.getElementById(`card-${this.flashcardIndex}`).classList.add('hidden');
            this.flashcardIndex--;
            document.getElementById(`card-${this.flashcardIndex}`).classList.remove('hidden');
            document.getElementById('flashcard-counter').textContent = 
                `${this.flashcardIndex + 1} / ${this.totalFlashcards}`;
        }
    }
    
    nextFlashcard() {
        if (this.flashcardIndex < this.totalFlashcards - 1) {
            document.getElementById(`card-${this.flashcardIndex}`).classList.add('hidden');
            this.flashcardIndex++;
            document.getElementById(`card-${this.flashcardIndex}`).classList.remove('hidden');
            document.getElementById('flashcard-counter').textContent = 
                `${this.flashcardIndex + 1} / ${this.totalFlashcards}`;
        }
    }
    
    completeFlashcards() {
        this.handleCorrectAnswer();
    }
    
    // Default render for unknown types
    renderDefault(exercise) {
        return `
            <div class="space-y-4">
                <p class="text-gray-600">Answer: ${exercise.answer || '???'}</p>
                <input type="text" id="answer-input" 
                       class="w-full p-4 border-2 rounded-xl"
                       onkeypress="if(event.key === 'Enter') window.player.checkAnswer()">
                <button onclick="window.player.checkAnswer()" 
                        class="w-full bg-green-600 text-white px-6 py-4 rounded-xl hover:bg-green-700 transition">
                    Check Answer
                </button>
            </div>
        `;
    }
    
    // Helper: Normalize string for comparison
    normalizeString(str) {
        return String(str)
            .toLowerCase()
            .trim()
            .replace(/[^\w\s]/g, '')
            .replace(/\s+/g, ' ')
            .trim();
    }
    
    // Text-to-speech
    speak(text) {
        if (!text) return;
        
        if (!window.speechSynthesis) {
            console.warn('Speech synthesis not supported');
            return;
        }
        
        // Ensure voices are loaded
        if (this.voices.length === 0) {
            this.voices = this.synth.getVoices();
            if (this.voices.length > 0) {
                this.loadVoices(); // This will set preferredVoice based on lesson language
            }
        }
        
        try {
            window.speechSynthesis.cancel();
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.rate = 0.7; // Slower for clarity
            utterance.pitch = 1.2; // Higher pitch for more female-like sound
            utterance.volume = 1.0;
            
            // Determine language for utterance based on lesson language
            const langMap = {
                'en-to-sw': 'sw',
                'en-to-rw': 'rw',
                'en-sw:en': 'sw',
                'en-sw:sw': 'sw',
                'fr-sw:sw': 'sw',
                'sw-to-en': 'sw',
                'sw-to-fr': 'sw',
                'en-rw:en': 'en',
                'en-rw:rw': 'rw',
                'fr-rw:fr': 'fr',
                'fr-rw:rw': 'rw',
                'fr-to-rw': 'rw',
                'rw-to-en': 'rw',
                'fr-to-sw': 'fr',
                'sw': 'sw',
                'rw': 'rw',
                'fr': 'fr',
                'en': 'en'
            };
            
            const targetLang = langMap[this.lang] || 'en';
            utterance.lang = targetLang;
            
            // Use the pre-selected preferred voice
            if (this.preferredVoice) {
                utterance.voice = this.preferredVoice;
                console.log('Using preferred voice:', this.preferredVoice.name, 'Lang:', this.preferredVoice.lang);
            } else {
                // Fallback if not loaded yet
                const voices = window.speechSynthesis.getVoices();
                const fallbackVoice = voices.find(voice => 
                    voice.lang.startsWith(targetLang)
                ) || voices.find(voice => 
                    voice.name.toLowerCase().includes('female')
                ) || voices[0];
                
                if (fallbackVoice) {
                    utterance.voice = fallbackVoice;
                    console.log('Using fallback voice:', fallbackVoice.name, 'Lang:', fallbackVoice.lang);
                }
            }
            
            window.speechSynthesis.speak(utterance);
        } catch (error) {
            console.error('Speech error:', error);
        }
    }
    
    // Handle correct answer
    handleCorrectAnswer() {
        const exercise = this.exercises[this.currentExerciseIndex];
        const xpEarned = exercise.xp_reward || 10;
        
        this.score++;
        this.completedExercises++;
        
        // Show success message
        alert(`  Correct! You earned ${xpEarned} XP!`);
        
        // Move to next exercise
        this.currentExerciseIndex++;
        
        if (this.currentExerciseIndex < this.totalExercises) {
            this.showExercise();
        } else {
            this.showCompletionScreen();
        }
    }
    
    // Completion screen
    showCompletionScreen() {
        const container = document.getElementById('exercise-container');
        if (!container) return;
        
        const percentage = Math.round((this.score / this.totalExercises) * 100);
        
        container.innerHTML = `
            <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
                <div class="text-8xl mb-6">🎉</div>
                <h2 class="text-3xl font-bold mb-4">Lesson Complete!</h2>
                <p class="text-xl text-gray-600 mb-8">Great job!</p>
                
                <div class="grid grid-cols-2 gap-4 mb-8">
                    <div class="bg-blue-50 p-4 rounded-xl">
                        <div class="text-2xl font-bold text-blue-600">${this.score}/${this.totalExercises}</div>
                        <div class="text-sm text-gray-600">Correct Answers</div>
                    </div>
                    <div class="bg-yellow-50 p-4 rounded-xl">
                        <div class="text-2xl font-bold text-yellow-600">${percentage}%</div>
                        <div class="text-sm text-gray-600">Score</div>
                    </div>
                </div>
                
                <div class="bg-green-50 p-6 rounded-xl mb-8">
                    <div class="text-sm text-gray-600 mb-1">Total XP Earned</div>
                    <div class="text-4xl font-bold text-green-600">+${this.totalXP}</div>
                </div>
                
                <div class="flex space-x-4">
                    <a href="choose-topic.php?lang=${this.lang}&level=${this.level}" 
                       class="flex-1 bg-blue-600 text-white px-6 py-3 rounded-xl hover:bg-blue-700 transition">
                        More Topics
                    </a>
                    <a href="choose-level.php?lang=${this.lang}" 
                       class="flex-1 bg-purple-600 text-white px-6 py-3 rounded-xl hover:bg-purple-700 transition">
                        Change Level
                    </a>
                </div>
            </div>
        `;
    }
}

// Make player available globally
window.LessonPlayer = LessonPlayer;