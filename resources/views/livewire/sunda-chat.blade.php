<div class="app-container" x-data="{ sidebarOpen: window.innerWidth > 768 }" @start-generating.window="$wire.generateAiResponse()">
    <!-- Sidebar -->
    <aside class="sidebar" :class="{ 'show': sidebarOpen, 'collapsed': !sidebarOpen }">
        <div class="sidebar-header">
            <div class="logo">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo-img">
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

        <div class="sidebar-footer" x-data="{ showDevModal: false }">
            <button @click="showDevModal = true" class="user-profile">
                <div class="avatar">
                    <i class="fa-solid fa-code"></i>
                </div>
                <a href="{{ route('profile') }}" class="dev-info" x-show="sidebarOpen" x-transition
                    style="text-decoration: none; color: inherit;">
                    <small>Dimekarkeun ku:</small>
                    <strong style="display: block;">Rifal Kurniawan</strong>
                </a>
            </button>

            <!-- Dev Info Modal -->
            <div class="modal" :class="{ 'active': showDevModal }" x-show="showDevModal" x-transition
                style="display: none;" :style="showDevModal ? 'display: flex' : 'display: none'">
                <div class="modal-content glass-panel" @click.away="showDevModal = false">
                    <div class="modal-header">
                        <h2>Ngeunaan Pamekar</h2>
                        <button @click="showDevModal = false" class="close-modal"><i
                                class="fa-solid fa-xmark"></i></button>
                    </div>
                    <div class="modal-body" style="text-align: center;">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo"
                            style="width: 80px; height: 80px; margin: 0 auto 1rem; border-radius: 1rem; display: block; object-fit: contain;">
                        <h3 style="color: var(--primary); margin-bottom: 0.5rem; font-size: 1.5rem;">Pananyaan Abah</h3>
                        <p style="margin-bottom: 1.5rem; font-size: 0.95rem; line-height: 1.4;">Aplikasi cariosan
                            kacerdasan jieunan (AI) anu ngagunakeun Basa Sunda, pikeun ngalestarikeun budaya jeung basa
                            karuhun urang dina jaman modérn.</p>

                        <div
                            style="background: rgba(0,0,0,0.3); padding: 1.25rem; border-radius: 0.75rem; border: 1px solid var(--border);">
                            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.25rem;">Dimekarkeun
                                kalayan asih ku:</p>
                            <h4 style="color: var(--text-main); font-size: 1.25rem; margin: 0;">
                                <a href="{{ route('profile') }}"
                                    style="color: var(--primary); text-decoration: none; border-bottom: 1px dashed var(--primary);">Rifal
                                    Kurniawan</a>
                            </h4>
                            <p style="font-size: 0.8rem; margin-top: 0.75rem; opacity: 0.6;">© 2024 Pananyaan Abah.
                                Sadaya hak ditangtayungan.</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button @click="showDevModal = false" class="btn-primary">Tutup</button>
                    </div>
                </div>
            </div>
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
            <div class="brand-group" style="flex-direction: row; align-items: center; gap: 0.75rem;">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo-img"
                    style="width: 32px; height: 32px;">
                <div style="display: flex; flex-direction: column;">
                    <span class="brand">Pananyaan Abah</span>
                    <span class="aksara-sunda mobile-aksara">ᮕᮔᮑᮃᮔ᮪ ᮃᮒᮂ</span>
                </div>
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
                <textarea wire:model="userInput" wire:keydown.enter.prevent="sendMessage"
                    placeholder="Tulis parosian anjeun didieu..." rows="1" style="height: auto;"
                    oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"></textarea>
                <button wire:click="sendMessage" class="send-btn" wire:loading.attr="disabled">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </div>
            <p class="disclaimer">SundaAI tiasa waé ngadamel kalepatan. Pariksa deui informasina.</p>
        </div>
    </main>


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
