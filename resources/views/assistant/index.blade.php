<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Smart Assistant') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Container Chat -->
            <div class="bg-[#181A2F] border border-[#37415C] rounded-2xl shadow-xl overflow-hidden flex flex-col h-[75vh]">
                
                <!-- Header Chat -->
                <div class="bg-[#242E49] px-6 py-4 border-b border-[#37415C] flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-xl">
                        🤖
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white">Smart Assistant</h2>
                        <p class="text-xs text-green-400">Online - Gemini 3.8 Flash Connected</p>
                    </div>
                </div>

                <!-- Area Pesan (Chat Box) -->
                <div id="chat-box" class="flex-1 p-6 overflow-y-auto space-y-4 bg-[#0F111A]">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-xs">🤖</div>
                        <div class="bg-[#242E49] text-white p-3 rounded-2xl rounded-tl-none max-w-[80%] text-sm">
                            Halo Bagus! Saya adalah AI Assistant. Ada data SAP atau log mesin yang ingin dianalisis hari ini?
                        </div>
                    </div>
                </div>

                <!-- Area Input Text -->
                <div class="p-4 bg-[#242E49] border-t border-[#37415C]">
                    <form id="chat-form" class="flex gap-3">
                        @csrf
                        <input type="text" id="user-input" class="flex-1 bg-[#181A2F] border border-[#37415C] rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500" placeholder="Ketik pertanyaanmu..." autocomplete="off" required>
                        <button type="submit" id="send-btn" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-3 rounded-xl font-bold shadow-md transition duration-200 shrink-0">
                            Kirim
                        </button>
                    </form>
                </div>

            </div>

        </div>
    </div>

    <!-- Script AJAX untuk Chat -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $('#chat-form').submit(function(e) {
            e.preventDefault();
            
            let message = $('#user-input').val();
            if(!message) return;

            // Tampilkan pesan user
            $('#chat-box').append(`
                <div class="flex items-start gap-3 justify-end">
                    <div class="bg-blue-600 text-white p-3 rounded-2xl rounded-tr-none max-w-[80%] text-sm">
                        ${message}
                    </div>
                </div>
            `);
            
            $('#user-input').val('');
            $('#chat-box').scrollTop($('#chat-box')[0].scrollHeight);

            // Indikator Loading
            let loadingId = 'loading-' + Date.now();
            $('#chat-box').append(`
                <div id="${loadingId}" class="flex items-start gap-3">
                    <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-xs">🤖</div>
                    <div class="bg-[#242E49] text-slate-400 p-3 rounded-2xl rounded-tl-none text-sm italic">
                        Sedang berpikir...
                    </div>
                </div>
            `);
            $('#chat-box').scrollTop($('#chat-box')[0].scrollHeight);

            // Kirim ke API Controller
            $.ajax({
                url: "{{ route('assistant.chat') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    message: message
                },
                success: function(response) {
                    $('#' + loadingId).remove();
                    
                    // Rapikan format Markdown dari Gemini (Bold dan Enter)
                    let formattedReply = response.reply
                        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                        .replace(/\n/g, '<br>');

                    $('#chat-box').append(`
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-xs shrink-0">🤖</div>
                            <div class="bg-[#242E49] text-white p-3 rounded-2xl rounded-tl-none max-w-[80%] text-sm leading-relaxed">
                                ${formattedReply}
                            </div>
                        </div>
                    `);
                    $('#chat-box').scrollTop($('#chat-box')[0].scrollHeight);
                },
                error: function(xhr) {
                    $('#' + loadingId).remove();
                    let errMsg = xhr.responseJSON && xhr.responseJSON.reply ? xhr.responseJSON.reply : 'Error: Koneksi ke server terputus.';
                    $('#chat-box').append(`
                        <div class="flex items-start gap-3">
                            <div class="bg-red-500 text-white p-3 rounded-2xl max-w-[80%] text-sm">
                                ${errMsg}
                            </div>
                        </div>
                    `);
                    $('#chat-box').scrollTop($('#chat-box')[0].scrollHeight);
                }
            });
        });
    </script>
</x-app-layout>