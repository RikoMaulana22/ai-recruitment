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
                <h2 class="text-2xl font-bold text-gray-900 mb-6">🎤 Hasil AI Interview</h2>

                @if($candidate->interview)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-indigo-50 p-6 rounded-xl border border-indigo-100">
                            <div class="text-sm text-indigo-600 font-bold uppercase tracking-wider mb-1">Skor Bicara
                            </div>
                            <div class="text-4xl font-black text-indigo-900">
                                {{ $candidate->interview->interview_score ?? 0 }}<span
                                    class="text-lg text-indigo-400">/100</span></div>
                        </div>

                        <div class="col-span-2 bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                            <h3 class="font-bold text-gray-800 mb-2">Kesimpulan Wawancara:</h3>
                            <p class="text-gray-600 leading-relaxed">
                                {{ $candidate->interview->interview_summary ?? 'Menunggu hasil...' }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-6" x-data="{ open: false }">
                        <button @click="open = !open"
                            class="flex items-center justify-between w-full bg-gray-100 px-4 py-3 rounded-lg text-left font-semibold text-gray-700 hover:bg-gray-200 transition">
                            <span>📄 Lihat Transkrip Percakapan Lengkap</span>
                            <span x-show="!open">▼</span>
                            <span x-show="open">▲</span>
                        </button>

                        <div x-show="open"
                            class="mt-2 bg-white border border-gray-200 rounded-lg p-4 max-h-96 overflow-y-auto space-y-3">
                            @if(is_array($candidate->interview->chat_history))
                                @foreach($candidate->interview->chat_history as $msg)
                                    <div
                                        class="p-3 rounded-lg text-sm {{ $msg['role'] == 'assistant' ? 'bg-gray-50 text-gray-800' : 'bg-blue-50 text-blue-900 ml-8' }}">
                                        <span
                                            class="font-bold text-xs uppercase block mb-1 opacity-50">{{ $msg['role'] }}</span>
                                        {{ $msg['content'] }}
                                    </div>
                                @endforeach
                            @else
                                <p class="text-gray-400 italic">Format chat tidak valid.</p>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="bg-yellow-50 text-yellow-800 p-4 rounded-lg border border-yellow-200">
                        Kandidat belum melakukan sesi wawancara.
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