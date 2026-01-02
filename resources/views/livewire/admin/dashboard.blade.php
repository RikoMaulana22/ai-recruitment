<div class="py-6 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <div class="flex flex-col md:flex-row justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Dashboard Admin</h2>
                <p class="text-sm text-gray-500">Selamat datang kembali, Admin.</p>
            </div>
            
            <button wire:click="logout" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-lg shadow-md transition flex items-center gap-2 transform hover:scale-105">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                Logout
            </button>
        </div>


        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Pelamar</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                </div>
                <div class="p-3 bg-blue-50 text-blue-600 rounded-lg">
                    👥
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Perlu Review</p>
                    <p class="text-3xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
                </div>
                <div class="p-3 bg-yellow-50 text-yellow-600 rounded-lg">
                    ⏳
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Tahap Interview</p>
                    <p class="text-3xl font-bold text-purple-600">{{ $stats['interview'] }}</p>
                </div>
                <div class="p-3 bg-purple-50 text-purple-600 rounded-lg">
                    🎤
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Diterima</p>
                    <p class="text-3xl font-bold text-green-600">{{ $stats['accepted'] }}</p>
                </div>
                <div class="p-3 bg-green-50 text-green-600 rounded-lg">
                    ✅
                </div>
            </div>
        </div>

        <div class="flex flex-col md:flex-row justify-between items-center gap-4 bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            <h2 class="text-lg font-bold text-gray-800">Daftar Kandidat</h2>
            
            <div class="flex gap-3 w-full md:w-auto">
                <select wire:model.live="filterStatus" class="border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Status</option>
                    <option value="pending">Pending</option>
                    <option value="analyzed">Analyzed</option>
                    <option value="interviewed">Interviewed</option>
                    <option value="accepted">Accepted</option>
                    <option value="rejected">Rejected</option>
                </select>

                <input type="text" 
                       wire:model.live.debounce.300ms="search" 
                       placeholder="Cari nama atau skill..." 
                       class="w-full md:w-64 border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Kandidat</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Skor AI</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Ringkasan Skill</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($candidates as $candidate)
                            <tr class="hover:bg-gray-50 transition duration-150 ease-in-out">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold">
                                                {{ substr($candidate->name, 0, 1) }}
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $candidate->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $candidate->email }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        @if($candidate->score >= 80)
                                            <div class="w-full bg-gray-200 rounded-full h-2.5 w-24 mr-2">
                                                <div class="bg-green-600 h-2.5 rounded-full" style="width: {{ $candidate->score }}%"></div>
                                            </div>
                                            <span class="text-sm font-bold text-green-700">{{ $candidate->score }}</span>
                                        @elseif($candidate->score >= 50)
                                            <div class="w-full bg-gray-200 rounded-full h-2.5 w-24 mr-2">
                                                <div class="bg-yellow-500 h-2.5 rounded-full" style="width: {{ $candidate->score }}%"></div>
                                            </div>
                                            <span class="text-sm font-bold text-yellow-700">{{ $candidate->score }}</span>
                                        @else
                                            <div class="w-full bg-gray-200 rounded-full h-2.5 w-24 mr-2">
                                                <div class="bg-red-500 h-2.5 rounded-full" style="width: {{ $candidate->score }}%"></div>
                                            </div>
                                            <span class="text-sm font-bold text-red-700">{{ $candidate->score }}</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        @if(isset($candidate->ai_analysis['skills']) && is_array($candidate->ai_analysis['skills']))
                                            @foreach(array_slice($candidate->ai_analysis['skills'], 0, 2) as $skill)
                                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                                    {{ $skill }}
                                                </span>
                                            @endforeach
                                            @if(count($candidate->ai_analysis['skills']) > 2)
                                                <span class="text-xs text-gray-400">+{{ count($candidate->ai_analysis['skills']) - 2 }} lainnya</span>
                                            @endif
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $badges = [
                                            'pending' => 'bg-gray-100 text-gray-800',
                                            'analyzed' => 'bg-blue-50 text-blue-700 border-blue-100',
                                            'interviewed' => 'bg-purple-50 text-purple-700 border-purple-100',
                                            'accepted' => 'bg-green-50 text-green-700 border-green-100',
                                            'rejected' => 'bg-red-50 text-red-700 border-red-100',
                                        ];
                                        $class = $badges[$candidate->status] ?? 'bg-gray-100';
                                    @endphp
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border {{ $class }}">
                                        {{ ucfirst($candidate->status) }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('candidates.show', $candidate->id) }}" 
                                       class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach

                        @if($candidates->isEmpty())
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <p>Data tidak ditemukan.</p>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $candidates->links() }}
            </div>
        </div>
    </div>
</div>