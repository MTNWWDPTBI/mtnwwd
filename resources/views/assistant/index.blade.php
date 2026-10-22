<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-white-800 dark:text-white-200 leading-tight flex items-center gap-2">
                <span>🤖</span>
                <span>{{ __('Smart Assistant (MTN-AI Bekaert)') }}</span>
            </h2>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                <span class="w-1.5 h-1.5 mr-1.5 bg-emerald-400 rounded-full animate-pulse"></span>
                MTN-AI Online
            </span>
        </div>
    </x-slot>

    <div class="py-4 md:py-6">
        <div class="max-w-5xl mx-auto px-2 sm:px-6 lg:px-8">
            
            <!-- Container WhatsApp Style Chat -->
            <div class="bg-[#111b21] border border-[#222e35] rounded-2xl shadow-2xl overflow-hidden flex flex-col h-[82vh] sm:h-[80vh] relative">
                
                <!-- WhatsApp Style Header -->
                <div class="bg-[#202c33] px-4 py-3 border-b border-[#222e35] flex items-center justify-between shrink-0 shadow-sm z-10">
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-emerald-600 to-teal-400 flex items-center justify-center text-white font-bold text-lg shadow-md">
                                🤖
                            </div>
                            <span class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 border-2 border-[#202c33] rounded-full"></span>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-white tracking-wide">Smart Assistant</h3>
                            <p class="text-xs text-emerald-400 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-ping"></span>
                                online • MTN-AI Bekaert Indonesia
                            </p>
                        </div>
                    </div>

                    <!-- Header Actions -->
                    <div class="flex items-center gap-2">
                        <button type="button" id="btn-clear-chat" title="Bersihkan riwayat obrolan akun Anda" class="text-slate-400 hover:text-rose-400 hover:bg-[#111b21] p-2 rounded-lg transition text-xs flex items-center gap-1.5 border border-transparent hover:border-rose-500/30">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            <span class="hidden sm:inline">Hapus Riwayat</span>
                        </button>
                    </div>
                </div>

                <!-- Chat Box Area with WhatsApp Style Background -->
                <div id="chat-box" class="flex-1 p-3 sm:p-5 overflow-y-auto space-y-3.5 bg-[#0b141a] relative" style="background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 1px); background-size: 20px 20px;">
                    
                    <!-- Date badge -->
                    <div class="flex justify-center my-2">
                        <span class="bg-[#182229] text-slate-400 text-[11px] font-medium px-3 py-1 rounded-full shadow-sm border border-[#222e35]">
                            Percakapan Terenkripsi & Riwayat Tersimpan per Akun
                        </span>
                    </div>

                    <!-- Bot Welcome Message -->
                    <div class="flex items-start gap-2.5 max-w-[92%] sm:max-w-[80%]">
                        <div class="w-7 h-7 rounded-full bg-emerald-700 flex items-center justify-center text-xs shrink-0 text-white shadow">
                            🤖
                        </div>
                        <div class="bg-[#202c33] text-slate-100 p-3.5 rounded-2xl rounded-tl-none shadow-md text-sm leading-relaxed border border-[#2a3942]/50 ai-content">
                            <p class="font-medium text-emerald-400 mb-1">Halo {{ auth()->user()->name }}! 👋</p>
                            <p>Saya <strong>MTN-AI Bekaert Indonesia</strong>, asisten cerdas maintenance pabrik Bekaert. Saya dapat membaca seluruh riwayat order SAP, menganalisis kerusakan mesin, downtime, riwayat teknisi, serta <strong>menyajikan grafik analisis</strong> secara instan.</p>
                            <div class="mt-2 text-xs text-slate-400 bg-[#111b21] p-2.5 rounded-lg border border-[#222e35]">
                                💡 <em>Tips: Coba tanyakan data mesin, perbandingan jam breakdown, atau minta saya membuatkan grafik visual.</em>
                            </div>
                            <span class="block text-right text-[10px] text-slate-400 mt-1">Baru saja</span>
                        </div>
                    </div>

                    <!-- Preloaded History (Account Specific) -->
                    @if(isset($histories) && count($histories) > 0)
                        @foreach($histories as $history)
                            <!-- User Message -->
                            <div class="flex items-start justify-end">
                                <div class="bg-[#005c4b] text-white p-3 rounded-2xl rounded-tr-none max-w-[90%] sm:max-w-[78%] text-sm shadow-md break-words">
                                    <div class="whitespace-pre-wrap">{{ $history->user_message }}</div>
                                    <div class="flex items-center justify-end gap-1 mt-1 text-[10px] text-emerald-200/80">
                                        <span>{{ $history->created_at->format('H:i') }}</span>
                                        <svg class="w-3.5 h-3.5 text-blue-300 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7m-14 4l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- AI Message -->
                            <div class="flex items-start gap-2.5 max-w-[92%] sm:max-w-[85%]">
                                <div class="w-7 h-7 rounded-full bg-emerald-700 flex items-center justify-center text-xs shrink-0 text-white shadow">
                                    🤖
                                </div>
                                <div class="bg-[#202c33] text-slate-100 p-3.5 rounded-2xl rounded-tl-none shadow-md text-sm leading-relaxed border border-[#2a3942]/50 ai-content overflow-x-auto w-full">
                                    <div class="raw-ai-text" data-rendered="false">{!! e($history->ai_response) !!}</div>
                                    <span class="block text-right text-[10px] text-slate-400 mt-1">{{ $history->created_at->format('H:i') }}</span>
                                </div>
                            </div>
                        @endforeach
                    @endif

                </div>

                <!-- Quick Prompt Suggestions (Horizontal Scroll on Mobile) -->
                <div class="px-3 py-2 bg-[#182229] border-t border-[#222e35] flex items-center gap-2 overflow-x-auto no-scrollbar shrink-0">
                    <span class="text-xs text-slate-400 font-medium shrink-0 flex items-center gap-1">
                        ⚡ Pintasan:
                    </span>
                    <button type="button" class="quick-chip text-xs bg-[#202c33] hover:bg-[#2a3942] text-slate-200 px-3 py-1.5 rounded-full whitespace-nowrap transition border border-[#2a3942] active:scale-95 shrink-0" data-prompt="Tampilkan 5 mesin dengan jam breakdown tertinggi beserta grafiknya">
                        📉 Top 5 Breakdown Mesin + Grafik
                    </button>
                    <button type="button" class="quick-chip text-xs bg-[#202c33] hover:bg-[#2a3942] text-slate-200 px-3 py-1.5 rounded-full whitespace-nowrap transition border border-[#2a3942] active:scale-95 shrink-0" data-prompt="Buatkan grafik perbandingan komponen part mesin (object_part) yang paling sering rusak">
                        📊 Grafik Kerusakan Komponen
                    </button>
                    <button type="button" class="quick-chip text-xs bg-[#202c33] hover:bg-[#2a3942] text-slate-200 px-3 py-1.5 rounded-full whitespace-nowrap transition border border-[#2a3942] active:scale-95 shrink-0" data-prompt="Berapa total jam breakdown mesin NDB-3006 di bulan 01.2026 dan apa saja masalah utamanya?">
                        ⏱️ Breakdown NDB-3006 Bulan Ini
                    </button>
                    <button type="button" class="quick-chip text-xs bg-[#202c33] hover:bg-[#2a3942] text-slate-200 px-3 py-1.5 rounded-full whitespace-nowrap transition border border-[#2a3942] active:scale-95 shrink-0" data-prompt="Siapa saja teknisi (executor) yang paling banyak menyelesaikan perbaikan dan berapa total jam kerjanya?">
                        👨‍🔧 Ranking Teknisi Perbaikan
                    </button>
                </div>

                <!-- WhatsApp Input Area -->
                <div class="p-2.5 sm:p-3 bg-[#202c33] border-t border-[#222e35] shrink-0">
                    <form id="chat-form" class="flex items-center gap-2">
                        @csrf
                        <div class="flex-1 relative">
                            <input 
                                type="text" 
                                id="user-input" 
                                class="w-full bg-[#2a3942] text-slate-100 placeholder-slate-400 text-sm sm:text-base rounded-full px-4 sm:px-5 py-2.5 sm:py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500 border-none transition shadow-inner" 
                                placeholder="Ketik pertanyaan atau minta analisis data mesin..." 
                                autocomplete="off" 
                                required
                            >
                        </div>
                        <button 
                            type="submit" 
                            id="send-btn" 
                            class="bg-[#00a884] hover:bg-[#02906f] active:scale-95 text-white w-10 h-10 sm:w-11 sm:h-11 rounded-full flex items-center justify-center font-bold shadow-lg transition duration-150 shrink-0 disabled:opacity-50 disabled:cursor-not-allowed"
                            title="Kirim Pesan"
                        >
                            <svg class="w-5 h-5 translate-x-0.5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                            </svg>
                        </button>
                    </form>
                </div>

            </div>

        </div>
    </div>

    <!-- Scripts: Chart.js, Marked.js, Mermaid.js, jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>

    <style>
        /* Custom scrollbar */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        #chat-box::-webkit-scrollbar { width: 6px; }
        #chat-box::-webkit-scrollbar-track { background: transparent; }
        #chat-box::-webkit-scrollbar-thumb { background-color: rgba(255, 255, 255, 0.15); border-radius: 9999px; }

        /* Markdown & Tables inside AI Bubbles */
        .ai-content table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 0.75rem 0; 
            font-size: 0.82rem; 
            background: #111b21;
            border-radius: 8px;
            overflow: hidden;
            display: block;
            overflow-x: auto;
        }
        .ai-content th, .ai-content td { 
            border: 1px solid #2a3942; 
            padding: 7px 10px; 
            text-align: left; 
            white-space: nowrap;
        }
        .ai-content th { 
            background-color: #182229; 
            font-weight: 600; 
            color: #34d399; 
        }
        .ai-content tr:nth-child(even) { background-color: rgba(255, 255, 255, 0.02); }
        .ai-content ul { list-style-type: disc; padding-left: 1.25rem; margin: 0.5rem 0; }
        .ai-content ol { list-style-type: decimal; padding-left: 1.25rem; margin: 0.5rem 0; }
        .ai-content code { 
            background-color: #111b21; 
            padding: 2px 6px; 
            border-radius: 4px; 
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; 
            font-size: 0.85em; 
            color: #38bdf8; 
            border: 1px solid #222e35;
        }
        .ai-content pre code {
            display: block;
            padding: 10px;
            overflow-x: auto;
            border-radius: 8px;
            margin: 0.5rem 0;
        }
        .ai-content p { margin-bottom: 0.5rem; }
        .ai-content p:last-child { margin-bottom: 0; }
        .ai-content strong { color: #f1f5f9; }

        /* Typing Dots Animation */
        .typing-dot {
            width: 6px;
            height: 6px;
            background-color: #34d399;
            border-radius: 50%;
            display: inline-block;
            animation: typingBounce 1.4s infinite ease-in-out both;
        }
        .typing-dot:nth-child(1) { animation-delay: -0.32s; }
        .typing-dot:nth-child(2) { animation-delay: -0.16s; }
        @keyframes typingBounce {
            0%, 80%, 100% { transform: scale(0); opacity: 0.4; }
            40% { transform: scale(1); opacity: 1; }
        }

        /* Chart Canvas Container */
        .chart-box-container {
            background: #111b21;
            border: 1px solid #2a3942;
            border-radius: 12px;
            padding: 14px;
            margin: 12px 0;
            position: relative;
            width: 100%;
            max-width: 100%;
            overflow: hidden;
        }
        .chart-box-canvas {
            max-height: 280px;
            width: 100% !important;
        }
    </style>

    <script>
        $(document).ready(function() {
            // Inisialisasi mermaid jika ada diagram
            if (typeof mermaid !== 'undefined') {
                mermaid.initialize({ startOnLoad: false, theme: 'dark' });
            }

            // Scroll otomatis ke posisi paling bawah saat halaman dimuat
            function scrollToBottom(smooth = false) {
                let box = document.getElementById('chat-box');
                if (box) {
                    if (smooth) {
                        box.scrollTo({ top: box.scrollHeight, behavior: 'smooth' });
                    } else {
                        box.scrollTop = box.scrollHeight;
                    }
                }
            }
            scrollToBottom();

            // Parser Pesan AI: Render Markdown + Parse [CHART] JSON ke Chart.js
            function renderAiContent(text) {
                if (!text) return '';

                let chartMatches = [];
                // Cari tag [CHART]...[/CHART]
                let cleanText = text.replace(/\[CHART\](.*?)\[\/CHART\]/gis, function(match, jsonContent) {
                    try {
                        let parsed = JSON.parse(jsonContent.trim());
                        let chartId = 'chart-' + Math.random().toString(36).substring(2, 9);
                        chartMatches.push({ id: chartId, data: parsed });
                        return `<div class="chart-box-container"><canvas id="${chartId}" class="chart-box-canvas"></canvas></div>`;
                    } catch(e) {
                        console.error('Invalid Chart JSON:', e, jsonContent);
                        return '';
                    }
                });

                // Render Markdown via Marked.js
                let html = (typeof marked !== 'undefined') 
                    ? marked.parse(cleanText) 
                    : cleanText.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>').replace(/\n/g, '<br>');

                return { html: html, charts: chartMatches };
            }

            // Render Chart.js setelah elemen canvas masuk DOM
            function instantiateCharts(charts) {
                if (!charts || charts.length === 0) return;

                charts.forEach(function(item) {
                    let ctx = document.getElementById(item.id);
                    if (!ctx) return;

                    let cData = item.data;
                    let chartType = cData.chart_type || cData.type || 'bar';
                    let labels = cData.labels || [];
                    let datasetLabel = cData.dataset_label || cData.title || 'Nilai';
                    let dataValues = cData.data || [];

                    // Palette warna modern
                    let colors = [
                        '#10b981', '#3b82f6', '#f59e0b', '#ec4899', '#8b5cf6', 
                        '#06b6d4', '#14b8a6', '#f97316', '#6366f1', '#eab308'
                    ];

                    let bgColors = (chartType === 'pie' || chartType === 'doughnut') 
                        ? colors.slice(0, labels.length) 
                        : (chartType === 'line' ? 'rgba(16, 185, 129, 0.2)' : '#10b981');

                    let borderColors = (chartType === 'line') ? '#10b981' : colors.slice(0, labels.length);

                    new Chart(ctx, {
                        type: chartType,
                        data: {
                            labels: labels,
                            datasets: [{
                                label: datasetLabel,
                                data: dataValues,
                                backgroundColor: bgColors,
                                borderColor: borderColors,
                                borderWidth: 1.5,
                                fill: (chartType === 'line'),
                                tension: 0.35,
                                borderRadius: (chartType === 'bar' ? 6 : 0)
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: (chartType === 'pie' || chartType === 'doughnut'),
                                    labels: { color: '#94a3b8', font: { size: 11 } }
                                },
                                title: {
                                    display: !!cData.title,
                                    text: cData.title || '',
                                    color: '#f1f5f9',
                                    font: { size: 13, weight: 'bold' }
                                }
                            },
                            scales: (chartType === 'pie' || chartType === 'doughnut') ? {} : {
                                x: {
                                    ticks: { color: '#94a3b8', font: { size: 10 } },
                                    grid: { color: 'rgba(255, 255, 255, 0.05)' }
                                },
                                y: {
                                    beginAtZero: true,
                                    ticks: { color: '#94a3b8', font: { size: 10 } },
                                    grid: { color: 'rgba(255, 255, 255, 0.05)' }
                                }
                            }
                        }
                    });
                });
            }

            // Render riwayat chat yang sudah dimuat dari Blade
            $('.raw-ai-text[data-rendered="false"]').each(function() {
                let rawText = $(this).text();
                let res = renderAiContent(rawText);
                let parent = $(this).parent();
                $(this).replaceWith(res.html);
                instantiateCharts(res.charts);
            });

            // Quick Prompt Chips
            $('.quick-chip').click(function() {
                let prompt = $(this).data('prompt');
                $('#user-input').val(prompt);
                $('#chat-form').submit();
            });

            // Submit Form Chat
            $('#chat-form').submit(function(e) {
                e.preventDefault();
                
                let message = $('#user-input').val().trim();
                if (!message) return;

                let nowTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

                // Render Bubble User (WhatsApp Style Green)
                $('#chat-box').append(`
                    <div class="flex items-start justify-end">
                        <div class="bg-[#005c4b] text-white p-3 rounded-2xl rounded-tr-none max-w-[90%] sm:max-w-[78%] text-sm shadow-md break-words">
                            <div class="whitespace-pre-wrap">${$('<div>').text(message).html()}</div>
                            <div class="flex items-center justify-end gap-1 mt-1 text-[10px] text-emerald-200/80">
                                <span>${nowTime}</span>
                                <svg class="w-3.5 h-3.5 text-blue-300 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7m-14 4l4 4L19 7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                `);

                $('#user-input').val('');
                scrollToBottom(true);

                // Indikator Loading Bouncing Dots
                let loadingId = 'loading-' + Date.now();
                $('#chat-box').append(`
                    <div id="${loadingId}" class="flex items-start gap-2.5 max-w-[90%] sm:max-w-[80%]">
                        <div class="w-7 h-7 rounded-full bg-emerald-700 flex items-center justify-center text-xs shrink-0 text-white shadow">🤖</div>
                        <div class="bg-[#202c33] text-slate-300 px-4 py-3 rounded-2xl rounded-tl-none text-xs border border-[#2a3942] flex items-center gap-2">
                            <span>MTN-AI sedang berpikir & menganalisis</span>
                            <div class="flex gap-1 items-center ml-1">
                                <span class="typing-dot"></span>
                                <span class="typing-dot"></span>
                                <span class="typing-dot"></span>
                            </div>
                        </div>
                    </div>
                `);
                scrollToBottom(true);

                // Disable input saat proses
                $('#user-input').prop('disabled', true);
                $('#send-btn').prop('disabled', true);

                // Kirim AJAX ke Backend
                $.ajax({
                    url: "{{ route('assistant.chat') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        message: message
                    },
                    timeout: 60000,
                    success: function(response) {
                        $('#' + loadingId).remove();
                        $('#user-input').prop('disabled', false).focus();
                        $('#send-btn').prop('disabled', false);

                        let parsedResult = renderAiContent(response.reply);

                        $('#chat-box').append(`
                            <div class="flex items-start gap-2.5 max-w-[92%] sm:max-w-[85%]">
                                <div class="w-7 h-7 rounded-full bg-emerald-700 flex items-center justify-center text-xs shrink-0 text-white shadow">🤖</div>
                                <div class="bg-[#202c33] text-slate-100 p-3.5 rounded-2xl rounded-tl-none shadow-md text-sm leading-relaxed border border-[#2a3942]/50 ai-content overflow-x-auto w-full">
                                    ${parsedResult.html}
                                    <span class="block text-right text-[10px] text-slate-400 mt-1">${nowTime}</span>
                                </div>
                            </div>
                        `);

                        instantiateCharts(parsedResult.charts);
                        scrollToBottom(true);
                    },
                    error: function(xhr) {
                        $('#' + loadingId).remove();
                        $('#user-input').prop('disabled', false).focus();
                        $('#send-btn').prop('disabled', false);

                        let errMsg = xhr.responseJSON && xhr.responseJSON.reply ? xhr.responseJSON.reply : 'Koneksi ke AI Gateway terputus atau timed out. Silakan coba lagi.';
                        $('#chat-box').append(`
                            <div class="flex items-start gap-2.5 max-w-[90%] sm:max-w-[80%]">
                                <div class="w-7 h-7 rounded-full bg-rose-700 flex items-center justify-center text-xs shrink-0 text-white shadow">⚠️</div>
                                <div class="bg-rose-950/80 text-rose-200 border border-rose-800 p-3.5 rounded-2xl rounded-tl-none text-xs leading-relaxed">
                                    ${errMsg}
                                </div>
                            </div>
                        `);
                        scrollToBottom(true);
                    }
                });
            });

            // Tombol Hapus Riwayat Chat
            $('#btn-clear-chat').click(function() {
                if (!confirm('Apakah Anda yakin ingin menghapus seluruh riwayat percakapan Smart Assistant Anda? (Tindakan ini tidak dapat dibatalkan)')) {
                    return;
                }

                $.ajax({
                    url: "{{ route('assistant.clear') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function() {
                        // Bersihkan chat box dan kembalikan hanya pesan pembuka
                        $('#chat-box').html(`
                            <div class="flex justify-center my-2">
                                <span class="bg-[#182229] text-slate-400 text-[11px] font-medium px-3 py-1 rounded-full shadow-sm border border-[#222e35]">
                                    Riwayat obrolan berhasil dibersihkan
                                </span>
                            </div>
                            <div class="flex items-start gap-2.5 max-w-[92%] sm:max-w-[80%]">
                                <div class="w-7 h-7 rounded-full bg-emerald-700 flex items-center justify-center text-xs shrink-0 text-white shadow">🤖</div>
                                <div class="bg-[#202c33] text-slate-100 p-3.5 rounded-2xl rounded-tl-none shadow-md text-sm leading-relaxed border border-[#2a3942]/50 ai-content">
                                    <p class="font-medium text-emerald-400 mb-1">Riwayat obrolan Anda telah di-reset! 🔄</p>
                                    <p>Ada data atau analisis maintenance baru yang ingin Anda diskusikan sekarang?</p>
                                    <span class="block text-right text-[10px] text-slate-400 mt-1">Baru saja</span>
                                </div>
                            </div>
                        `);
                        scrollToBottom();
                    },
                    error: function() {
                        alert('Gagal membersihkan riwayat obrolan.');
                    }
                });
            });

        });
    </script>
</x-app-layout>
