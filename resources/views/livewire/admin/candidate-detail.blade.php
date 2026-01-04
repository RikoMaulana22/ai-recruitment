<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        {{-- Tombol Kembali --}}
        <div class="mb-6">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center text-gray-500 hover:text-gray-900 font-medium transition duration-150 ease-in-out">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Dashboard
            </a>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

            {{-- HEADER: Nama & Status --}}
            <div class="flex flex-col md:flex-row justify-between items-start border-b pb-6 mb-6 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $candidate->name }}</h1>
                    <p class="text-gray-500 flex items-center gap-2 mt-1">
                        <span>📧 {{ $candidate->email }}</span>
                        <span class="text-gray-300">•</span>
                        <span>📱 {{ $candidate->phone ?? '-' }}</span>
                    </p>
                </div>
                <div class="text-right flex flex-col items-end">
                    {{-- Badge Skor --}}
                    @php
                        $score = $candidate->score ?? 0;
                        $scoreColor = $score >= 70 ? 'bg-green-100 text-green-800 border-green-200' : ($score > 40 ? 'bg-yellow-100 text-yellow-800 border-yellow-200' : 'bg-red-100 text-red-800 border-red-200');
                    @endphp
                    <span class="px-4 py-2 rounded-lg text-lg font-bold shadow-sm border {{ $scoreColor }}">
                        Skor AI: {{ $score }}/100
                    </span>
                    
                    {{-- Badge Status --}}
                    <div class="mt-2 text-sm text-gray-500 font-medium bg-gray-50 px-3 py-1 rounded-full border">
                        Status: <span class="uppercase tracking-wider">{{ $candidate->status }}</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                {{-- KOLOM KIRI (2/3): Analisis & Detail --}}
                <div class="md:col-span-2 space-y-6">
                    
                    {{-- 🤖 BAGIAN ANALISIS AI --}}
                    <div class="bg-white rounded-xl border border-indigo-100 shadow-sm overflow-hidden">
                        <div class="bg-indigo-50 px-6 py-4 border-b border-indigo-100 flex justify-between items-center">
                            <h3 class="text-lg font-bold text-indigo-900 flex items-center gap-2">
                                🤖 Analisis HR Assistant
                            </h3>
                            @if(!$candidate->ai_analysis && $candidate->status == 'pending')
                                <span class="text-xs text-indigo-600 animate-pulse font-semibold">Sedang Menganalisis...</span>
                            @endif
                        </div>

                        <div class="p-6">
                            @if($candidate->ai_analysis)
                                @php $analysis = $candidate->ai_analysis; @endphp

                                {{-- Ringkasan & Rekomendasi --}}
                                <div class="mb-6 bg-blue-50/50 rounded-lg p-4 border border-blue-100">
                                    <p class="text-gray-800 italic mb-3">"{{ $analysis['summary'] ?? $candidate->ai_summary ?? 'Tidak ada ringkasan.' }}"</p>
                                    
                                    @if(isset($analysis['recommendation']))
                                        <div class="flex items-start gap-2 mt-2">
                                            <span class="font-bold text-blue-800 whitespace-nowrap">💡 Rekomendasi:</span>
                                            <span class="font-medium text-blue-900">{{ $analysis['recommendation'] }}</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Grid Pros & Cons --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    {{-- Kelebihan --}}
                                    <div>
                                        <h4 class="font-semibold text-green-700 flex items-center mb-3 bg-green-50 px-3 py-1 rounded-md w-fit">
                                            ✅ Kelebihan (Pros)
                                        </h4>
                                        <ul class="space-y-2">
                                            @forelse($analysis['pros'] ?? [] as $pro)
                                                <li class="flex items-start gap-2 text-sm text-gray-700">
                                                    <span class="text-green-500 mt-0.5">•</span>
                                                    {{ $pro }}
                                                </li>
                                            @empty
                                                <li class="text-gray-400 text-sm italic">Tidak ada data spesifik.</li>
                                            @endforelse
                                        </ul>
                                    </div>

                                    {{-- Kekurangan --}}
                                    <div>
                                        <h4 class="font-semibold text-red-700 flex items-center mb-3 bg-red-50 px-3 py-1 rounded-md w-fit">
                                            ⚠️ Pertimbangan (Cons)
                                        </h4>
                                        <ul class="space-y-2">
                                            @forelse($analysis['cons'] ?? [] as $con)
                                                <li class="flex items-start gap-2 text-sm text-gray-700">
                                                    <span class="text-red-500 mt-0.5">•</span>
                                                    {{ $con }}
                                                </li>
                                            @empty
                                                <li class="text-gray-400 text-sm italic">Tidak ada data spesifik.</li>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>

                            @elseif($candidate->status == 'pending')
                                <div class="text-center py-10">
                                    <span class="loading loading-spinner loading-lg text-indigo-500 mb-2"></span>
                                    <p class="text-gray-500">Gemini sedang membaca CV...</p>
                                </div>
                            @else
                                <div class="text-center py-6 text-gray-400 italic">
                                    Belum ada hasil analisis.
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Skill (Opsional, jika ada di data) --}}
                    @if(isset($candidate->ai_analysis['skills']) && count($candidate->ai_analysis['skills']) > 0)
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">🛠 Skill Terdeteksi</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($candidate->ai_analysis['skills'] as $skill)
                                <span class="px-3 py-1 bg-gray-100 text-gray-700 border border-gray-200 rounded-full text-xs font-medium">
                                    {{ $skill }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Teks Asli --}}
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">📄 Teks Asli CV</h3>
                        <textarea readonly class="w-full h-40 text-xs text-gray-500 border-gray-300 rounded-lg bg-gray-50 p-3 focus:ring-0 resize-none font-mono">{{ $candidate->resume_text }}</textarea>
                    </div>
                </div>

                {{-- KOLOM KANAN (1/3): Aksi & File --}}
                <div class="space-y-6">
                    <div class="bg-gray-50 p-6 rounded-xl border border-gray-200 text-center sticky top-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Aksi Lanjutan</h3>

                        {{-- Tombol PDF --}}
                        @if($candidate->resume_path)
                            <a href="{{ asset('storage/' . $candidate->resume_path) }}" target="_blank"
                               class="flex items-center justify-center gap-2 w-full mb-4 bg-white border border-gray-300 text-gray-700 font-bold py-2.5 px-4 rounded-lg hover:bg-gray-100 transition shadow-sm group">
                                <svg class="w-5 h-5 text-red-500 group-hover:scale-110 transition" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"></path></svg>
                                Lihat File PDF
                            </a>
                        @endif

                        {{-- Tombol Link Interview --}}
                        <div class="space-y-3">
                            <label class="text-xs text-gray-500 font-bold uppercase tracking-wide">Test Interview</label>
                            
                            <div class="flex gap-2">
                                <button onclick="navigator.clipboard.writeText('{{ route('interview.start', $candidate->id) }}'); alert('Link berhasil disalin!');" 
                                    class="flex-1 bg-white border border-gray-300 text-gray-700 font-bold py-2 px-3 rounded-lg hover:bg-gray-50 transition text-sm flex justify-center items-center gap-1">
                                    📋 Salin
                                </button>

                                <a href="{{ route('interview.start', $candidate->id) }}" target="_blank" 
                                   class="flex-1 flex items-center justify-center gap-1 bg-indigo-600 text-white font-bold py-2 px-3 rounded-lg hover:bg-indigo-700 shadow-md transition text-sm">
                                    ▶️ Buka
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- BAGIAN VIDEO INTERVIEW (Realtime Poll) --}}
            <div class="mt-12 border-t pt-8" wire:poll.5s>
                <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
                    <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        🎬 Hasil Video Interview
                        @if($candidate->interview)
                            <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full border border-green-200">Selesai</span>
                        @endif
                    </h2>
                    
                    {{-- Tombol Paksa Nilai Ulang --}}
                    @if($candidate->interview)
                        <button wire:click="regrade({{ $candidate->interview->id }})" 
                                wire:loading.attr="disabled"
                                class="text-sm text-yellow-600 hover:text-yellow-700 underline font-medium flex items-center gap-1 disabled:opacity-50">
                            <span wire:loading.remove wire:target="regrade">⚡ Paksa Analisis Ulang Video</span>
                            <span wire:loading wire:target="regrade">⏳ Memproses...</span>
                        </button>
                    @endif
                </div>

                @if($candidate->interview)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @php
                            $videoLabels = [
                                1 => '1. Perkenalan & Pengalaman',
                                2 => '2. Pencapaian & Kegagalan',
                                3 => '3. Motivasi Gabung'
                            ];
                        @endphp

                        @foreach($videoLabels as $index => $label)
                            @php $videoPath = $candidate->interview->{'video_answer_'.$index}; @endphp

                            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col h-full">
                                <h3 class="font-bold text-gray-800 mb-3 text-sm border-b pb-2 truncate" title="{{ $label }}">
                                    {{ $label }}
                                </h3>

                                @if($videoPath)
                                    <div class="relative bg-black rounded-lg overflow-hidden aspect-video mb-3">
                                        <video controls class="w-full h-full object-contain">
                                            <source src="{{ asset('storage/' . $videoPath) }}" type="video/webm">
                                            <source src="{{ asset('storage/' . $videoPath) }}" type="video/mp4">
                                            Browser tidak support video.
                                        </video>
                                    </div>
                                    <div class="mt-auto text-right">
                                        <a href="{{ asset('storage/' . $videoPath) }}" target="_blank" class="text-indigo-600 text-xs font-bold hover:underline">
                                            ⬇️ Download Video
                                        </a>
                                    </div>
                                @else
                                    <div class="flex-1 flex flex-col items-center justify-center bg-gray-50 rounded-lg border border-dashed border-gray-300 min-h-[150px]">
                                        <span class="text-2xl opacity-20">📹</span>
                                        <p class="text-xs text-gray-400 mt-2">Tidak ada rekaman</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 bg-gray-50 border border-gray-200 border-dashed rounded-xl text-center">
                        <p class="text-gray-500 font-medium">Kandidat belum melakukan sesi interview video.</p>
                        <p class="text-sm text-gray-400 mt-1">Silakan bagikan link interview kepada kandidat.</p>
                    </div>
                @endif
            </div>

            {{-- BAGIAN KEPUTUSAN AKHIR --}}
            <div class="mt-10 border-t pt-8 pb-4">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">⚖️ Keputusan Akhir</h2>

                @if (session()->has('message'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6 flex items-center gap-2">
                        ✅ {{ session('message') }}
                    </div>
                @endif

                @if($candidate->status == 'accepted')
                    <div class="bg-green-50 border border-green-200 rounded-xl p-8 text-center shadow-sm">
                        <div class="text-5xl mb-4">🎉</div>
                        <h3 class="text-2xl font-bold text-green-800">Kandidat Diterima</h3>
                        <p class="text-green-600 mt-2 font-medium">Kandidat ini telah lolos seleksi.</p>
                    </div>
                @elseif($candidate->status == 'rejected')
                    <div class="bg-red-50 border border-red-200 rounded-xl p-8 text-center shadow-sm">
                        <div class="text-5xl mb-4">🚫</div>
                        <h3 class="text-2xl font-bold text-red-800">Kandidat Ditolak</h3>
                        <p class="text-red-600 mt-2 font-medium">Kandidat ini tidak memenuhi kriteria.</p>
                    </div>
                @else
                    <div class="bg-white p-8 rounded-xl border border-gray-200 shadow-sm text-center">
                        <p class="text-gray-600 mb-8 text-lg">Berdasarkan skor CV, analisis AI, dan hasil interview, apa keputusan Anda?</p>
                        
                        <div class="flex flex-col sm:flex-row gap-4 max-w-lg mx-auto">
                            <button wire:click="reject" 
                                    wire:confirm="Yakin ingin menolak kandidat ini?"
                                    class="flex-1 bg-white border-2 border-red-500 text-red-600 font-bold py-3 px-6 rounded-xl hover:bg-red-50 transition flex justify-center items-center gap-2">
                                ❌ Tolak (Reject)
                            </button>

                            <button wire:click="accept" 
                                    wire:confirm="Yakin ingin menerima kandidat ini?"
                                    class="flex-1 bg-green-600 text-white font-bold py-3 px-6 rounded-xl hover:bg-green-700 shadow-lg transition transform hover:-translate-y-1 flex justify-center items-center gap-2">
                                ✅ Terima (Hire)
                            </button>
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>