
const DOM = {
    chatContainer: document.getElementById('chat-container'),
    userInput: document.getElementById('user-input'),
    sendBtn: document.getElementById('send-btn'),
    settingsBtn: document.getElementById('settings-btn'),
    apiModal: document.getElementById('api-modal'),
    closeModalBtn: document.querySelector('.close-modal'),
    saveApiBtn: document.getElementById('save-api-btn'),
    apiKeyInput: document.getElementById('api-key-input'),
    toggleVisibilityBtn: document.getElementById('toggle-visibility'),
    newChatBtn: document.getElementById('new-chat-btn'),
    suggestionChips: document.querySelectorAll('.suggestion-chip'),
    menuToggle: document.getElementById('menu-toggle'),
    sidebar: document.querySelector('.sidebar')
};

// State
let apiKey = localStorage.getItem('gemini_api_key') || '';
let chatHistory = [];
let isGenerating = false;

// System Prompt for Sundanese Context
const SYSTEM_PROMPT = `
You are a helpful AI assistant named "SundaAI" (or Batara Guru).
IMPORTANT: You MUST speak ONLY in Sundanese (Basa Sunda).
- Do NOT use Indonesian (Bahasa Indonesia) or English unless explicitly asked to translate specific words.
- Your tone should be polite, friendly, and respectful (Sunda lemes/sopan when appropriate, or loma if the user is casual).
- If the user asks in another language, politely explain in Sundanese that you only understand and speak Sundanese.
- Use markdown for formatting.
- Be helpful, concise, and accurate.
`;

// Initialize
function init() {
    if (!apiKey) {
        showModal();
    }
    
    // Auto-resize textarea
    DOM.userInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
        if(this.value.trim().length > 0) {
            DOM.sendBtn.removeAttribute('disabled');
        } else {
            DOM.sendBtn.setAttribute('disabled', 'true');
        }
    });

    // Event Listeners
    DOM.sendBtn.addEventListener('click', handleSendMessage);
    DOM.userInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            handleSendMessage();
        }
    });

    DOM.settingsBtn.addEventListener('click', showModal);
    DOM.closeModalBtn.addEventListener('click', hideModal);
    DOM.saveApiBtn.addEventListener('click', saveApiKey);
    
    DOM.toggleVisibilityBtn.addEventListener('click', () => {
        const type = DOM.apiKeyInput.type === 'password' ? 'text' : 'password';
        DOM.apiKeyInput.type = type;
        DOM.toggleVisibilityBtn.innerHTML = type === 'password' ? '<i class="fa-regular fa-eye"></i>' : '<i class="fa-regular fa-eye-slash"></i>';
    });

    DOM.newChatBtn.addEventListener('click', () => {
        // Clear chat UI except welcome message
        DOM.chatContainer.innerHTML = '';
        appendMessage('ai', `<h2>Sampurasun! 🙏</h2><p>Simkuring téh asistén AI anu khusus nyarios Basa Sunda. Aya nu tiasa dibantos dinten ieu?</p>`, false);
        chatHistory = [];
    });

    DOM.suggestionChips.forEach(chip => {
        chip.addEventListener('click', () => {
            DOM.userInput.value = chip.innerText;
            handleSendMessage();
            DOM.userInput.style.height = 'auto';
        });
    });
    
    // Mobile Menu
    DOM.menuToggle.addEventListener('click', () => {
        DOM.sidebar.classList.toggle('show');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 768 && 
            !DOM.sidebar.contains(e.target) && 
            !DOM.menuToggle.contains(e.target) &&
            DOM.sidebar.classList.contains('show')) {
            DOM.sidebar.classList.remove('show');
        }
    });
}

function showModal() {
    DOM.apiModal.classList.add('active');
    DOM.apiKeyInput.value = apiKey;
}

function hideModal() {
    DOM.apiModal.classList.remove('active');
}

function saveApiKey() {
    const key = DOM.apiKeyInput.value.trim();
    if (key) {
        apiKey = key;
        localStorage.setItem('gemini_api_key', apiKey);
        hideModal();
        alert('API Key disimpen! Hatur nuhun.');
    } else {
        alert('Punten lebetkeun API Key heula.');
    }
}

async function handleSendMessage() {
    const text = DOM.userInput.value.trim();
    if (!text || isGenerating) return;

    if (!apiKey) {
        showModal();
        return;
    }

    // Add User Message
    appendMessage('user', text);
    DOM.userInput.value = '';
    DOM.userInput.style.height = 'auto';
    DOM.sendBtn.setAttribute('disabled', 'true');
    
    // Create Loading Bubble
    const loadingId = 'loading-' + Date.now();
    appendLoadingMessage(loadingId);
    isGenerating = true;

    try {
        const responseText = await callGeminiAPI(text);
        removeMessage(loadingId);
        appendMessage('ai', responseText);
    } catch (error) {
        removeMessage(loadingId);
        appendMessage('ai', `**Punten, aya kasalahan:** ${error.message}. Cobi parios deui API Key anjeun atanapi sambungan internét.`);
        console.error(error);
    } finally {
        isGenerating = false;
    }
}

function appendMessage(role, content, isMarkdown = true) {
    const msgDiv = document.createElement('div');
    msgDiv.className = `message ${role === 'user' ? 'user-message' : 'ai-message'}`;
    
    const contentDiv = document.createElement('div');
    contentDiv.className = 'message-content prose';
    
    if (role === 'ai' && isMarkdown) {
        contentDiv.innerHTML = marked.parse(content);
        // Highlight code blocks
        contentDiv.querySelectorAll('pre code').forEach((block) => {
            hljs.highlightElement(block);
        });
    } else {
        contentDiv.innerHTML = content; // If user content, maybe sanitize, but here simplistic
        if(role === 'user') contentDiv.textContent = content; // Safer for user input
    }

    msgDiv.appendChild(contentDiv);
    DOM.chatContainer.appendChild(msgDiv);
    DOM.chatContainer.scrollTop = DOM.chatContainer.scrollHeight;
    
    // Save to history context (limit context size if needed, but for now simple)
    chatHistory.push({
        role: role === 'user' ? 'user' : 'model',
        parts: [{ text: content }]
    });
}

function appendLoadingMessage(id) {
    const msgDiv = document.createElement('div');
    msgDiv.id = id;
    msgDiv.className = 'message ai-message';
    msgDiv.innerHTML = `
        <div class="message-content">
            <div class="loading-dots">
                <span></span><span></span><span></span>
            </div>
        </div>
    `;
    DOM.chatContainer.appendChild(msgDiv);
    DOM.chatContainer.scrollTop = DOM.chatContainer.scrollHeight;
}

function removeMessage(id) {
    const el = document.getElementById(id);
    if (el) el.remove();
}

async function callGeminiAPI(userMessage) {
    const url = `https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=${apiKey}`;
    
    // Construct payload with history
    // Note: Gemini API format requires "parts": [{"text": "..."}]
    
    const contents = [
        {
            role: 'user',
            parts: [{ text: SYSTEM_PROMPT }] // Inserting system prompt as first user message or proper system instruction if supported. 
            // Gemini 1.5/2.0 supports system_instruction, but via REST sometimes 'system' role or just initial prompt works best.
            // Let's use 'system_instruction' field if creating a new chat, but 'generateContent' REST API usually takes 'contents' and optional 'systemInstruction'.
        },
        ...chatHistory, // Previous history
        {
            role: 'user',
            parts: [{ text: userMessage }]
        }
    ];

    // Better approach for System Prompt in Gemini 1.5+ REST API: use systemInstruction field
    const payload = {
        systemInstruction: {
            parts: [{ text: SYSTEM_PROMPT }]
        },
        contents: [
            ...chatHistory,
            {
                role: 'user',
                parts: [{ text: userMessage }]
            }
        ],
        generationConfig: {
            temperature: 0.7,
            topK: 40,
            topP: 0.95,
            maxOutputTokens: 2048,
        }
    };

    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    });

    if (!response.ok) {
        const errData = await response.json();
        throw new Error(errData.error?.message || 'Failed to fetch from Gemini');
    }

    const data = await response.json();
    
    if (data.candidates && data.candidates.length > 0) {
        return data.candidates[0].content.parts[0].text;
    } else {
        throw new Error('No content returned');
    }
}

// Start
init();
