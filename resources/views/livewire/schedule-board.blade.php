<div>
    <div class="overflow-auto border border-neutral-200 dark:border-neutral-700 rounded-lg" >
        <table class="min-w-full text-sm relative">
            <thead class="bg-gray-100 dark:bg-neutral-800 z-10">
                <tr class="bg-gray-100 dark:bg-neutral-800 text-center">
                    <th class="sticky left-0 top-0 z-30 bg-gray-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700" rowspan="2">Tanggal</th>
                    <th class="sticky left-14 top-0 z-30 bg-gray-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700" rowspan="2">Pekerja</th>
                    @foreach (range(8, 17) as $hour)
                        <th class="border border-neutral-200 dark:border-neutral-700" colspan="4">{{ sprintf('%02d', $hour) }}</th>
                    @endforeach
                </tr>
                <tr class="bg-gray-100 dark:bg-neutral-800 text-center">
                    @foreach (range(8, 17) as $hour)
                        @foreach (['00', '15', '30', '45'] as $minute)
                            <th class="border border-neutral-200 dark:border-neutral-700">{{ $minute }}</th>
                        @endforeach
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($dates as $date)
                    @foreach ($workers as $worker)
                        <tr class="text-center">
                            @if ($loop->first)
                                <td rowspan="{{ count($workers) }}" class="sticky left-0 top-0 z-30 border border-neutral-200 dark:border-neutral-700 px-2 py-1 bg-gray-50 dark:bg-neutral-900">{{ $date }}</td>
                            @endif
                            <td class="sticky left-14 top-0 z-30 border border-neutral-200 dark:border-neutral-700 px-1 py-1 {{ $worker->colorClass }}">
                                <button wire:click="showEditWorker({{ $worker->id }})" class="hover:underline font-semibold">
                                    {{ $worker->nama }}
                                </button>
                                <br/>({{ $worker->status }})
                            </td>
                            {{-- <td class="border border-neutral-200 dark:border-neutral-700 px-1 py-1 bg-white dark:bg-neutral-900">{{ $worker->nama }} ({{ $worker->status }})</td> --}}
                            @php
                                $filledSlots = [];
                                $workerSchedules = collect($schedules[$date][$worker->id] ?? []);
                            @endphp
                            @foreach ($times as $time)
                                @if (in_array($time, $filledSlots))
                                    @continue
                                @endif
                                @php
                                    $schedule = $workerSchedules->firstWhere('start', $time);
                                @endphp
                                @if ($schedule)
                                    @php
                                        $waktuMulai = $schedule['waktu_mulai'] ?? $schedule['start'] ?? null;
                                        $waktuSelesai = $schedule['waktu_selesai'] ?? null;
                                        // jaga jaga g ad waktu selesai
                                        if (!$waktuSelesai && isset($schedule[$date][$worker->id])){
                                            $jadwalUtama = collect($schedules[$date][$worker->id])->firstWhere('id', $schedule['id']);
                                            $waktuSelesai = $jadwalUtama['waktu_selesai'] ?? null;
                                        }
                                        // Hitung durasi
                                        if ($waktuMulai && $waktuSelesai) {
                                            $duration = (strtotime($waktuSelesai)- strtotime($waktuMulai)) / 60; // mnt
                                            $colspan = ceil($duration / 15); // 15 menit per slot 
                                        } else{
                                            $duration = 15;
                                            $colspan = 1;
                                        }
                                        // isi ijo
                                        $filledSlots = array_merge($filledSlots, collect($times)
                                            ->filter(fn($t) => strtotime($t) >= strtotime($time) && strtotime($t) < strtotime("+".($colspan*15)." minutes", strtotime($time)))
                                            ->values()
                                            ->toArray());
                                            
                                        
                                        // $duration = $schedule['duration'];
                                        // $colspan = $duration / 15;
                                        // $filledSlots = collect($times)
                                        //     ->filter(fn($t) => strtotime($t) >= strtotime($time) && strtotime($t) < strtotime("+{$duration} minutes", strtotime($time)))
                                        //     ->values()
                                        //     ->toArray();

                                   
                                        
                                    @endphp
                                    <td colspan="{{ $colspan }}" class="border border-neutral-200 dark:border-neutral-700 w-8 cursor-pointer transition hover:bg-grey-200 dark:hover:bg-grey-200 {{ $schedule['colorClass'] }} "
                                        wire:click="editSchedule('{{ $date }}', '{{ $worker->id }}', '{{ $time }}')">
                                        <span class="block text-xs font-semibold text-gray-800 dark:text-gray-100">
                                            {{ $schedule['plat'] }} ({{ $duration }}m)
                                        </span>
                                    </td>
                                @else
                                    <td class="border border-neutral-200 dark:border-neutral-700 min-w-24 bg-white dark:bg-neutral-900"></td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- EDIT MODAL--}}
    @if ($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 dark:bg-black/70 backdrop-blur-sm transition-all">
        <div class="relative bg-white dark:bg-neutral-900 p-6 rounded-xl shadow-2xl w-full max-w-md mx-4">
            <button wire:click="$set('showModal', false)" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white transition" aria-label="Tutup">
                &times;
            </button>
            <h2 class="text-xl font-bold mb-6 text-center text-gray-900 dark:text-gray-100">Edit Schedule</h2>
            {{-- SHOW No SPP --}}
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">No.spp</label>
                <input type="text" wire:model.defer="showNoSpp" readonly
                    class="border border-neutral-200 dark:border-neutral-700 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-300 transition" />
            </div>
            {{-- EDIT PLAT --}}
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">Plat Nomor</label>
                <input type="text" wire:model.defer="editPlat"
                    class="border border-neutral-200 dark:border-neutral-700 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-300 transition" />
            </div>
            {{-- CATATAN(gw bikin show only, sama lebarin sus, hehe) --}}
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">Catatan</label>
                <textarea type="text" wire:model.defer="showCatatan" class="border border-neutral-200 dark:border-neutral-700 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-300 transition"></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">Tambah Durasi (menit)</label>
                <input
                    type="number"
                    wire:model.defer="editDuration"
                    min="15"
                    step="15"
                    value="0"
                    class="border border-neutral-200 dark:border-neutral-700 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-300 transition"
                />
                <span class="text-xs text-gray-500 dark:text-gray-400">Kelipatan 15 menit</span>
            </div>
            <div class="mb-6 text-sm text-gray-500 dark:text-gray-400">
                Detail Waktu sekarang:
                {{optional($worktypes->firstWhere('id', $durationAsli))->flatrate ?? '-'}} menit
                ({{ optional($worktypes->firstWhere('id', $durationAsli))->nama_pekerjaan ?? optional($worktypes->firstWhere('id', $durationAsli))->nama ?? '-' }})
            </div>
            @if ($errors->has('overlap'))
                <div class="mb-2 px-3 py-2 bg-red-500 text-white rounded text-center">
                    {{ $errors->first('overlap') }}
                </div>
            @endif
            <div class="flex justify-end gap-2">
                {{-- TIMER --}}
                @php
                    $schedule = \App\Models\Schedule::find($editScheduleId);
                @endphp

                @if ($schedule && $schedule->status === 'selesai')
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-base text-gray-800 dark:text-gray-100">
                            Hasil: {{ $schedule->timer ?? 0 }} detik
                        </span>
                    </div>
                @elseif (isset($timers[$editScheduleId]))
                    <div class="flex items-center gap-2">
                        <span wire:poll.1s="updateTimer" class="font-mono text-base text-gray-800 dark:text-gray-100">
                            {{ gmdate('i:s', $timers[$editScheduleId]['value'] ?? 0) }}
                        </span>
                        <button wire:click="stopTimer({{ $editScheduleId }})" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded transition">Stop</button>
                    </div>
                @else
                    <button wire:click="startTimer({{ $editScheduleId }})" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded transition">Start Timer</button>
                @endif
                {{-- END TIMER --}}
                <button wire:click="$set('showModal', false)" class="px-4 py-2 bg-gray-200 dark:bg-neutral-800 text-gray-700 dark:text-gray-200 rounded hover:bg-gray-300 dark:hover:bg-neutral-700 transition">Batal</button>
                <button wire:click="updateSchedule" class="px-4 py-2 bg-blue-600 dark:bg-blue-700 text-white rounded hover:bg-blue-700 dark:hover:bg-blue-800 transition font-semibold">
                    Simpan
                </button>
            </div>
        </div>
    </div>
    @endif
    @if ($editWorkerId)
    <div x-data="{ open: true }" x-show="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 dark:bg-black/70 backdrop-blur-sm transition-all">
        <div class="relative bg-white dark:bg-neutral-900 p-6 rounded-xl shadow-2xl w-full max-w-md mx-4">
            <button wire:click="cancelEditWorker" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white transition" aria-label="Tutup">
                &times;
            </button>
            <h2 class="text-xl font-bold mb-6 text-center text-gray-900 dark:text-gray-100">Edit Pekerja</h2>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">Nama</label>
                <input type="text" wire:model.defer="editWorkerNama"
                    class="border border-neutral-200 dark:border-neutral-700 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100" />
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">Status</label>
                <select wire:model.defer="editWorkerStatus"
                    class="border border-neutral-200 dark:border-neutral-700 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100">
                    <option value="">Pilih Status</option>
                    <option value="aktif">Aktif</option>
                    <option value="sedang memperbaiki">Sedang Memperbaiki</option>
                    <option value="izin">Izin</option>
                    <option value="sakit">Sakit</option>
                    <option value="cuti">Cuti</option>
                    <option value="training">Training</option>
                    <option value="off">Off</option>
                </select>
            </div>
            <div class="flex justify-end gap-2">
                <button wire:click="cancelEditWorker" class="px-4 py-2 bg-gray-200 dark:bg-neutral-800 text-gray-700 dark:text-gray-200 rounded hover:bg-gray-300 dark:hover:bg-neutral-700 transition">Batal</button>
                <button wire:click="updateWorker" class="px-4 py-2 bg-blue-600 dark:bg-blue-700 text-white rounded hover:bg-blue-700 dark:hover:bg-blue-800 transition font-semibold">
                    Simpan
                </button>
            </div>
        </div>
    </div>
    @endif
    {{-- EDIT MODAL END --}}

    {{-- Ekxport --}}
    <div>
        <div class="flex justify-end mt-4">
            <button
                wire:click="exportExcel"
                class="bg-green-600 hover:bg-green-700 text-white font-semibold px-5 py-2 rounded shadow transition">
                Export Excel
            </button>
        </div>
    </div>
    {{-- Ekxport --}}

    {{-- TAMBAH --}}
    <div class="p-6 bg-gray-50 dark:bg-neutral-900 mb-6 rounded-xl shadow border border-neutral-200 dark:border-neutral-700 max-w-6xl mt-12">
        <form wire:submit.prevent="tambahSchedule" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold mb-1 text-gray-700 dark:text-gray-200">Teknisi</label>
                <select wire:model="newWorker" class="cursor-pointer border border-neutral-200 dark:border-neutral-700 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                    <option value="">Pilih</option>
                    @foreach($workers as $w)
                        <option value="{{ $w->id }}">{{ $w->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1 text-gray-700 dark:text-gray-200">No. SPP</label>
                <input type="text" wire:model="newNoSpp" class="border border-neutral-200 dark:border-neutral-700 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-400 transition" />
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1 text-gray-700 dark:text-gray-200">Tanggal</label>
                <input type="date" wire:model="newDate" class="border border-neutral-200 dark:border-neutral-700 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-400 transition" />
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1 text-gray-700 dark:text-gray-200">Jam Mulai</label>
                <select wire:model="newTime" class="cursor-pointer border border-neutral-200 dark:border-neutral-700 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                    <option value="">Pilih</option>
                    @foreach($times as $t)
                        <option value="{{ $t }}">{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1 text-gray-700 dark:text-gray-200">Jenis Pekerjaan</label>
                <select wire:model="newWorktype" class="cursor-pointer border border-neutral-200 dark:border-neutral-700 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                    <option value="">Pilih</option>
                    @foreach($worktypes as $type)
                        <option value="{{ $type->id }}">{{ $type->nama_pekerjaan ?? $type->nama }} ({{ $type->flatrate }}m)</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1 text-gray-700 dark:text-gray-200">Plat</label>
                <input type="text" wire:model="newPlat" class="border border-neutral-200 dark:border-neutral-700 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-400 transition" />
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1 text-gray-700 dark:text-gray-200">Nama Mobil</label>
                <input type="text" wire:model="newNamaMobil" class="border border-neutral-200 dark:border-neutral-700 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-400 transition" />
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1 text-gray-700 dark:text-gray-200">Catatan</label>
                <input type="text" wire:model="newKeterangan" class="border border-neutral-200 dark:border-neutral-700 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-400 transition" />
            </div>
            <div class="flex items-center gap-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold shadow transition cursor-pointer min-w-fit">
                    Tambah Jadwal
                </button>
                @if ($errors->has('overlap'))
                    {{-- Show error message if there's an overlap --}}
                    <span class="text-red-500 text-sm font-semibold min-w-fit px-3 py-2 rounded">
                        {{ $errors->first('overlap') }}
                    </span>
                    @endif
                @if ($errors->has('newWorker'))
                    {{-- Show error message if no worker is selected --}}
                    {{-- Show error message if there's an overlap --}}
                    <span class="text-red-500 text-sm font-semibold min-w-fit px-3 py-2 rounded">
                        {{ $errors->first('newWorker') }}
                    </span>
                    @endif
                {{-- <span class="text-red-500 text-sm font-semibold min-w-full px-3 py-2">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Est magnam sed cumque obcaecati?</span> --}}
            </div>
        </form>
    </div>
</div>