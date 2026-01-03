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

            <div class="flex flex-col md:flex-row justify-between items-start border-b pb-6 mb-6 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $candidate->name }}</h1>
                    <p class="text-gray-500 flex items-center gap-2 mt-1">
                        <span>📧 {{ $candidate->email }}</span>
                        <span>•</span>
                        <span>📱 {{ $candidate->phone ?? '-' }}</span>
                    </p>
                </div>
                <div class="text-right flex flex-col items-end">
                    <span class="px-4 py-2 rounded-lg text-lg font-bold shadow-sm 
                        {{ $candidate->score >= 70 ? 'bg-green-100 text-green-800' : ($candidate->score > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                        Skor AI: {{ $candidate->score }}/100
                    </span>
                    <div class="mt-2 text-sm text-gray-500 font-medium bg-gray-50 px-3 py-1 rounded-full border">
                        Status: {{ strtoupper($candidate->status) }}
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2 flex items-center gap-2">
                            🤖 Ringkasan AI
                        </h3>
                        <div class="bg-blue-50 p-4 rounded-lg text-blue-900 leading-relaxed border border-blue-100 text-sm whitespace-pre-line">
                            {{ $candidate->ai_summary ?? 'Belum ada ringkasan dari AI.' }}
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">🛠 Skill Terdeteksi</h3>
                        <div class="flex flex-wrap gap-2">
                            @if(isset($candidate->ai_analysis['skills']) && is_array($candidate->ai_analysis['skills']) && count($candidate->ai_analysis['skills']) > 0)
                                @foreach($candidate->ai_analysis['skills'] as $skill)
                                    <span class="px-3 py-1 bg-gray-100 text-gray-700 border border-gray-200 rounded-full text-xs font-medium">
                                        {{ $skill }}
                                    </span>
                                @endforeach
                            @else
                                <span class="text-gray-400 italic text-sm">Tidak ada data skill spesifik.</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">📄 Teks Asli CV</h3>
                        <textarea readonly class="w-full h-40 text-xs text-gray-500 border-gray-300 rounded-lg bg-gray-50 p-3 focus:ring-0 focus:border-gray-300">{{ $candidate->resume_text }}</textarea>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-gray-50 p-6 rounded-xl border border-gray-200 text-center">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Aksi Lanjutan</h3>

                        @if($candidate->resume_path)
                            <a href="{{ asset('ai-recruitment/storage/app/livewire-temp/' . $candidate->resume_path) }}" target="_blank"
                                class="flex items-center justify-center gap-2 w-full mb-3 bg-white border border-gray-300 text-gray-700 font-bold py-2 px-4 rounded-lg hover:bg-gray-100 transition shadow-sm">
                                📄 Lihat File PDF Asli
                            </a>
                        @endif

                        <div class="flex flex-col gap-2 mt-4">
                            <div class="flex gap-2">
                                <button onclick="navigator.clipboard.writeText('{{ route('interview.start', $candidate->id) }}'); alert('Link berhasil disalin! Kirimkan ke kandidat.');" 
                                    class="flex-1 bg-white border border-gray-300 text-gray-700 font-bold py-2 px-4 rounded-lg hover:bg-gray-50 transition text-sm">
                                    🔗 Salin Link
                                </button>

                                <a href="{{ route('interview.start', $candidate->id) }}" target="_blank" 
                                   class="flex-1 flex items-center justify-center gap-1 bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-indigo-700 shadow-md transition text-sm">
                                    ▶️ Test Interview
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-10 border-t pt-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">🎬 Hasil Video Interview</h2>
                    
                    @if($candidate->interview)
                        <button wire:click="regrade({{ $candidate->interview->id }})" 
                                wire:loading.attr="disabled"
                                class="bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-bold px-4 py-2 rounded-lg shadow transition flex items-center gap-2">
                            <span wire:loading.remove>⚡ Paksa Nilai Ulang AI</span>
                            <span wire:loading>Memproses...</span>
                        </button>
                    @endif
                </div>

                <div class="mt-6 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-800 mb-3 flex items-center">
                        🎥 Jawaban Video Interview
                    </h3>

                    @if($candidate->interview && $candidate->interview->video_answer_1)
                        {{-- Debugging: Tampilkan path agar kita tahu apa yang tersimpan --}}
                        <p class="text-xs text-gray-400 mb-2">Path Database: {{ $candidate->interview->video_answer_1 }}</p>

                        <video controls class="w-full rounded-lg bg-black shadow-lg" style="max-height: 400px;">
                            {{-- PENTING: Tambahkan 'storage/' di depan path dari database --}}
                            <source src="{{ asset('ai-recruitment/storage/app/livewire-temp/' . $candidate->interview->video_answer_1) }}" type="video/webm">
                            <source src="{{ asset('ai-recruitment/storage/app/livewire-temp/' . $candidate->interview->video_answer_1) }}" type="video/mp4">
                            Maaf, browser Anda tidak mendukung pemutaran video ini.
                        </video>
                        
                        {{-- Tombol Download Alternatif --}}
                        <div class="mt-3 text-right">
                            <a href="{{ asset('ai-recruitment/storage/app/livewire-temp/' . $candidate->interview->video_answer_1) }}" target="_blank" class="text-indigo-600 text-sm font-semibold hover:underline">
                                ⬇️ Download / Tonton di Tab Baru
                            </a>
                        </div>
                    @else
                        <div class="p-6 bg-red-50 border border-red-100 rounded-lg text-center text-red-600">
                            <svg class="w-10 h-10 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                            <p class="font-bold">Kandidat belum merekam video.</p>
                        </div>
                    @endif
                </div>
                </div>

            <div class="mt-10 border-t pt-8 pb-4">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">⚖️ Keputusan Akhir</h2>

                @if (session()->has('message'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        {{ session('message') }}
                    </div>
                @endif

                @if($candidate->status == 'accepted')
                    <div class="bg-green-50 border border-green-200 rounded-xl p-8 text-center shadow-sm">
                        <div class="text-6xl mb-4">🎉</div>
                        <h3 class="text-2xl font-bold text-green-800">Kandidat Diterima</h3>
                        <p class="text-green-600 mt-2">Kandidat ini telah lolos seleksi. Silakan hubungi untuk penawaran kontrak.</p>
                    </div>
                @elseif($candidate->status == 'rejected')
                    <div class="bg-red-50 border border-red-200 rounded-xl p-8 text-center shadow-sm">
                        <div class="text-6xl mb-4">🚫</div>
                        <h3 class="text-2xl font-bold text-red-800">Kandidat Ditolak</h3>
                        <p class="text-red-600 mt-2">Kandidat ini tidak memenuhi kriteria yang dicari.</p>
                    </div>
                @else
                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                        <p class="text-gray-600 mb-6 text-center text-lg">Berdasarkan skor CV dan hasil interview, apa keputusan Anda?</p>
                        
                        <div class="flex gap-4 max-w-2xl mx-auto">
                            <button wire:click="reject" 
                                    wire:confirm="Yakin ingin menolak kandidat ini?"
                                    class="flex-1 bg-white border-2 border-red-500 text-red-600 font-bold py-4 px-6 rounded-xl hover:bg-red-50 transition flex justify-center items-center gap-2">
                                ❌ Tolak (Reject)
                            </button>

                            <button wire:click="accept" 
                                    wire:confirm="Yakin ingin menerima kandidat ini?"
                                    class="flex-1 bg-green-600 text-white font-bold py-4 px-6 rounded-xl hover:bg-green-700 shadow-lg transition transform hover:-translate-y-1 flex justify-center items-center gap-2">
                                ✅ Terima (Hire)
                            </button>
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>