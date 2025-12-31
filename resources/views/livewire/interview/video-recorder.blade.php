<div class="min-h-screen bg-gray-900 flex flex-col items-center justify-center p-4 text-white">

    {{-- Step indicator (ikut kecil) --}}
    <div class="w-full max-w-[220px] mb-3 flex items-center justify-between text-[10px] text-gray-400 font-bold tracking-widest uppercase">
        <span>Step {{ $currentStep }} / 3</span>
        <div class="flex gap-1">
            <div class="w-5 h-1 rounded-full {{ $currentStep >= 1 ? 'bg-blue-500' : 'bg-gray-700' }}"></div>
            <div class="w-5 h-1 rounded-full {{ $currentStep >= 2 ? 'bg-blue-500' : 'bg-gray-700' }}"></div>
            <div class="w-5 h-1 rounded-full {{ $currentStep >= 3 ? 'bg-blue-500' : 'bg-gray-700' }}"></div>
        </div>
    </div>

    {{-- Pertanyaan (ikut kecil) --}}
    <div class="w-full max-w-[220px] text-center mb-4 px-1">
        <h1 class="text-[11px] font-bold mb-1 text-gray-400">Pertanyaan #{{ $currentStep }}</h1>
        <p class="text-sm text-white font-semibold leading-snug">"{{ $questions[$currentStep] }}"</p>
    </div>

    {{-- Camera box (dikunci kecil + ignore) --}}
    <div class="relative w-[320px] bg-black rounded-xl overflow-hidden shadow-2xl border border-gray-700" style="aspect-ratio: 16/9;">
        
        <video id="preview" autoplay muted class="w-full h-full object-cover transform scale-x-[-1]"></video>
        
        <video id="playback" controls class="w-full h-full object-cover hidden"></video>

        <div wire:loading wire:target="videoFile" class="absolute inset-0 bg-black/90 flex flex-col items-center justify-center z-50">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500 mb-2"></div>
            <p class="text-xs text-blue-400">Upload...</p>
        </div>

        <div id="recIndicator" class="hidden absolute top-2 right-2 flex items-center gap-1.5 bg-red-600/90 px-1.5 py-0.5 rounded backdrop-blur-sm">
            <div class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></div>
            <span class="text-[8px] font-bold uppercase tracking-wider">REC</span>
        </div>
    </div>

    {{-- Buttons (ikut kecil) --}}
    <div class="mt-4 flex flex-wrap gap-2 justify-center w-full max-w-[220px]">
        <button id="btnStart"
            class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-full flex items-center gap-2 transition shadow-lg shadow-red-900/30 text-[10px] uppercase tracking-wide">
            <div class="w-2 h-2 bg-white rounded-full"></div> Mulai
        </button>

        <button id="btnStop"
            class="hidden bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-full border border-red-500/30 animate-pulse text-[10px] uppercase tracking-wide">
            Stop
        </button>

        <button id="btnRetry"
            class="hidden text-gray-400 hover:text-white font-medium py-2 px-2 text-[10px] transition">
            Ulangi
        </button>

        <button id="btnSubmit"
            class="hidden bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-5 rounded-full flex items-center gap-1 transition shadow-lg shadow-blue-900/30 text-[10px] uppercase tracking-wide">
            Lanjut →
        </button>
    </div>

    <input type="file" wire:model="videoFile" id="videoInput" class="hidden" accept="video/*">
</div>

<script>
    let mediaRecorder;
    let recordedChunks = [];
    const preview = document.getElementById('preview');
    const playback = document.getElementById('playback');
    const btnStart = document.getElementById('btnStart');
    const btnStop = document.getElementById('btnStop');
    const btnRetry = document.getElementById('btnRetry');
    const btnSubmit = document.getElementById('btnSubmit');
    const videoInput = document.getElementById('videoInput');
    const recIndicator = document.getElementById('recIndicator');

    async function initCamera() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
            preview.srcObject = stream;
        } catch (err) {
            alert("Wajib izinkan kamera.");
        }
    }
    initCamera();

    btnStart.onclick = () => {
        recordedChunks = [];
        const stream = preview.srcObject;
        mediaRecorder = new MediaRecorder(stream);

        mediaRecorder.ondataavailable = event => {
            if (event.data.size > 0) recordedChunks.push(event.data);
        };

        mediaRecorder.onstop = () => {
            const blob = new Blob(recordedChunks, { type: 'video/webm' });
            const url = URL.createObjectURL(blob);

            preview.classList.add('hidden');
            playback.classList.remove('hidden');
            playback.src = url;

            const file = new File([blob], "jawaban.webm", { type: "video/webm" });
            const dt = new DataTransfer();
            dt.items.add(file);
            videoInput.files = dt.files;
            videoInput.dispatchEvent(new Event('change', { bubbles: true }));

            btnStop.classList.add('hidden');
            recIndicator.classList.add('hidden');
            btnSubmit.classList.remove('hidden');
            btnRetry.classList.remove('hidden');
        };

        mediaRecorder.start();
        btnStart.classList.add('hidden');
        btnStop.classList.remove('hidden');
        recIndicator.classList.remove('hidden');
    };

    btnStop.onclick = () => { mediaRecorder?.stop(); };

    btnRetry.onclick = () => {
        playback.classList.add('hidden');
        preview.classList.remove('hidden');
        btnSubmit.classList.add('hidden');
        btnRetry.classList.add('hidden');
        btnStart.classList.remove('hidden');
        recIndicator.classList.add('hidden');

        playback.pause();
        playback.src = '';
    };

    btnSubmit.onclick = () => {
        @this.saveVideo();
        // UI reset optional (kalau mau nunggu sukses, pindahkan reset ini ke event sukses dari Livewire)
        playback.classList.add('hidden');
        preview.classList.remove('hidden');
        btnSubmit.classList.add('hidden');
        btnRetry.classList.add('hidden');
        btnStart.classList.remove('hidden');
        recIndicator.classList.add('hidden');
    };
</script>
