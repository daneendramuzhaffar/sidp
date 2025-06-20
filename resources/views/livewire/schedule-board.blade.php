{{-- filepath: resources/views/livewire/schedule-board.blade.php --}}
<div>
    {{-- Filter Tanggal --}}
    {{-- <div class="mb-4 flex items-center gap-2"> 
        <label for="tanggal" class="font-semibold text-gray-700 dark:text-gray-200">Pilih Tanggal:</label>
        <select id="tanggal" wire:model="selectedDate" class="border border-neutral-200 dark:border-neutral-700 rounded px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100">
            @foreach($availableDates as $date)
                <option value="{{ $date }}">{{ $date }}</option>
            @endforeach
        </select>
    </div> --}}

    {{-- Tabel Jadwal --}}
    <div class="overflow-auto border max-h-[50%] border-neutral-200 dark:border-neutral-700 rounded-lg">
        <table class="min-w-full text-xs md:text-sm relative">
            <thead class="bg-gray-100 dark:bg-neutral-800 z-10">
                <tr class="text-center">
                    <th class="sticky top-0 bg-gray-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700" rowspan="2">Tanggal</th>
                    <th class="sticky top-0 left-0 z-11 bg-gray-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700" rowspan="2">Teknisi</th>
                    @foreach (range(8, 17) as $hour)
                        <th class="sticky top-0 z-10 bg-gray-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700" colspan="4">
                            {{ sprintf('%02d', $hour) }}
                        </th>
                    @endforeach
                </tr>
                <tr class="text-center">
                    @foreach (range(8, 17) as $hour)
                        @foreach (['00', '15', '30', '45'] as $minute)
                            <th class="sticky top-5 z-10 bg-gray-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700">
                                {{ $minute }}
                            </th>
                        @endforeach
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @foreach ($dates as $date)
                    @foreach ($workers as $worker)
                        <tr class="text-center">
                            @if ($loop->first)
                                <td rowspan="{{ count($workers) }}" class="border border-neutral-200 dark:border-neutral-700 px-2 py-1 bg-gray-50 dark:bg-neutral-900">{{ $date }}</td>
                            @endif
                            <td class="sticky left-0 top-0 z-10 border border-neutral-200 dark:border-neutral-700 px-1 py-1 {{ $worker->colorClass }}">
                                <button wire:click="showEditWorker({{ $worker->id }})" class="hover:underline font-semibold cursor-pointer">
                                    {{ $worker->nama }}
                                </button>
                                <br/><span class="text-xs">({{ $worker->status }})</span>
                            </td>
                            @php
                                $jamMulaiWorker = $worker->mulai;
                                $break = [
                                    '08:00:00' => ['12:00', '13:00'],
                                    '09:00:00' => ['13:00', '14:00'],
                                ][$jamMulaiWorker] ?? null;
                                $filledSlots = [];
                                $workerSchedules = collect($schedules[$date][$worker->id] ?? []);
                            @endphp
                            @foreach ($times as $time)
                                @php
                                    $isBreak = false;
                                    if ($break) {
                                        $isBreak = (strtotime($time) >= strtotime($break[0]) && strtotime($time) < strtotime($break[1]));
                                    }
                                @endphp
                                @if ($isBreak)
                                    <td
                                        class="bg-yellow-200 dark:bg-yellow-600 text-yellow-900 dark:text-yellow-100 font-bold uppercase border border-yellow-300 dark:border-yellow-700 rounded text-xs text-center"
                                        title="Waktu Istirahat"
                                        style="letter-spacing: 1px;"
                                    >
                                        ISTIRAHAT
                                    </td>
                                    @continue
                                @endif
                                @if (in_array($time, $filledSlots)) @continue @endif
                                @php
                                    $schedule = $workerSchedules->firstWhere('start', $time);
                                @endphp
                                @if ($schedule)
                                    @php
                                        $waktuMulai = $schedule['waktu_mulai'] ?? $schedule['start'] ?? null;
                                        $waktuSelesai = $schedule['waktu_selesai'] ?? null;
                                        if ($waktuMulai && $waktuSelesai) {
                                            $duration = (strtotime($waktuSelesai)- strtotime($waktuMulai)) / 60;
                                            $colspan = ceil($duration / 15);
                                        } else {
                                            $duration = 15;
                                            $colspan = 1;
                                        }
                                        $filledSlots = array_merge($filledSlots, collect($times)
                                            ->filter(fn($t) => strtotime($t) >= strtotime($time) && strtotime($t) < strtotime("+".($colspan*15)." minutes", strtotime($time)))
                                            ->values()
                                            ->toArray());
                                    @endphp
                                    <td colspan="{{ $colspan }}" class="border border-neutral-200 dark:border-neutral-700 w-8 cursor-pointer transition {{ $schedule['colorClass'] }}"
                                        wire:click="editSchedule('{{ $date }}', '{{ $worker->id }}', '{{ $time }}')">
                                        <span class="block text-xs px-1 font-semibold text-gray-800 dark:text-gray-100 truncate">
                                            {{ $schedule['no_spp'] ?? '' }}<br>
                                            <span class="font-normal">{{ $schedule['plat'] }}</span>
                                        </span>
                                    </td>
                                @else
                                    <td class="border border-neutral-200 dark:border-neutral-700 min-w-16 bg-white dark:bg-neutral-900"></td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Modal Edit Jadwal --}}
    @if ($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 dark:bg-black/70 backdrop-blur-sm transition-all">
        <div class="relative bg-white dark:bg-neutral-900 p-6 rounded-xl shadow-2xl w-full max-w-md mx-4">
            <button wire:click="cancelEditSchedule" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white transition cursor-pointer text-2xl" aria-label="Tutup">&times;</button>
            <h2 class="text-xl font-bold mb-6 text-center text-gray-900 dark:text-gray-100">Edit Schedule</h2>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">No.spp</label>
                <input type="text" wire:model.defer="showNoSpp" readonly class="border border-neutral-200 dark:border-neutral-700 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-300 transition" />
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">Plat Nomor</label>
                <input type="text" wire:model.defer="editPlat" class="border border-neutral-200 dark:border-neutral-700 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-300 transition" />
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">Catatan</label>
                <textarea wire:model.defer="showCatatan" class="border border-neutral-200 dark:border-neutral-700 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-300 transition"></textarea>
            </div>
            <div class="mb-4">
                @php $schedule = \App\Models\Schedule::find($editScheduleId); @endphp
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">Tambah Durasi (menit)</label>
                <input type="number" wire:model.defer="editDuration" min="15" step="15" value="0"
                    class="border border-neutral-200 dark:border-neutral-700 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-300 transition"
                    @if($schedule && in_array($schedule->status, ['selesai'])) disabled @endif />
                <span class="text-xs text-gray-500 dark:text-gray-400">Kelipatan 15 menit</span>
            </div>
            <div class="mb-6 text-sm text-gray-500 dark:text-gray-400">
                @php
                    $selisihMenit = null;
                    if ($schedule && $schedule->waktu_mulai && $schedule->waktu_selesai) {
                        $start = strtotime($schedule->waktu_mulai);
                        $end = strtotime($schedule->waktu_selesai);
                        $selisihMenit = ($end - $start) / 60;
                    }
                @endphp
                Durasi Jadwal: {{ $selisihMenit !== null ? $selisihMenit . ' menit' : '-' }}
                @if($schedule)
                    ({{ substr($schedule->waktu_mulai, 0, 5) }} - {{ substr($schedule->waktu_selesai, 0, 5) }})
                @endif
                @php
                    $workTypeName = null;
                    if ($schedule && $schedule->duration) {
                        $workType = \App\Models\WorkTypes::find($schedule->duration);
                        $workTypeName = $workType ? ($workType->nama_pekerjaan ?? $workType->nama) : null;
                    }
                @endphp
                <br/>
                Jenis Pekerjaan: <span class="font-semibold">{{ $workTypeName ?? '-' }}</span>
            </div>
            @if ($errors->has('overlap'))
                <div class="mb-2 px-3 py-2 bg-red-500 text-white rounded text-center">
                    {{ $errors->first('overlap') }}
                </div>
            @endif
            <div class="flex flex-wrap justify-end gap-2">
                @if ($schedule && $schedule->status === 'selesai')
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-base text-gray-800 dark:text-gray-100">Hasil: {{ gmdate('H:i:s', $schedule->timer) ?? 0 }}</span>
                    </div>
                @elseif (isset($timers[$editScheduleId]))
                    <div class="flex items-center gap-2">
                        <span wire:poll.1s="updateTimer" class="font-mono text-base text-gray-800 dark:text-gray-100">
                            {{ gmdate('i:s', $timers[$editScheduleId]['value'] ?? 0) }}
                        </span>
                        <button wire:click="pauseTimer('{{ $schedule->no_spp }}')" wire:loading.attr="disabled" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded transition cursor-pointer">
                            Pause
                            <span wire:loading wire:target="pauseTimer" class="animate-spin mx-1">⏳</span>
                        </button>
                        <button wire:click="stopTimer('{{ $schedule->no_spp }}')" wire:loading.attr="disabled" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded transition cursor-pointer">
                            Stop
                            <span wire:loading wire:target="stopTimer" class="animate-spin mx-1">⏳</span>
                        </button>
                    </div>
                @elseif ($schedule && $schedule->status === 'pause')
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-base text-gray-800 dark:text-gray-100">
                            {{ gmdate('i:s', $schedule->timer ?? 0) }}
                        </span>
                        <button wire:click="resumeTimer('{{ $schedule->no_spp }}')" wire:loading.attr="disabled"
                            class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded transition cursor-pointer">
                            Resume
                            <span wire:loading wire:target="resumeTimer" class="animate-spin mx-1">⏳</span>
                        </button>
                    </div>
                @else
                    <button wire:click="startTimer('{{ $schedule->no_spp }}')" wire:loading.attr="disabled" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded transition cursor-pointer">
                        Start Timer
                        <span wire:loading wire:target="startTimer" class="animate-spin mx-1">⏳</span>
                    </button>
                @endif
                <button onclick="if(confirm('Yakin hapus pekerjaan ini?')) { @this.hapusSchedule() }" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded transition cursor-pointer cursor-pointer">Hapus</button>
                <button wire:click="updateSchedule" class="px-4 py-2 bg-blue-600 dark:bg-blue-700 text-white rounded hover:bg-blue-700 dark:hover:bg-blue-800 transition font-semibold cursor-pointer" wire:loading.attr="disabled">
                    Simpan
                    <span wire:loading wire:target="updateSchedule" class="animate-spin mx-1">⏳</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal Edit Worker --}}
    @if ($editWorkerId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 dark:bg-black/70 backdrop-blur-sm transition-all" >
            <div class="relative bg-white dark:bg-neutral-900 p-6 rounded-xl shadow-2xl w-full max-w-md mx-4">
                <button wire:click="cancelEditWorker" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white transition cursor-pointer text-2xl" aria-label="Tutup">&times;</button>
                <h2 class="text-xl font-bold mb-6 text-center text-gray-900 dark:text-gray-100">Edit Pekerja</h2>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">Nama</label>
                    <input type="text" wire:model.defer="editWorkerNama" class="border border-neutral-200 dark:border-neutral-700 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100" />
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">Status</label>
                    <select wire:model.defer="editWorkerStatus" class="border border-neutral-200 dark:border-neutral-700 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100">
                        <option value="" disabled >Pilih Status</option>
                        <option value="aktif">Aktif</option>
                        <option value="sedang memperbaiki">Sedang Memperbaiki</option>
                        <option value="izin">Izin</option>
                        <option value="sakit">Sakit</option>
                        <option value="cuti">Cuti</option>
                        <option value="training">Training</option>
                        <option value="off">Off</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">Shift</label>
                    <select wire:model.defer="editMulai" class="border border-neutral-200 dark:border-neutral-700 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100">
                        <option value="08:00:00">Shift 1 (08:00 - 16:00)</option>
                        <option value="09:00:00">Shift 2 (09:00 - 17:00)</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2">
                    <button onclick="if(confirm('Yakin hapus teknisi ini beserta seluruh jadwalnya?')) { @this.hapusTeknisi({{ $worker->id }}) }" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded transition cursor-pointer cursor-pointer">Hapus</button>
                    <button wire:click="updateWorker" wire:loading.attr="disabled" class="px-4 py-2 bg-blue-600 dark:bg-blue-700 text-white rounded hover:bg-blue-700 dark:hover:bg-blue-800 transition font-semibold cursor-pointer">
                        Simpan
                        <span wire:loading wire:target="updateWorker" class="animate-spin mx-1">⏳</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Export Excel --}}
    <div class="flex justify-end mt-12">
        <button wire:click="exportExcel" wire:loading.attr="disabled"
            class="bg-green-600 hover:bg-green-700 text-white font-semibold px-5 py-2 rounded shadow transition cursor-pointer">
            Export Excel
            <span wire:loading wire:target="exportExcel" class="animate-spin mx-1">⏳</span>
        </button>
    </div>

    {{-- Form Tambah Jadwal --}}
    <div class="p-6 bg-gray-50 dark:bg-neutral-900 mb-6 rounded-xl shadow border border-neutral-200 dark:border-neutral-700 max-w-6xl mt-12">
        <form wire:submit.prevent="tambahSchedule" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold mb-1 text-gray-700 dark:text-gray-200">Teknisi</label>
                <select required wire:model="newWorker" class="cursor-pointer border border-neutral-200 dark:border-neutral-700 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                    <option value="">Pilih</option>
                    @foreach($workers as $w)
                        <option value="{{ $w->id }}">{{ $w->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1 text-gray-700 dark:text-gray-200">No. SPP</label>
                <input required type="text" wire:model="newNoSpp" class="border border-neutral-200 dark:border-neutral-700 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-400 transition" />
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1 text-gray-700 dark:text-gray-200">Tanggal</label>
                <input required type="date" wire:model="newDate" class="border border-neutral-200 dark:border-neutral-700 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-400 transition" />
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1 text-gray-700 dark:text-gray-200">Jam Mulai</label>
                <select required wire:model="newTime" class="cursor-pointer border border-neutral-200 dark:border-neutral-700 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                    <option value="">Pilih</option>
                    @foreach($times as $t)
                        <option value="{{ $t }}">{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1 text-gray-700 dark:text-gray-200">Jenis Pekerjaan</label>
                <select required wire:model="newWorktype" class="cursor-pointer border border-neutral-200 dark:border-neutral-700 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                    <option value="">Pilih</option>
                    @foreach($worktypes as $type)
                        <option value="{{ $type->id }}">{{ $type->nama_pekerjaan ?? $type->nama }} ({{ $type->flatrate }}m)</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1 text-gray-700 dark:text-gray-200">Plat</label>
                <input required type="text" wire:model="newPlat" class="border border-neutral-200 dark:border-neutral-700 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-400 transition" />
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1 text-gray-700 dark:text-gray-200">Nama Mobil</label>
                <input required type="text" wire:model="newNamaMobil" class="border border-neutral-200 dark:border-neutral-700 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-400 transition" />
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1 text-gray-700 dark:text-gray-200">Catatan</label>
                <input required type="text" wire:model="newKeterangan" class="border border-neutral-200 dark:border-neutral-700 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-400 transition" />
            </div>
            <div class="flex items-center gap-4">
                <button type="submit" wire:loading.attr="disabled" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold shadow transition cursor-pointer min-w-fit">
                    Tambah Jadwal
                    <span wire:loading wire:target="tambahSchedule" class="animate-spin mx-1">⏳</span>
                </button>
                @if ($errors->has('newWorker'))
                    <span class="text-red-500 text-sm font-semibold min-w-fit px-3 py-2 rounded">
                        {{ $errors->first('newWorker') }}
                    </span>
                @endif
                @if ($errors->has('overlap'))
                    <span class="text-red-500 text-sm font-semibold min-w-fit px-3 py-2 rounded">
                        {{ $errors->first('overlap') }}
                    </span>
                @endif
            </div>
        </form>
    </div>
</div>
<script>
    window.addEventListener('refresh-page', () => {
        window.location.reload();
    });
</script>