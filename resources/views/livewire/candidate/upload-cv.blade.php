<div class="w-screen min-h-dvh bg-white flex">

  <!-- Left panel -->
  <div class="hidden lg:flex w-5/12 bg-indigo-900 flex-col justify-center px-12 relative overflow-hidden">
    <div class="absolute top-0 left-0 w-full h-full opacity-10">
      <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="w-full h-full text-white fill-current">
        <path d="M0 0 L100 100 L0 100 Z" />
      </svg>
    </div>

    <div class="relative z-10 text-white">
      <h2 class="text-4xl font-bold mb-6">Bergabung Bersama Kami</h2>
      <p class="text-lg text-indigo-200 mb-8">
        Kami menggunakan teknologi AI untuk mempermudah proses lamaran Anda.
        Cukup upload CV, dan biarkan sistem kami bekerja untuk Anda.
      </p>

      <div class="space-y-4">
        <div class="flex items-center space-x-4">
          <div class="w-8 h-8 rounded-full bg-indigo-700 flex items-center justify-center font-bold">1</div>
          <span>Upload CV (PDF)</span>
        </div>
        <div class="flex items-center space-x-4 opacity-75">
          <div class="w-8 h-8 rounded-full bg-indigo-800 flex items-center justify-center font-bold">2</div>
          <span>Review Data Otomatis</span>
        </div>
        <div class="flex items-center space-x-4 opacity-50">
          <div class="w-8 h-8 rounded-full bg-indigo-800 flex items-center justify-center font-bold">3</div>
          <span>Interview Singkat</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Right panel (form) -->
  <div class="w-full lg:w-7/12 flex flex-col min-h-dvh bg-white">
    <div class="flex-1 overflow-y-auto px-4 sm:px-12 py-12">
      <!-- DIBUAT FULL: hapus max-w-2xl mx-auto -->
      <div class="w-full">

        <div class="mb-10 lg:hidden">
          <h2 class="text-3xl font-extrabold text-gray-900">Bergabung Bersama Kami</h2>
          <p class="mt-2 text-sm text-gray-600">Upload CV untuk auto-fill data.</p>
        </div>

        <form wire:submit.prevent="submitForm" class="space-y-8">

          <div class="bg-gray-50 p-6 rounded-xl border-2 border-dashed border-gray-300 hover:border-indigo-500 transition relative group">
            <label for="resume-upload" class="cursor-pointer block text-center space-y-4">
              <div class="w-16 h-16 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mx-auto group-hover:bg-indigo-600 group-hover:text-white transition">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                  </path>
                </svg>
              </div>
              <div>
                <span class="text-indigo-600 font-bold text-lg hover:underline">Klik untuk Upload CV</span>
                <p class="text-gray-500 text-sm">atau drag and drop file PDF (Max 2MB)</p>
              </div>
              <input id="resume-upload" wire:model="resume" type="file" class="sr-only" accept="application/pdf">
            </label>

            <div wire:loading.flex wire:target="resume"
              class="absolute inset-0 bg-white/90 backdrop-blur-sm flex-col items-center justify-center rounded-xl z-20">
              <svg class="animate-spin h-10 w-10 text-indigo-600 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
              </svg>
              <p class="text-indigo-700 font-bold animate-pulse">🤖 AI sedang membaca & mengekstrak data...</p>
            </div>

            @error('resume')
              <p class="text-red-500 text-sm mt-2 text-center">{{ $message }}</p>
            @enderror

            @if($resume && !$isAutoFilling)
              <div
                class="mt-4 bg-green-50 border border-green-200 text-green-700 px-4 py-2 rounded-lg flex items-center justify-center text-sm">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z">
                  </path>
                </svg>
                {{ $resume->getClientOriginalName() }}
              </div>
            @endif
          </div>

          <div class="border-t border-gray-200 my-8"></div>

          <div
            class="grid grid-cols-1 md:grid-cols-2 gap-6 transition-all duration-500 {{ $isAutoFilling ? 'opacity-40 blur-sm pointer-events-none' : 'opacity-100' }}">

            <div class="md:col-span-2">
              <label class="block text-sm font-bold text-gray-700 mb-1">Nama Lengkap</label>
              <input type="text" wire:model="name"
                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-3"
                placeholder="Nama lengkap Anda">
              @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
              <label class="block text-sm font-bold text-gray-700 mb-1">Email</label>
              <input type="email" wire:model="email"
                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-3"
                placeholder="email@domain.com">
              @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
              <label class="block text-sm font-bold text-gray-700 mb-1">No. WhatsApp</label>
              <input type="text" wire:model="phone"
                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-3"
                placeholder="Contoh: 0812...">
              @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-bold text-gray-700 mb-1">Keahlian (Skills)</label>
              <input type="text" wire:model="skills"
                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-3"
                placeholder="Pisahkan dengan koma (Misal: PHP, Laravel, Design)">
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-bold text-gray-700 mb-1">Ringkasan Profesional</label>
              <textarea wire:model="summary" rows="5"
                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                placeholder="Ceritakan singkat tentang diri Anda..."></textarea>
              @error('summary') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

          </div>

          <div class="pt-4 pb-12">
            <button type="submit"
              class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 px-6 rounded-xl shadow-lg transform transition hover:-translate-y-1 focus:outline-none focus:ring-4 focus:ring-indigo-300 disabled:opacity-50 disabled:cursor-not-allowed"
              wire:loading.attr="disabled"
              wire:target="submitForm, resume">

              <span wire:loading.remove wire:target="submitForm">Lanjut ke Sesi Interview ➔</span>
              <span wire:loading wire:target="submitForm" class="flex items-center justify-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                  viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                  </path>
                </svg>
                Sedang Menyimpan...
              </span>
            </button>
          </div>

        </form>
      </div>
    </div>
  </div>

</div>
