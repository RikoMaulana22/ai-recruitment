<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <div class="mb-6">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center text-gray-500 hover:text-gray-900 font-medium transition duration-150 ease-in-out">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Dashboard
            </a>
        </div>
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

            <div class="flex justify-between items-start border-b pb-6 mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $candidate->name }}</h1>
                    <p class="text-gray-500">{{ $candidate->email }} • {{ $candidate->phone }}</p>
                </div>
                <div class="text-right">
                    <span class="px-4 py-2 rounded-lg text-lg font-bold 
                        {{ $candidate->score >= 70 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        Skor AI: {{ $candidate->score }}/100
                    </span>
                    <div class="mt-2 text-sm text-gray-500">Status: {{ strtoupper($candidate->status) }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">🤖 Ringkasan AI</h3>
                        <div class="bg-blue-50 p-4 rounded-lg text-blue-900 leading-relaxed border border-blue-100">
                            {{ $candidate->ai_summary ?? 'Belum ada ringkasan.' }}
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">🛠 Skill Terdeteksi</h3>
                        <div class="flex flex-wrap gap-2">
                            @if(isset($candidate->ai_analysis['skills']) && is_array($candidate->ai_analysis['skills']))
                                @foreach($candidate->ai_analysis['skills'] as $skill)
                                    <span class="px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-sm">
                                        {{ $skill }}
                                    </span>
                                @endforeach
                            @else
                                <span class="text-gray-400 italic">Tidak ada data skill.</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">📄 Teks Asli CV</h3>
                        <textarea readonly
                            class="w-full h-40 text-xs text-gray-500 border rounded bg-gray-50 p-2">{{ $candidate->resume_text }}</textarea>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 text-center">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Aksi Lanjutan</h3>

                        <a href="{{ asset('storage/' . $candidate->resume_path) }}" target="_blank"
                            class="block w-full mb-3 bg-white border border-gray-300 text-gray-700 font-bold py-2 px-4 rounded hover:bg-gray-50">
                            📄 Lihat File PDF Asli
                        </a>

                        <div class="flex gap-2">
                            <button onclick="navigator.clipboard.writeText('{{ route('interview.start', $candidate->id) }}'); alert('Link berhasil disalin! Kirimkan ke kandidat.');" 
                                class="flex-1 bg-gray-100 text-gray-700 font-bold py-3 px-4 rounded hover:bg-gray-200 border border-gray-300">
                                🔗 Salin Link
                            </button>

                            <a href="{{ route('interview.start', $candidate->id) }}" target="_blank" 
                               class="flex-1 text-center bg-indigo-600 text-white font-bold py-3 px-4 rounded hover:bg-indigo-700 shadow-lg">
                               ▶️ Test Interview
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 border-t pt-8">
    <h2 class="text-2xl font-bold text-gray-900 mb-6">🎬 Hasil Video Interview</h2>

    @if($candidate->interview)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @php
                $labels = [
                    1 => 'Perkenalan & Pengalaman',
                    2 => 'Pencapaian & Kegagalan',
                    3 => 'Motivasi Gabung'
                ];
            @endphp

            @foreach($labels as $step => $label)
                @php $videoField = 'video_answer_' . $step; @endphp
                
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 shadow-sm">
                    <h3 class="font-bold text-gray-700 mb-3 text-sm border-b pb-2">{{ $label }}</h3>
                    
                    @if(!empty($candidate->interview->$videoField))
                        <div class="rounded-lg overflow-hidden bg-black aspect-video relative group">
                            <video controls class="w-full h-full object-cover">
                                <source src="{{ asset('storage/' . $candidate->interview->$videoField) }}" type="video/webm">
                                <source src="{{ asset('storage/' . $candidate->interview->$videoField) }}" type="video/mp4">
                                Browser Anda tidak mendukung pemutar video.
                            </video>
                        </div>
                        <div class="mt-2 text-xs text-green-600 font-bold flex items-center gap-1">
                            ✅ Video Terkirim
                        </div>
                    @else
                        <div class="aspect-video bg-gray-200 rounded-lg flex flex-col items-center justify-center text-gray-400">
                            <svg class="w-10 h-10 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            <span class="text-xs font-semibold">Belum ada rekaman</span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-100 text-blue-800 text-sm">
            <strong>Catatan:</strong> Fitur analisis AI otomatis untuk video sedang dalam pengembangan. Saat ini Anda bisa menilai manual dengan menonton video di atas.
        </div>

    @else
        <div class="bg-yellow-50 text-yellow-800 p-4 rounded-lg border border-yellow-200 flex items-center gap-3">
            <span class="text-2xl">⏳</span>
            <div>
                <strong>Belum ada data interview.</strong>
                <p class="text-sm">Kandidat ini belum memulai atau menyelesaikan sesi perekaman video.</p>
            </div>
        </div>
    @endif
</div>

            <div class="mt-8 border-t pt-8 pb-10">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">⚖️ Keputusan Akhir</h2>

                @if (session()->has('message'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                        {{ session('message') }}
                    </div>
                @endif

                @if($candidate->status == 'accepted')
                    <div class="bg-green-50 border border-green-200 rounded-xl p-8 text-center">
                        <div class="text-5xl mb-4">🎉</div>
                        <h3 class="text-2xl font-bold text-green-800">Kandidat Diterima</h3>
                        <p class="text-green-600">Kandidat ini telah lolos seleksi. Silakan hubungi untuk penawaran kontrak.</p>
                    </div>
                @elseif($candidate->status == 'rejected')
                    <div class="bg-red-50 border border-red-200 rounded-xl p-8 text-center">
                        <div class="text-5xl mb-4">🚫</div>
                        <h3 class="text-2xl font-bold text-red-800">Kandidat Ditolak</h3>
                        <p class="text-red-600">Kandidat ini tidak memenuhi kriteria yang dicari.</p>
                    </div>
                @else
                    <div class="bg-gray-50 p-6 rounded-xl border border-gray-200">
                        <p class="text-gray-600 mb-4">Berdasarkan skor CV dan hasil interview, apa keputusan Anda untuk kandidat ini?</p>
                        
                        <div class="flex gap-4">
                            <button wire:click="reject" 
                                    wire:confirm="Yakin ingin menolak kandidat ini?"
                                    class="flex-1 bg-white border-2 border-red-500 text-red-600 font-bold py-3 px-4 rounded-lg hover:bg-red-50 transition">
                                ❌ Tolak (Reject)
                            </button>

                            <button wire:click="accept" 
                                    wire:confirm="Yakin ingin menerima kandidat ini?"
                                    class="flex-1 bg-green-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-green-700 shadow-md transition transform hover:-translate-y-1">
                                ✅ Terima (Hire)
                            </button>
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>