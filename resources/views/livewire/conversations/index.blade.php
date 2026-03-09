<x-app-container>
    <x-page-header title="Unified Inbox">
        <x-button color="primary">New Message</x-button>
    </x-page-header>

    <div class="flex h-[75vh] bg-white rounded-lg shadow border border-gray-200 overflow-hidden">

        <!-- Channels & Contacts Sidebar -->
        <div class="w-1/3 border-r border-gray-200 flex flex-col bg-gray-50">
            <div class="p-3 border-b border-gray-200 bg-white">
                <div class="flex items-center space-x-2">
                    <x-input type="text" placeholder="Search conversations..." class="w-full text-sm py-1.5" />
                    <x-button color="outline" class="px-2 py-1"><svg class="w-5 h-5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                            </path>
                        </svg></x-button>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto w-full">
                @foreach($conversations as $conversation)
                    <!-- Conversation List Item -->
                    <div wire:click="selectConversation({{ $conversation->id }})"
                        class="p-4 border-b border-gray-100 cursor-pointer {{ $selectedConversationId == $conversation->id ? 'bg-white border-l-4 border-l-primary' : 'hover:bg-gray-100 bg-gray-50' }}">
                        <div class="flex justify-between items-start mb-1">
                            <h4 class="font-bold text-sm text-text-primary">{{ $conversation->contact->first_name }}
                                {{ $conversation->contact->last_name }}</h4>
                            @if($conversation->messages->count() > 0)
                                <span
                                    class="text-xs text-text-muted">{{ $conversation->messages->first()->sent_at->diffForHumans() }}</span>
                            @endif
                        </div>
                        <div class="flex items-center text-xs text-text-muted mb-1 space-x-1">
                            <span
                                class="bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded text-[10px] font-bold uppercase">{{ $conversation->type }}</span>
                            @if($conversation->contact->company_name)
                                <span>&bull; {{ $conversation->contact->company_name }}</span>
                            @endif
                        </div>
                        <p class="text-sm text-text-muted truncate">
                            {{ $conversation->messages->count() > 0 ? $conversation->messages->first()->body : 'No messages' }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Chat Area -->
        <div class="flex-1 flex flex-col bg-white">
            @if($selectedConversation)
                <!-- Chat Header -->
                <div class="p-4 border-b border-gray-200 flex justify-between items-center bg-gray-50/50">
                    <div>
                        <h3 class="font-bold text-lg text-text-primary">{{ $selectedConversation->contact->first_name }}
                            {{ $selectedConversation->contact->last_name }}</h3>
                        <p class="text-xs text-text-muted">{{ $selectedConversation->contact->phone ?? 'No Phone' }} •
                            {{ $selectedConversation->contact->email ?? 'No email' }}</p>
                    </div>
                    <div class="flex space-x-2">
                        <x-button color="outline" class="text-xs py-1">View Contact</x-button>
                        <x-button color="primary" class="text-xs py-1">Book Meeting</x-button>
                    </div>
                </div>

                <!-- Messages Area -->
                <div class="flex-1 p-4 overflow-y-auto space-y-4 bg-gray-50">
                    <div class="text-center text-xs text-gray-400 my-4">Conversation Started</div>

                    @foreach($selectedConversation->messages as $msg)
                        @if($msg->direction == 'outbound')
                            <!-- Outgoing -->
                            <div class="flex justify-end">
                                <div class="bg-primary text-white p-3 rounded-lg rounded-tr-none max-w-md text-sm">
                                    <p>{{ $msg->body }}</p>
                                    <p class="text-[10px] text-indigo-200 text-right mt-1">Sent via {{ strtoupper($msg->channel) }}
                                        • {{ $msg->sent_at->format('g:i A') }}</p>
                                </div>
                            </div>
                        @else
                            <!-- Incoming -->
                            <div class="flex justify-start">
                                <div
                                    class="bg-white border border-gray-200 text-text-primary p-3 rounded-lg rounded-tl-none max-w-md text-sm shadow-sm">
                                    <p>{{ $msg->body }}</p>
                                    <p class="text-[10px] text-gray-400 mt-1">Received via {{ strtoupper($msg->channel) }} •
                                        {{ $msg->sent_at->format('g:i A') }}</p>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                <!-- Input Area -->
                <div class="p-4 border-t border-gray-200 bg-white">
                    <form wire:submit="sendMessage">
                        <div class="flex space-x-2 mb-2">
                            <button type="button"
                                class="text-xs px-3 py-1 bg-gray-100 text-gray-600 rounded font-semibold uppercase">{{ $selectedConversation->type }}</button>
                        </div>
                        <div class="flex pb-2">
                            <textarea wire:model="messageBody" required
                                class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                rows="3" placeholder="Type a message..."></textarea>
                        </div>
                        <div class="flex justify-between items-center">
                            <div class="flex space-x-2 text-gray-400">
                                <!-- Helper icons like attachments -->
                            </div>
                            <x-button type="submit" color="primary">Send</x-button>
                        </div>
                    </form>
                </div>
            @else
                <div class="flex-1 flex items-center justify-center text-gray-400">
                    <p>Select a conversation from the sidebar to view.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-container>