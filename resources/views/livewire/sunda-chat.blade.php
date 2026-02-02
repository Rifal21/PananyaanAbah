<div class="app-container" x-data="{ sidebarOpen: window.innerWidth > 768 }" @start-generating.window="$wire.generateAiResponse()">
    <!-- Sidebar -->
    <aside class="sidebar" :class="{ 'show': sidebarOpen, 'collapsed': !sidebarOpen }">
        <div class="sidebar-header">
            <div class="logo">
                <i class="fa-solid fa-scroll"></i>
                <div class="logo-text" x-show="sidebarOpen" x-transition>
                    <span>Pananyaan Abah</span>
                    <small class="aksara-sunda">ᮕᮔᮑᮃᮔ᮪ ᮃᮒᮂ</small>
                </div>
            </div>
            <button wire:click="newChat" class="btn-icon" title="Cariosan Anyar">
                <i class="fa-solid fa-feather-pointed"></i>
            </button>
        </div>

        <div class="history-list">
            <div class="history-group">
                <h3 x-show="sidebarOpen">Riwayat Gunem Catur</h3>
                @foreach ($sessions as $session)
                    <div class="history-item-wrapper {{ $currentSessionId === $session['id'] ? 'active' : '' }}">
                        <button wire:click="loadChat('{{ $session['id'] }}')" @click="startHistoryLoad()"
                            class="history-btn" title="{{ $session['title'] }}">
                            <i class="fa-solid fa-scroll"></i>
                            <span x-show="sidebarOpen"
                                style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{ $session['title'] }}
                            </span>
                        </button>
                        <button x-show="sidebarOpen" wire:click="deleteChat('{{ $session['id'] }}')"
                            wire:confirm="Naha anjeun yakin badé ngahapus ieu cariosan?" class="delete-btn"
                            title="Hapus">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="sidebar-footer">
            <button wire:click="$set('showApiKeyModal', true)" class="user-profile">
                <div class="avatar">
                    <i class="fa-solid fa-user-gear"></i>
                </div>
                <span x-show="sidebarOpen">Setélan Konci (API)</span>
            </button>
        </div>
    </aside>

    <!-- Mobile Sidebar Overlay -->
    <div class="sidebar-overlay" x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity></div>

    <!-- Main Chat Area -->
    <main class="chat-area">
        <header class="mobile-header" style="display: flex;">
            <button @click="sidebarOpen = !sidebarOpen" class="btn-icon">
                <i class="fa-solid" :class="sidebarOpen ? 'fa-bars-staggered' : 'fa-bars'"></i>
            </button>
            <div class="brand-group">
                <span class="brand">Pananyaan Abah</span>
                <span class="aksara-sunda mobile-aksara">ᮕᮔᮑᮃᮔ᮪ ᮃᮒᮂ</span>
            </div>
        </header>

        <div id="chat-container" class="messages-container">
            @foreach ($messages as $msg)
                <div wire:key="{{ $msg['id'] }}"
                    class="message {{ $msg['role'] === 'user' ? 'user-message' : 'ai-message' }}">
                    <div class="message-content prose">
                        @if ($msg['role'] === 'model')
                            <div class="ai-header">
                                <span class="ai-name">Abah</span>
                            </div>
                            <div x-data="typewriter(@js($msg['content']), '{{ $msg['id'] }}')" x-init="start()" class="markdown-body">
                                <span x-html="rendered"></span>
                                <span x-show="typing" class="cursor-blink">|</span>
                            </div>
                        @else
                            {{ $msg['content'] }}
                        @endif
                    </div>
                </div>
            @endforeach

            @if ($isLoading)
                <div class="message ai-message">
                    <div class="message-content">
                        <div class="ai-header">
                            <span class="ai-name">Abah</span>
                        </div>
                        <div class="loading-dots">
                            <span></span><span></span><span></span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="input-area">
            @if (count($messages) <= 1)
                <div class="suggestions" style="margin-bottom: 1rem; justify-content: center;">
                    <button wire:click="setInput('Bah, ari hirup téh kanggé naon?'); $wire.sendMessage()"
                        class="suggestion-chip">Bah, ari hirup téh kanggé naon?</button>
                    <button wire:click="setInput('Cobi dongéngkeun sasakala Tangkuban Parahu'); $wire.sendMessage()"
                        class="suggestion-chip">Sasakala Tangkuban Parahu</button>
                    <button
                        wire:click="setInput('Abah, pasihan piwuruk supados abdi sumanget digawé'); $wire.sendMessage()"
                        class="suggestion-chip">Piwuruk supados sumanget digawé</button>
                </div>
            @endif

            <div class="input-wrapper">
                <textarea wire:model="userInput" wire:keydown.enter.prevent="sendMessage" placeholder="Tulis parosian anjeun didieu..."
                    rows="1" style="height: auto;" oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"></textarea>
                <button wire:click="sendMessage" class="send-btn" wire:loading.attr="disabled">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </div>
            <p class="disclaimer">SundaAI tiasa waé ngadamel kalepatan. Pariksa deui informasina.</p>
        </div>
    </main>

    <!-- API Key Modal -->
    @if ($showApiKeyModal)
        <div class="modal active">
            <div class="modal-content glass-panel" wire:ignore.self x-data="{ showTutorial: false }">
                <div class="modal-header">
                    <h2>Setélan API Key</h2>
                    <button wire:click="$set('showApiKeyModal', false)" class="close-modal"><i
                            class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="modal-body">
                    <p>Punten lebetkeun <strong>Google Gemini API Key</strong> anjeun kanggo ngawitan.</p>

                    <button @click="showTutorial = !showTutorial" class="tutorial-toggle">
                        <i class="fa-solid" :class="showTutorial ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
                        Kumaha cara kengingkeun API Key? (Tutorial)
                    </button>

                    <div x-show="showTutorial" x-transition class="tutorial-content">
                        <ol>
                            <li>Buka loka <a href="https://aistudio.google.com/app/apikey" target="_blank">Google AI
                                    Studio</a>.</li>
                            <li>Login nganggo akun Google (Gmail) salira.</li>
                            <li>Klik tombol biru <strong>"Create API key"</strong>.</li>
                            <li>Pilih <strong>"Create API key in new project"</strong>.</li>
                            <li>Salin kode (Copy) anu muncul, teras témpélkeun di handap ieu.</li>
                        </ol>
                    </div>

                    <div class="input-group">
                        <input type="text" wire:model="apiKeyInput" placeholder="Tempelkeun API Key didieu...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button wire:click="saveApiKey" class="btn-primary">Simpen</button>
                </div>
            </div>
        </div>
    @endif

    <script>
        // Track seen messages and suppression
        window.seenMessages = new Set();
        window.suppressTyping = false;

        function startHistoryLoad() {
            window.suppressTyping = true;
            // Close sidebar on mobile if a chat is selected
            if (window.innerWidth <= 768) {
                // We can access Alpine data from outside efficiently if we had a ref, 
                // but here we let the user close it or clicking overlay close it. 
                // Actually the user wants it to just work.
                // We can use a dispatch event?
                // Or just let the overlay handle it. 
                // But typically clicking a menu item ON MOBILE should close the menu.
                document.querySelector('.app-container')._x_dataStack[0].sidebarOpen = false;
            }

            // Reset suppression after enough time for Livewire to replace DOM
            setTimeout(() => {
                window.suppressTyping = false;
            }, 2000);
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('typewriter', (text, messageId) => ({
                fullText: text,
                rendered: '',
                typing: false,
                id: messageId,

                start() {
                    // Start logic modification:
                    // If suppressed (loading history) OR seen => show immediately.
                    if (window.suppressTyping || window.seenMessages.has(this.id) || window
                        .isInitialLoad) {
                        this.rendered = marked.parse(this.fullText);
                        this.highlight();
                        // Mark as seen so subsequent visits in this session are fine
                        window.seenMessages.add(this.id);
                        return;
                    }

                    // Mark as seen immediately
                    window.seenMessages.add(this.id);

                    // Type it out
                    this.typing = true;
                    let i = 0;
                    let currentText = '';

                    const typeChar = () => {
                        if (i < this.fullText.length) {
                            currentText += this.fullText.charAt(i);
                            this.rendered = marked.parse(currentText);
                            i++;

                            // Scroll
                            if (i % 5 === 0) {
                                let el = document.getElementById('chat-container');
                                if (el) el.scrollTop = el.scrollHeight;
                            }

                            setTimeout(typeChar, Math.random() * 10 + 5);
                        } else {
                            this.typing = false;
                            this.rendered = marked.parse(this.fullText);
                            this.highlight();
                            // Final scroll
                            let el = document.getElementById('chat-container');
                            if (el) el.scrollTop = el.scrollHeight;
                        }
                    };

                    typeChar();
                },

                highlight() {
                    this.$nextTick(() => {
                        this.$el.querySelectorAll('pre code').forEach((block) => {
                            hljs.highlightElement(block);
                        });
                    });
                }
            }));
        });

        document.addEventListener('livewire:initialized', () => {
            const container = document.getElementById('chat-container');
            const scrollToBottom = () => {
                if (container) container.scrollTop = container.scrollHeight;
            };

            Livewire.on('message-added', () => {
                setTimeout(scrollToBottom, 50);
            });

            // Mark existing messages as seen on first load
            @foreach ($messages as $msg)
                window.seenMessages.add('{{ $msg['id'] }}');
            @endforeach

            setTimeout(scrollToBottom, 100);
        });
    </script>

    <style>
        .cursor-blink {
            display: inline-block;
            animation: blink 1s step-end infinite;
            margin-left: 2px;
            color: var(--primary);
        }

        @keyframes blink {
            50% {
                opacity: 0;
            }
        }

        /* Transition for sidebar elements */
        .logo-text,
        .history-item span,
        .user-profile span,
        .history-group h3 {
            transition: opacity 0.2s;
        }
    </style>
</div>
