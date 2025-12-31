<div class="max-w-2xl mx-auto mt-10 p-6 bg-white shadow-lg rounded-lg">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Upload CV Anda</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="save">
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Nama Lengkap</label>
            <input type="text" wire:model="name" class="w-full px-3 py-2 border rounded shadow-sm focus:outline-none focus:border-blue-500">
            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
            <input type="email" wire:model="email" class="w-full px-3 py-2 border rounded shadow-sm focus:outline-none focus:border-blue-500">
            @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">No. WhatsApp</label>
            <input type="text" wire:model="phone" class="w-full px-3 py-2 border rounded shadow-sm focus:outline-none focus:border-blue-500">
            @error('phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2">Upload CV (PDF Max 2MB)</label>
            <input type="file" wire:model="resume" class="w-full p-2 border border-dashed border-gray-400 rounded bg-gray-50">
            <div wire:loading wire:target="resume" class="text-sm text-blue-500 mt-1">Sedang mengunggah...</div>
            @error('resume') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <button type="submit" 
                class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700 transition disabled:opacity-50"
                wire:loading.attr="disabled"
                wire:target="save">
            <span wire:loading.remove wire:target="save">Kirim Lamaran</span>
            <span wire:loading wire:target="save">Sedang Memproses...</span>
        </button>
    </form>
</div>