<?php

namespace App\Livewire\Conversations;

use Livewire\Component;

class Index extends Component
{
    public $selectedConversationId = null;

    public $messageBody = '';

    public function mount()
    {
        $first = \App\Models\Conversation::first();
        if ($first) {
            $this->selectedConversationId = $first->id;
        }
    }

    public function selectConversation($id)
    {
        $this->selectedConversationId = $id;
    }

    public function sendMessage()
    {
        if (empty(trim($this->messageBody)) || ! $this->selectedConversationId) {
            return;
        }

        $conv = \App\Models\Conversation::find($this->selectedConversationId);
        if (! $conv) {
            return;
        }

        \App\Models\Message::create([
            'conversation_id' => $conv->id,
            'body' => $this->messageBody,
            'direction' => 'outbound',
            'channel' => $conv->type,
            'sent_at' => now(),
        ]);

        $this->messageBody = '';
    }

    public function render()
    {
        $conversations = \App\Models\Conversation::with([
            'contact',
            'messages' => function ($q) {
                $q->latest('sent_at');
            },
        ])->get();

        $selectedConversation = null;
        if ($this->selectedConversationId) {
            $selectedConversation = \App\Models\Conversation::with([
                'contact',
                'messages' => function ($q) {
                    $q->orderBy('sent_at', 'asc');
                },
            ])->find($this->selectedConversationId);
        }

        return view('livewire.conversations.index', [
            'conversations' => $conversations,
            'selectedConversation' => $selectedConversation,
        ]);
    }
}
