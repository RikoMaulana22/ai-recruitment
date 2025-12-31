<div class="flex flex-col h-screen bg-gray-100">
    
    <div class="bg-white shadow px-6 py-4 flex justify-between items-center fixed w-full top-0 z-10 border-b">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-bold">
                AI
            </div>
            <div>
                <h1 class="text-lg font-bold text-gray-800">{{ $candidate->name }}</h1>
                <p class="text-xs text-green-600 flex items-center gap-1">
                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                    Sedang Wawancara
                </p>
            </div>
        </div>

        <button wire:click="finishInterview" 
                wire:confirm="Apakah Anda yakin ingin mengakhiri wawancara ini? Jawaban Anda akan langsung dinilai."
                class="bg-red-50 text-red-600 hover:bg-red-100 px-4 py-2 rounded-lg text-sm font-semibold border border-red-200 transition">
            ⏹ Akhiri Sesi
        </button>
    </div>

    <div class="flex-1 overflow-y-auto p-6 pt-24 pb-24 space-y-4 bg-gray-100" id="chat-container">
        @foreach($messages as $msg)
            <div class="flex {{ $msg['role'] == 'user' ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-2xl px-5 py-3 rounded-2xl shadow-sm text-sm leading-relaxed
                    {{ $msg['role'] == 'user' 
                        ? 'bg-blue-600 text-white rounded-br-none' 
                        : 'bg-white text-gray-800 border border-gray-200 rounded-bl-none' }}">
                    {!! nl2br(e($msg['content'])) !!}
                </div>
            </div>
        @endforeach

        <div wire:loading wire:target="sendMessage" class="flex justify-start">
            <div class="bg-gray-200 text-gray-500 px-4 py-2 rounded-full text-xs animate-pulse">
                AI sedang mengetik...
            </div>
        </div>
    </div>

    <div class="bg-white border-t p-4 fixed bottom-0 w-full z-10">
        <div class="max-w-4xl mx-auto flex gap-2">
            <input type="text" 
                   wire:model="userMessage" 
                   wire:keydown.enter="sendMessage"
                   class="flex-1 border-gray-300 rounded-full focus:border-blue-500 focus:ring focus:ring-blue-200 px-4 py-3 shadow-sm"
                   placeholder="Ketik jawaban Anda di sini..."
                   autofocus>
            
            <button wire:click="sendMessage" 
                    wire:loading.attr="disabled"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-full font-bold transition flex items-center disabled:opacity-50 shadow-md">
                <span wire:loading.remove>Kirim</span>
                <span wire:loading>...</span>
            </button>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            const chatContainer = document.getElementById('chat-container');
            const scrollToBottom = () => { chatContainer.scrollTop = chatContainer.scrollHeight; };

            scrollToBottom();
            Livewire.hook('morph.updated', () => { scrollToBottom(); });
        });
    </script>
</div>