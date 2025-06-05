<?php

namespace App\Livewire;

use App\Models\Workers;
use App\Models\Schedule;
use App\Models\WorkTypes;
use Livewire\Component;
use App\Exports\SchedulesExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ScheduleBoard extends Component
{
    // Data utama
    public $dates = [];
    public $selectedDate;
    public $availableDates = [];
    public $workers = [];
    public $times = [];
    public $statuses = [];
    public $schedules = [];
    public $worktypes = [];
    public $timers = [];

    // Break times for each shift
    private $breakTimes = [
        '08:00:00' => ['12:00', '13:00'], // shift 1
        '09:00:00' => ['13:00', '14:00'], // shift 2
    ];

    //editShift
    public $editWorkerShift = '';

    // Modal & Edit
    public $showModal = false;
    public $editScheduleId;
    public $editDate, $editWorker, $editTime, $editPlat, $editStatus, $showNoSpp, $showCatatan;
    public $editDuration = 0;
    public $durationAsli = 0;

    // Tambah Jadwal
    public $newWorker, $newDate, $newTime, $newWorktype, $newPlat, $newNoSpp, $newKeterangan, $newNamaMobil;

    // Edit Worker
    public $editWorkerId = null;
    public $editWorkerNama = '';
    public $editWorkerStatus = '';
    public $editMulai = '';

    public function mount()
    {
        $today = date('Y-m-d');
        $this->availableDates = Schedule::orderBy('date')->pluck('date')->unique()->values()->toArray();
        $this->selectedDate = $today;
        $this->worktypes = WorkTypes::all();
        $this->times = collect(range(8, 17))
            ->flatMap(fn($h) => ['00','15','30','45'])
            ->map(fn($m, $i) => sprintf('%02d:%s', 8 + intdiv($i, 4), $m))
            ->toArray();
        $this->workers = $this->mapWorkerColors(Workers::all());
        // $this->workers = Workers::all();
        $this->initTimers();
        $this->loadSchedules();
    }

    public function updatedSelectedDate()
    {
        $this->loadSchedules();
    }

    private function mapWorkerColors($workers)
    {
        return $workers->map(function($worker) {
            $status = strtolower($worker->status);
            $worker->colorClass = match($status) {
                'aktif' => 'bg-green-500 text-white',
                'sedang memperbaiki' => 'bg-yellow-500 text-white',
                'training' => 'bg-blue-500 text-white',
                default => 'bg-red-500 text-white',
            };
            return $worker;
        });
    }

    private function initTimers()
    {
        $this->timers = [];
        foreach (Schedule::where('status', 'proses')->get() as $schedule) {
            $start = $schedule->timer;
            $elapsed = now()->timestamp - $start;
            $this->timers[$schedule->id] = [
                'start' => $start,
                'value' => $elapsed,
            ];
        }
    }

    public function loadSchedules()
    {
        $this->schedules = [];
        $date = $this->selectedDate ?? date('Y-m-d');
        $this->dates = [$date];

        $schedules = Schedule::with(['worker', 'worktype'])
            ->where('date', $date)
            ->get();

        // $workerIds = $schedules->pluck('id_worker')->unique()->values();
        // $this->workers = Workers::whereIn('id', $workerIds)->get()->map(function($worker) {
        //     $status = strtolower($worker->status);
        //     $worker->colorClass = match($status) {
        //         'aktif' => 'bg-green-500 text-white',
        //         'sedang memperbaiki' => 'bg-yellow-400 text-gray-900',
        //         'training' => 'bg-blue-500 text-white',
        //         default => 'bg-red-500 text-white',
        //     };
        //     return $worker;
        // });

        foreach ($schedules as $schedule) {
            $dateKey = $schedule->date;
            $workerId = $schedule->id_worker;
            $mulai = Carbon::createFromFormat('H:i:s', $schedule->waktu_mulai);
            $selesai = Carbon::createFromFormat('H:i:s', $schedule->waktu_selesai);
            $interval = 15;

            $now = now()->timestamp;
            $waktuSelesaiTimestamp = strtotime($schedule->date . ' ' . $schedule->waktu_selesai);

            $colorClass = match (true) {
                ($schedule->status === 'proses' && $now > $waktuSelesaiTimestamp) => 'bg-red-500 text-white hover:bg-red-700 dark:bg-red-500',
                ($schedule->status === 'proses' && $now <= $waktuSelesaiTimestamp) => 'bg-blue-500 text-white dark:bg-blue-500 hover:bg-blue-700',
                ($schedule->status ?? 'belum dimulai') === 'selesai' => 'bg-green-500 dark:bg-green-500 hover:bg-green-700 text-white',
                default => 'bg-slate-500 dark:bg-slate-500 hover:bg-slate-700 text-white',
            };

            while ($mulai < $selesai) {
                $this->schedules[$dateKey][$workerId][] = [
                    'start'         => $mulai->format('H:i'),
                    'waktu_mulai'   => $schedule->waktu_mulai,
                    'waktu_selesai' => $schedule->waktu_selesai,
                    'duration'      => $interval,
                    'plat'          => $schedule->plat,
                    'id'            => $schedule->id,
                    'status'        => $schedule->status ?? 'belum dimulai',
                    'colorClass'    => $colorClass,
                    'no_spp'        => $schedule->no_spp,
                ];
                $mulai->addMinutes($interval);
            }
        }
    }

    // EDIT TABLE SCHEDULE
    public function editSchedule($date, $workerId, $time)
    {
        $schedule = Schedule::where([
            ['date', $date],
            ['id_worker', $workerId],
            ['waktu_mulai', $time . ':00'],
        ])->first();

        if ($schedule) {
            $this->editDate = $schedule->date;
            $this->editWorker = $schedule->id_worker;
            $this->editTime = substr($schedule->waktu_mulai, 0, 5);
            $this->durationAsli = $schedule->duration;
            $this->showNoSpp = $schedule->no_spp ?? '';
            $this->showCatatan = $schedule->keterangan ?? '';
            $this->editDuration = 0;
            $this->editStatus = $schedule->status;
            $this->editPlat = $schedule->plat;
            $this->editScheduleId = $schedule->id;
            $this->showModal = true;
            $this->workers = $this->mapWorkerColors(Workers::all());
        }
    }

    public function cancelEditSchedule()
    {
        $this->showModal = false;
        $this->mount();
    }

    // UPDATE TABEL SCHEDULE
    public function updateSchedule()
    {
        $this->validate([
            'editStatus' => 'required|in:belum dimulai,proses,selesai',
            'editDuration' => 'required|integer|min:0',
        ]);

        $schedule = Schedule::find($this->editScheduleId);

        if ($schedule) {
            $waktuSelesaiLama = $schedule->waktu_selesai;
            $waktuBaru = Carbon::createFromFormat('H:i:s', $waktuSelesaiLama)
                ->addMinutes((int)$this->editDuration)
                ->format('H:i:s');

            // Cek overlap dengan jadwal lain (kecuali dirinya sendiri)
            $overlap = Schedule::where('id_worker', $schedule->id_worker)
                ->where('date', $schedule->date)
                ->where('id', '!=', $schedule->id)
                ->where(function($q) use ($schedule, $waktuBaru) {
                    $q->where(function($q2) use ($schedule, $waktuBaru) {
                        $q2->where('waktu_mulai', '<', $waktuBaru)
                        ->where('waktu_selesai', '>', $schedule->waktu_mulai);
                    });
                })
                ->exists();

            if ($overlap) {
                $this->addError('overlap', 'Tidak Bisa Menambah Durasi Karena Sudah Ada Jadwal Lain!');
                return;
            }

            $schedule->plat = $this->editPlat;
            $schedule->keterangan = $this->showCatatan;
            $schedule->status = $this->editStatus;
            $schedule->waktu_selesai = $waktuBaru;
            $schedule->save();
            $schedule->refresh();
            $this->showModal = false;
        }
        $this->workers = $this->mapWorkerColors(Workers::all());
        $this->initTimers();
        $this->loadSchedules();
    }

    // TAMBAH SCHEDULE BARU
    public function tambahSchedule()
    {
        $this->validate([
            'newWorker' => 'required|exists:workers,id',
            'newNoSpp' => 'required|string|max:20',
            'newDate' => 'required|date',
            'newTime' => 'required',
            'newWorktype' => 'required|exists:work_types,id',
            'newPlat' => 'required|string|max:11',
            'newNamaMobil' => 'required|string|max:50',
        ]);

        $worker = Workers::find($this->newWorker);
        $allowedStatuses = ['aktif', 'sedang memperbaiki', 'training'];
        if (!$worker || !in_array(strtolower($worker->status), $allowedStatuses)) {
            $this->addError('newWorker', 'Status pekerja tidak mengizinkan input jadwal.');
            return;
        }

        $worktype = WorkTypes::find($this->newWorktype);

        $waktuMulai = $this->newTime . ':00';
        $durasiMenit = $worktype->flatrate ?? 0;
        $waktuSelesai = date('H:i:s', strtotime($waktuMulai) + $durasiMenit * 60);

        // Cek jam kerja pekerja
        if ($worker && $worker->mulai && $worker->selesai) {
            $jamMulaiWorker = strlen($worker->mulai) === 5 ? $worker->mulai . ':00' : $worker->mulai;
            $jamSelesaiWorker = strlen($worker->selesai) === 5 ? $worker->selesai . ':00' : $worker->selesai;

            if (
                strtotime($waktuMulai) < strtotime($jamMulaiWorker) ||
                strtotime($waktuSelesai) > strtotime($jamSelesaiWorker)
            ) {
                $this->addError('overlap', 'Jadwal di luar jam kerja pekerja! ('.substr($worker->mulai,0,5).' - '.substr($worker->selesai,0,5).')');
                return;
            }
        }

        // Cek waktu istirahat
        $jamMulaiWorker = strlen($worker->mulai) === 5 ? $worker->mulai . ':00' : $worker->mulai;
        $break = $this->breakTimes[$jamMulaiWorker] ?? null;
        if ($break) {
            $breakStart = $break[0];
            $breakEnd = $break[1];
            if (
                (strtotime($waktuMulai) < strtotime($breakEnd)) &&
                (strtotime($waktuSelesai) > strtotime($breakStart))
            ) {
                $this->addError('overlap', 'Tidak bisa menambah jadwal pada jam istirahat shift!');
                return;
            }
        }

        // Cek overlap
        $overlap = Schedule::where('id_worker', $this->newWorker)
            ->where('date', $this->newDate)
            ->where(function($q) use ($waktuMulai, $waktuSelesai) {
                $q->where(function($q2) use ($waktuMulai, $waktuSelesai) {
                    $q2->where('waktu_mulai', '<', $waktuSelesai)
                        ->where('waktu_selesai', '>', $waktuMulai);
                });
            })
            ->exists();

        if ($overlap) {
            $this->addError('overlap', 'Jadwal bentrok dengan jadwal lain untuk teknisi ini!');
            return;
        }

        Schedule::create([
            'id_worker'     => $this->newWorker,
            'date'          => $this->newDate,
            'no_spp'        => $this->newNoSpp,
            'waktu_mulai'   => $waktuMulai,
            'waktu_selesai' => $waktuSelesai,
            'duration'      => $this->newWorktype,
            'plat'          => $this->newPlat,
            'keterangan'    => $this->newKeterangan,
            'id_worktype'   => $this->newWorktype,
            'nama_mobil'    => $this->newNamaMobil,
        ]);
        $this->reset(['newWorker', 'newDate','newNoSpp', 'newTime','newKeterangan','newWorktype', 'newPlat','newNamaMobil']);
        $this->workers = $this->mapWorkerColors(Workers::all());
        $this->initTimers();
        $this->loadSchedules();
    }

    // TIMER
    public function startTimer($scheduleId)
    {
        $now = now()->timestamp;
        $this->timers[$scheduleId] = [
            'start' => $now,
            'value' => 0,
        ];

        $schedule = Schedule::find($scheduleId);
        if ($schedule) {
            $schedule->status = 'proses';
            $schedule->timer = $now;
            $schedule->save();

            // Update status pekerja menjadi "sedang memperbaiki"
            $worker = Workers::find($schedule->id_worker);
            if ($worker) {
                $worker->status = 'sedang memperbaiki';
                $worker->save();
            }
        }
        $this->showModal = false;
        $this->workers = $this->mapWorkerColors(Workers::all());
        $this->initTimers();
        $this->loadSchedules();
    }

    public function stopTimer($scheduleId)
    {
        $schedule = Schedule::find($scheduleId);
        if ($schedule && $schedule->status === 'proses') {
            $start = $schedule->timer;
            $elapsed = now()->timestamp - $start;
            $schedule->timer = $elapsed;
            $schedule->status = 'selesai';
            $schedule->save();
            $schedule->refresh();

            // Update status pekerja menjadi "aktif"
            $worker = Workers::find($schedule->id_worker);
            if ($worker) {
                $worker->status = 'aktif';
                $worker->save();
            }
        }
        unset($this->timers[$scheduleId]);
        $this->showModal = false;
        $this->workers = $this->mapWorkerColors(Workers::all());
        $this->initTimers();
        $this->loadSchedules();
    }

    public function updateTimer()
    {
        foreach ($this->timers as $id => $timer) {
            $elapsed = now()->timestamp - $timer['start'];
            $this->timers[$id]['value'] = $elapsed;
        }
        $this->workers = $this->mapWorkerColors(Workers::all());
        $this->initTimers();
        $this->loadSchedules();
    }

    // EXPORT TO EXCEL
    public function exportExcel()
    {
        return Excel::download(new SchedulesExport, 'jadwal.xlsx');
    }

    // EDIT WORKER
    public function showEditWorker($workerId)
    {
        $worker = Workers::find($workerId);
        if ($worker) {
            $this->editWorkerId = $worker->id;
            $this->editWorkerNama = $worker->nama;
            $this->editWorkerStatus = $worker->status;
            // $this->editWorkerShift = $worker->mulai === '08:00:00' ? 1 : 2;
            $this->editMulai = $worker->mulai;
            $this->workers = $this->mapWorkerColors(Workers::all());
        }
    }

    public function updateWorker()
    {
        $worker = Workers::find($this->editWorkerId);
        if ($worker) {
            $worker->nama = $this->editWorkerNama;
            $worker->status = $this->editWorkerStatus;
            $worker->mulai = $this->editMulai;
            // Konversi angka shift ke waktu
            // if ($this->editWorkerShift == 1) {
            //     $worker->mulai = '08:00:00';
            //     $worker->selesai = '16:00:00';
            // };
            // if ($this->editWorkerShift == 2) {
            //     $worker->mulai = '09:00:00';
            //     $worker->selesai = '17:00:00';
            // } 
            if ($this->editMulai == '09:00:00') {
                $worker->mulai = '09:00:00';
                $worker->selesai = '17:00:00';
            }
            elseif ($this->editMulai == "08:00:00"){
                $worker->mulai = '08:00:00';
                $worker->selesai = '16:00:00';
            } 
            $worker->save();
        }
        // dd($this->editWorkerId);
        $this->editWorkerId = null;
        $this->editWorkerNama = '';
        $this->editWorkerStatus = '';
        $this->editWorkerShift = '';
        $this->editMulai = '';
        $this->workers = $this->mapWorkerColors(Workers::all());
        $this->initTimers();
        $this->loadSchedules();
        $this->dispatch('refresh-page');
        // $this->mount();
    }

    public function cancelEditWorker()
    {
        $this->editWorkerId = null;
        $this->editWorkerNama = '';
        $this->editWorkerStatus = '';
        $this->editWorkerShift = '';
        // $this->loadSchedules();
        $this->workers = $this->mapWorkerColors(Workers::all());
    }

    public function render()
    {
        
        return view('livewire.schedule-board');
    }
    
}
