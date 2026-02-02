<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class SundaChat extends Component
{
    public $sessions = []; // Metadata for sidebar: id, title, created_at
    public $currentSessionId = null;
    public $messages = [];
    public $userInput = '';
    public $isLoading = false;
    public $showApiKeyModal = false;
    public $apiKeyInput = '';

    // System Prompt for Sundanese Context
    const SYSTEM_PROMPT = <<<EOT
You are a helpful AI assistant named "Abah" (Pananyaan Abah).
IMPORTANT: You MUST speak ONLY in Sundanese (Basa Sunda).
- Adopt the persona of a wise, patient, and polite Sundanese elder ("Sesepuh" or "Abah").
- Use "Basa Sunda Lemes" (Polite Sundanese) mixed with some "Basa Sunda Buhun" (Old/Classic Sundanese) idioms/words where appropriate to give a traditional nuance.
- Address the user as "Kasep" (if male/general) or "Geulis" (if female), or simply "Anaking" (my child) / "Wargi" (kin).
- Do NOT use Indonesian (Bahasa Indonesia) or English unless explicitly asked to translate.
- If the user asks in another language, politely explain in Sundanese that Abah only speaks the language of the ancestors (Basa Karuhun/Sunda).
- Use markdown for formatting.
- Be helpful, wise, and soothing.
EOT;

    public function mount()
    {
        // Initialize sessions storage if invalid
        if (!Session::has('chat_sessions')) {
            Session::put('chat_sessions', []);
        }

        // Load sessions list for sidebar (sorted by date desc)
        $this->refreshSidebar();

        // Restore last active session or create new
        $currentId = Session::get('current_chat_id');

        // Check if previous session exists in our storage
        $allSessions = Session::get('chat_sessions');

        if ($currentId && isset($allSessions[$currentId])) {
            $this->loadChat($currentId);
        } else {
            // Check if there are any sessions at all, maybe load the latest one?
            // Or just create new. Let's create new for a fresh start or if migration failed.
            $this->createNewSession();
        }

        if (!Session::has('gemini_api_key')) {
            $this->showApiKeyModal = true;
        }
    }

    public function refreshSidebar()
    {
        $all = Session::get('chat_sessions', []);

        // Sort by created_at desc
        uasort($all, function ($a, $b) {
            return $b['created_at'] <=> $a['created_at'];
        });

        $this->sessions = $all;
    }

    public function saveApiKey()
    {
        if (!empty($this->apiKeyInput)) {
            Session::put('gemini_api_key', $this->apiKeyInput);
            $this->showApiKeyModal = false;
        }
    }

    public function newChat()
    {
        // If current session is empty (only greeting), don't create another empty one.
        if (count($this->messages) <= 1) {
            return;
        }

        $this->createNewSession();
    }

    public function createNewSession()
    {
        $id = (string) Str::uuid();

        $initialMessages = [[
            'id' => uniqid(),
            'role' => 'model',
            'content' => "Sampurasun, Anaking! 🙏\nIeu Abah, siap ngabandungan keluh kersal atanapi pananyaan hidep. Sok, badé naroskeun naon ka Abah?"
        ]];

        $sessionData = [
            'id' => $id,
            'title' => 'Gunem Catur Anyar',
            'messages' => $initialMessages,
            'created_at' => time() // Timestamp
        ];

        // Save to store
        $all = Session::get('chat_sessions', []);
        $all[$id] = $sessionData;
        Session::put('chat_sessions', $all);
        Session::put('current_chat_id', $id);

        // Update local state
        $this->currentSessionId = $id;
        $this->messages = $initialMessages;
        $this->refreshSidebar();
    }

    public function loadChat($id)
    {
        $all = Session::get('chat_sessions', []);

        if (isset($all[$id])) {
            $this->currentSessionId = $id;
            $this->messages = $all[$id]['messages'];
            Session::put('current_chat_id', $id);
            // $this->refreshSidebar(); // No need unless title changed elsewhere
        }
    }

    public function deleteChat($id)
    {
        $all = Session::get('chat_sessions', []);

        if (isset($all[$id])) {
            unset($all[$id]);
            Session::put('chat_sessions', $all);

            // If we deleted the current session, switch to another or create new
            if ($this->currentSessionId === $id) {
                // Get the first available key if any
                $remainingIds = array_keys($all);
                if (!empty($remainingIds)) {
                    // Load the first available
                    $this->loadChat($remainingIds[0]);
                } else {
                    // No chats left, create new
                    $this->createNewSession();
                }
            }

            $this->refreshSidebar();
        }
    }

    public function setInput($text)
    {
        $this->userInput = $text;
    }

    public function sendMessage()
    {
        if (empty(trim($this->userInput))) return;

        $userText = $this->userInput;
        $this->messages[] = [
            'id' => uniqid(),
            'role' => 'user',
            'content' => $userText
        ];

        $this->userInput = '';
        $this->isLoading = true;

        $this->persistSession(); // Save user message immediately

        // Check if we need to update title (if this is the first user message)
        // Greeting is index 0. User message is index 1.
        if (count($this->messages) === 2) {
            $this->updateSessionTitle($userText);
        }

        $this->dispatch('start-generating');
    }

    public function updateSessionTitle($text)
    {
        $title = Str::limit($text, 30, '...');

        $all = Session::get('chat_sessions', []);
        if (isset($all[$this->currentSessionId])) {
            $all[$this->currentSessionId]['title'] = $title;
            Session::put('chat_sessions', $all);
            $this->refreshSidebar();
        }
    }

    public function persistSession()
    {
        $all = Session::get('chat_sessions', []);
        if (isset($all[$this->currentSessionId])) {
            $all[$this->currentSessionId]['messages'] = $this->messages;
            Session::put('chat_sessions', $all);
        }
    }

    public function generateAiResponse()
    {
        $apiKey = Session::get('gemini_api_key');

        if (!$apiKey) {
            $this->messages[] = [
                'id' => uniqid(),
                'role' => 'model',
                'content' => "**Punten:** API Key teu acan disetél."
            ];
            $this->isLoading = false;
            $this->persistSession();
            return;
        }

        $this->isLoading = true;

        try {
            $geminiHistory = $this->formatHistoryForGemini();
            // Revert back to 1.5-flash as 2.5 is not yet available/stable public API
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

            $payload = [
                'contents' => $geminiHistory,
                'systemInstruction' => [
                    'parts' => [['text' => self::SYSTEM_PROMPT]]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 2048,
                ]
            ];

            $response = Http::post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Punten, teu aya waleran ti server.';

                $this->messages[] = [
                    'id' => uniqid(),
                    'role' => 'model',
                    'content' => $reply
                ];
            } else {
                $errorMsg = $response->json()['error']['message'] ?? $response->body();
                $this->messages[] = [
                    'id' => uniqid(),
                    'role' => 'model',
                    'content' => "**Punten, aya kasalahan:** " . $errorMsg
                ];
            }
        } catch (\Exception $e) {
            $this->messages[] = [
                'id' => uniqid(),
                'role' => 'model',
                'content' => "**Error:** " . $e->getMessage()
            ];
        }

        $this->isLoading = false;
        $this->persistSession(); // Save AI response
    }

    private function formatHistoryForGemini()
    {
        $formatted = [];
        foreach ($this->messages as $msg) {
            // Gemini roles: 'user' or 'model'
            $formatted[] = [
                'role' => $msg['role'] === 'user' ? 'user' : 'model',
                'parts' => [['text' => $msg['content']]]
            ];
        }
        return $formatted;
    }

    public function render()
    {
        return view('livewire.sunda-chat')->layout('layouts.app');
    }
}
