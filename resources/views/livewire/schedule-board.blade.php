<div class="overflow-auto border border-neutral-200 dark:border-neutral-700">
    <table class="min-w-full table-fixed text-sm">
        <thead>
            <tr class="bg-gray-100 dark:bg-neutral-800 text-center">
                <th class="border border-neutral-200 dark:border-neutral-700 w-28" rowspan="2">Tanggal</th>
                <th class="border border-neutral-200 dark:border-neutral-700 w-14" rowspan="2">Pekerja</th>
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
                            <td rowspan="{{ count($workers) }}" class="border border-neutral-200 dark:border-neutral-700 px-2 py-1 bg-gray-50 dark:bg-neutral-900">{{ $date }}</td>
                        @endif
                        <td class="border border-neutral-200 dark:border-neutral-700 px-1 py-1 bg-white dark:bg-neutral-900">{{ $worker }}</td>
                        @php
                            $filledSlots = [];
                            $workerSchedules = collect($schedules[$date][$worker] ?? []);
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
                                    $duration = $schedule['duration'];
                                    $colspan = $duration / 15;
                                    $filledSlots = collect($times)
                                        ->filter(fn($t) => strtotime($t) >= strtotime($time) && strtotime($t) < strtotime("+{$duration} minutes", strtotime($time)))
                                        ->values()
                                        ->toArray();
                                @endphp
                                <td colspan="{{ $colspan }}" class="border border-neutral-200 dark:border-neutral-700 bg-green-100 dark:bg-green-900 w-8 cursor-pointer" wire:click="editSchedule('{{ $date }}', '{{ $worker }}', '{{ $time }}')">
                                    {{ $schedule['plat'] }} ({{ $duration }}m)
                                </td>
                            @else
                                <td class="border border-neutral-200 dark:border-neutral-700 w-8 bg-white dark:bg-neutral-900"></td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
    @if ($showModal)
<div 
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 dark:bg-black/70 backdrop-blur-sm transition-all">
    <div class="relative bg-white dark:bg-neutral-900 p-6 rounded-xl shadow-2xl w-full max-w-md mx-4">
        <!-- Close button -->
        <button 
            wire:click="$set('showModal', false)" 
            class="absolute top-3 right-3 text-gray-400 hover:cursor-pointer dark:text-gray-300 hover:text-gray-700 dark:hover:text-white transition"
            aria-label="Tutup"
        >
            &times;
        </button>
        <h2 class="text-xl font-bold mb-6 text-center text-gray-900 dark:text-gray-100">Edit schedule</h2>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">Plat Nomor</label>
            <input type="text" wire:model.defer="editPlat" class="border border-neutral-200 dark:border-neutral-700 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-300" />
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">Durasi (menit)</label>
            <select wire:model.defer="editDuration" class="border border-neutral-200 dark:border-neutral-700 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-300">
                <option value="15">15 Menit</option>
                <option value="30">30 Menit</option>
                <option value="45">45 Menit</option>
                <option value="60">60 Menit</option>
            </select>
        </div>

        <div class="flex justify-end gap-2">
            <button 
                wire:click="$set('showModal', false)" 
                class="px-4 py-2 bg-gray-200 hover:cursor-pointer dark:bg-neutral-800 text-gray-700 dark:text-gray-200 rounded hover:bg-gray-300  dark:hover:bg-neutral-700 transition"
            >
                Batal
            </button>
            <button 
                wire:click="updateSchedule"
                class="px-4 py-2 bg-blue-600 hover:cursor-pointer dark:bg-blue-700 text-white rounded hover:bg-blue-700 dark:hover:bg-blue-800 transition font-semibold"
            >
                Simpan
                <svg wire:loading class="inline-block ml-2 w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2.93 6.343A8.001 8.001 0 014 12H0c0 5.523 4.477 10 10 10v-4a6.002 6.002 0 01-3.07-1.657z"></path>
                </svg>
            </button>
        </div>
    </div>
</div>
@endif
</div>