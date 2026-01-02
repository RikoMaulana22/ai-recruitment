<div class="min-h-screen bg-gray-900 flex flex-col items-center justify-center p-4 text-white">

    {{-- Indikator Step --}}
    <div class="w-full max-w-[250px] mb-4 flex items-center justify-between text-[10px] text-gray-400 font-bold tracking-widest uppercase">
        <span>Step {{ $currentStep }} / 3</span>
        <div class="flex gap-1">
            <div class="w-6 h-1 rounded-full {{ $currentStep >= 1 ? 'bg-blue-500' : 'bg-gray-700' }}"></div>
            <div class="w-6 h-1 rounded-full {{ $currentStep >= 2 ? 'bg-blue-500' : 'bg-gray-700' }}"></div>
            <div class="w-6 h-1 rounded-full {{ $currentStep >= 3 ? 'bg-blue-500' : 'bg-gray-700' }}"></div>
        </div>
    </div>

    {{-- Teks Pertanyaan --}}
    <div class="w-full max-w-[320px] text-center mb-6 px-2 min-h-[80px] flex flex-col justify-center">
        <h1 class="text-xs font-bold mb-2 text-blue-400 uppercase tracking-wider">Pertanyaan #{{ $currentStep }}</h1>
        <p class="text-base text-white font-medium leading-relaxed">
            "{{ $questions[$currentStep] }}"
        </p>
    </div>

    {{-- Container Kamera --}}
    <div class="relative w-[320px] bg-black rounded-2xl overflow-hidden shadow-2xl border border-gray-800" style="aspect-ratio: 16/9;">
        
        {{-- Viewfinder --}}
        <video id="preview" autoplay muted class="w-full h-full object-cover transform scale-x-[-1]"></video>
        
        {{-- Overlay: Loading Proses Otomatis --}}
        <div id="processingOverlay" class="hidden absolute inset-0 bg-black/90 flex flex-col items-center justify-center z-50">
            <div class="animate-spin rounded-full h-10 w-10 border-4 border-blue-500 border-t-transparent mb-4"></div>
            <p class="text-sm font-bold text-blue-400 animate-pulse">MEMPROSES JAWABAN...</p>
            <p class="text-xs text-gray-500 mt-2">Mohon tunggu, AI sedang menganalisis...</p>
        </div>

        {{-- Indikator REC --}}
        <div id="recIndicator" class="hidden absolute top-3 right-3 flex items-center gap-2 bg-red-600 px-2 py-1 rounded-md shadow-lg animate-pulse">
            <div class="w-2 h-2 bg-white rounded-full"></div>
            <span class="text-[10px] font-bold text-white uppercase tracking-wider">REC</span>
        </div>

        {{-- Timer (Opsional) --}}
        <div id="timer" class="hidden absolute bottom-3 left-3 text-xs font-mono font-bold text-white bg-black/50 px-2 py-1 rounded">
            00:00
        </div>
    </div>

    {{-- Tombol Kontrol --}}
    <div class="mt-8 flex justify-center w-full">
        
        {{-- Tombol MULAI --}}
        <button id="btnStart"
            class="bg-white text-gray-900 hover:bg-gray-200 font-bold py-3 px-8 rounded-full flex items-center gap-2 transition shadow-lg shadow-white/10 text-xs uppercase tracking-wider transform hover:scale-105">
            <div class="w-3 h-3 bg-red-600 rounded-full"></div> Mulai Jawab
        </button>

        {{-- Tombol STOP (Selesai) --}}
        <button id="btnStop"
            class="hidden bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-8 rounded-full border-4 border-red-800 shadow-xl text-xs uppercase tracking-wider transition transform active:scale-95">
            ⏹ Selesai & Lanjut
        </button>

    </div>

    {{-- Hidden Input untuk Livewire --}}
    <input type="file" wire:model="videoFile" id="videoInput" class="hidden">

</div>

<script>
    let mediaRecorder;
    let recordedChunks = [];
    let stream = null;
    let timerInterval;
    let startTime;

    const preview = document.getElementById('preview');
    const btnStart = document.getElementById('btnStart');
    const btnStop = document.getElementById('btnStop');
    const processingOverlay = document.getElementById('processingOverlay');
    const recIndicator = document.getElementById('recIndicator');
    const timerDisplay = document.getElementById('timer');

    // 1. Inisialisasi Kamera
    async function initCamera() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
            preview.srcObject = stream;
        } catch (err) {
            alert("Gagal akses kamera: " + err);
        }
    }
    initCamera();

    // 2. Tombol Mulai
    btnStart.onclick = () => {
        recordedChunks = [];
        
        // Codec detection agar tidak error di berbagai browser
        let options = { bitsPerSecond: 250000 }; // Low bitrate biar cepat
        if (MediaRecorder.isTypeSupported('video/webm;codecs=vp8,opus')) {
            options.mimeType = 'video/webm;codecs=vp8,opus';
        } else if (MediaRecorder.isTypeSupported('video/mp4')) {
            options.mimeType = 'video/mp4';
        }

        try {
            mediaRecorder = new MediaRecorder(stream, options);
        } catch (e) {
            mediaRecorder = new MediaRecorder(stream);
        }

        mediaRecorder.ondataavailable = event => {
            if (event.data.size > 0) recordedChunks.push(event.data);
        };

        // --- LOGIKA UTAMA: SAAT STOP, LANGSUNG UPLOAD ---
        mediaRecorder.onstop = () => {
            clearInterval(timerInterval);
            
            // 1. Tampilkan Overlay Loading "Memproses..."
            processingOverlay.classList.remove('hidden');
            btnStop.classList.add('hidden'); // Sembunyikan tombol
            
            // 2. Buat File Video
            const mimeType = mediaRecorder.mimeType || 'video/webm';
            const blob = new Blob(recordedChunks, { type: mimeType });
            const file = new File([blob], "jawaban.webm", { type: mimeType });

            // 3. UPLOAD LANGSUNG KE LIVEWIRE (Tanpa klik tombol lagi)
            // @this.upload adalah magic function dari Livewire
            @this.upload('videoFile', file, (uploadedFilename) => {
                // Sukses Upload -> Panggil PHP saveVideo()
                console.log("Upload selesai, menyimpan ke database...");
                @this.saveVideo();
            }, (error) => {
                // Error Upload
                alert("Gagal upload: " + error);
                processingOverlay.classList.add('hidden');
                btnStart.classList.remove('hidden');
            });
        };

        mediaRecorder.start();
        
        // Update UI
        startTimer();
        btnStart.classList.add('hidden');
        btnStop.classList.remove('hidden');
        recIndicator.classList.remove('hidden');
        timerDisplay.classList.remove('hidden');
    };

    // 3. Tombol Stop
    btnStop.onclick = () => {
        if(mediaRecorder && mediaRecorder.state !== 'inactive') {
            mediaRecorder.stop();
        }
    };

    // 4. Reset UI saat pindah soal (Sinyal dari PHP)
    document.addEventListener('livewire:initialized', () => {
        @this.on('step-complete', () => {
            console.log("Lanjut ke pertanyaan berikutnya...");
            
            // Reset UI
            processingOverlay.classList.add('hidden');
            preview.classList.remove('hidden');
            
            recIndicator.classList.add('hidden');
            timerDisplay.classList.add('hidden');
            
            // Munculkan tombol Mulai lagi untuk soal baru
            btnStart.classList.remove('hidden');
        });
    });

    // Helper: Timer sederhana
    function startTimer() {
        startTime = Date.now();
        timerInterval = setInterval(() => {
            let elapsed = Date.now() - startTime;
            let date = new Date(elapsed);
            let mm = date.getUTCMinutes().toString().padStart(2, '0');
            let ss = date.getUTCSeconds().toString().padStart(2, '0');
            timerDisplay.innerText = `${mm}:${ss}`;
        }, 1000);
    }
</script>

<script>
    let mediaRecorder;
    let recordedChunks = [];
    let stream = null;

    const preview = document.getElementById('preview');
    const playback = document.getElementById('playback');
    const btnStart = document.getElementById('btnStart');
    const btnStop = document.getElementById('btnStop');
    const actionButtons = document.getElementById('actionButtons');
    const btnRetry = document.getElementById('btnRetry');
    const btnSubmit = document.getElementById('btnSubmit');
    const videoInput = document.getElementById('videoInput');
    const recIndicator = document.getElementById('recIndicator');

    // 1. Inisialisasi Kamera
    async function initCamera() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
            preview.srcObject = stream;
            preview.muted = true; // Hindari feedback audio saat preview
        } catch (err) {
            alert("Gagal akses kamera: " + err);
        }
    }
    initCamera();

    // 2. Event Tombol Mulai
    btnStart.onclick = () => {
        recordedChunks = [];
        
        // Kita siapkan opsi bitrate agar file tetap kecil (250kbps)
        const commonOptions = { bitsPerSecond: 250000 };
        let options = {};

        // Cek format mana yang didukung browser ini
        if (MediaRecorder.isTypeSupported('video/webm;codecs=vp8,opus')) {
            // Format paling standar untuk Chrome/Edge (Video + Audio)
            options = { ...commonOptions, mimeType: 'video/webm;codecs=vp8,opus' };
        } else if (MediaRecorder.isTypeSupported('video/webm')) {
            // Format umum WebM
            options = { ...commonOptions, mimeType: 'video/webm' };
        } else if (MediaRecorder.isTypeSupported('video/mp4')) {
            // Format untuk Safari (MacOS/iPhone)
            options = { ...commonOptions, mimeType: 'video/mp4' };
        } else {
            // Fallback: Biarkan browser pakai default (biasanya file agak besar, tapi pasti jalan)
            console.warn("Browser tidak mendukung limit bitrate, menggunakan default.");
            options = {}; 
        }

        try {
            mediaRecorder = new MediaRecorder(stream, options);
        } catch (e) {
            // Jika masih gagal, paksa mode paling standar tanpa pengaturan apa-apa
            alert("Mode hemat data tidak didukung, beralih ke mode standar.");
            mediaRecorder = new MediaRecorder(stream);
        }

        mediaRecorder.ondataavailable = event => {
            if (event.data.size > 0) recordedChunks.push(event.data);
        };

        mediaRecorder.onstop = () => {
            // Tentukan tipe file berdasarkan apa yang dipakai browser
            const mimeType = mediaRecorder.mimeType || 'video/webm';
            const blob = new Blob(recordedChunks, { type: mimeType });
            const url = URL.createObjectURL(blob);

            // Tampilkan hasil rekaman
            preview.classList.add('hidden');
            playback.classList.remove('hidden');
            playback.src = url;
            
            // Siapkan file untuk upload
            // Kita beri nama ekstensi .webm secara default, server biasanya bisa membaca headernya
            const file = new File([blob], "jawaban.webm", { type: mimeType });
            const dt = new DataTransfer();
            dt.items.add(file);
            videoInput.files = dt.files;
            
            // Trigger upload ke Temporary Livewire
            videoInput.dispatchEvent(new Event('change', { bubbles: true }));

            // Update UI
            btnStop.classList.add('hidden');
            recIndicator.classList.add('hidden');
            actionButtons.classList.remove('hidden'); 
        };

        mediaRecorder.start();
        btnStart.classList.add('hidden');
        btnStop.classList.remove('hidden');
        recIndicator.classList.remove('hidden');
    };

    // 3. Event Tombol Stop
    btnStop.onclick = () => {
        if(mediaRecorder && mediaRecorder.state !== 'inactive') {
            mediaRecorder.stop();
        }
    };

    // 4. Event Tombol Ulangi (Reset Lokal)
    btnRetry.onclick = () => {
        resetUI();
    };

    // 5. Event Tombol Kirim
    btnSubmit.onclick = () => {
        // Panggil fungsi PHP saveVideo()
        @this.saveVideo();
    };

    // 6. Listener: Saat Server selesai memproses Step (perintah dari PHP)
    document.addEventListener('livewire:initialized', () => {
        @this.on('step-complete', () => {
            console.log("Step selesai, reset kamera...");
            resetUI();
            
            // Opsional: Matikan loading state manual jika perlu
            playback.src = ""; 
        });
    });

    // Fungsi Reset Tampilan ke Awal
    function resetUI() {
        playback.pause();
        playback.classList.add('hidden');
        preview.classList.remove('hidden'); // Kembali ke Webcam
        
        actionButtons.classList.add('hidden');
        btnStop.classList.add('hidden');
        recIndicator.classList.add('hidden');
        btnStart.classList.remove('hidden'); // Tampilkan tombol Mulai lagi

        videoInput.value = ''; // Clear input file
    }
</script>