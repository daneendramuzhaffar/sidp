<?php

namespace App\Livewire;

use App\Models\Workers;
use Livewire\Component;
use App\Models\Schedule;
use App\Models\WorkTypes;
use App\Exports\SchedulesExport;
use Maatwebsite\Excel\Facades\Excel;

class ScheduleBoard extends Component
{
    public $dates = [];
    public $workers = [];
    public $times = [];
    public $statuses = [];
    public $schedules = [];
    public $worktypes = [];
    public $showModal = false;
    public $timerScheduleId = null;
    public $timerStart = null;
    public $timerValue = 0;
    public $editScheduleId;
    public $timers = [];
    public $editDuration = 0;
    public $durationAsli = 0;
    public $editWorkerId = null;
    public $editWorkerNama = '';
    public $editWorkerStatus = '';

    // Untuk edit
    public $editDate, $editWorker, $editTime, $editPlat, $editStatus,$showNoSpp,$showCatatan;
    public $newWorker, $newDate, $newTime, $newWorktype, $newPlat, $newNoSpp, $newKeterangan,$newNamaMobil;

    public function mount()
    {
        $today = date('Y-m-d');
        $this->dates = [$today];

        // Ambil semua pekerja yang punya jadwal hari ini
        $this->workers = Workers::All();

        // Ambil semua worktypes untuk dropdown edit
        $this->worktypes = WorkTypes::all();

        // Siapkan array times (08:00, 08:15, dst)
        $this->times = collect(range(8, 17))->flatMap(function ($hour) {
            return collect(['00', '15', '30', '45'])->map(fn($m) => sprintf('%02d:%s', $hour, $m));
        })->toArray();

        // Ambil semua schedule hari ini beserta relasi
        $schedules = Schedule::with(['worker', 'worktype'])
            ->where('date', $today)
            ->get();

        // Susun ulang agar mudah dipakai di view
        foreach ($schedules as $schedule) {
            $date = $schedule->date;
            $workerId = $schedule->id_worker;
            $mulai = \Carbon\Carbon::createFromFormat('H:i:s', $schedule->waktu_mulai);
            $selesai = \Carbon\Carbon::createFromFormat('H:i:s', $schedule->waktu_selesai);
            $interval = 15; // menit
            while ($mulai < $selesai) {
                if (!isset($this->schedules[$date])) $this->schedules[$date] = [];
                if (!isset($this->schedules[$date][$workerId])) $this->schedules[$date][$workerId] = [];
                $this->schedules[$date][$workerId][] = [
                    'start'    => $mulai->format('H:i'),
                    'waktu_mulai' => $schedule->waktu_mulai,
                    'waktu_selesai' => $schedule->waktu_selesai,
                    'duration' => $interval,
                    'plat'     => $schedule->plat,
                    'id'       => $schedule->id,
                    'status'   => $schedule->status ?? 'belum dimulai',
                ];
                $mulai->addMinutes($interval);
            }
        }

        foreach (Schedule::where('status', 'proses')->get() as $schedule) {
            $start = $schedule->timer;
            $elapsed = now()->timestamp - $start;
            $this->timers[$schedule->id] = [
                'start' => $start,
                'value' => $elapsed,
            ];
        }
    }

    // EDIT TABLE SCHEDULE
    public function editSchedule($date, $workerId, $time)
    {
        $schedule = Schedule::where('date', $date)
            ->where('id_worker', $workerId)
            ->where('waktu_mulai', $time . ':00')
            ->first();

        if ($schedule) {
            $this->editDate = $schedule->date;
            $this->editWorker = $schedule->id_worker;
            $this->editTime = substr($schedule->waktu_mulai, 0, 5);
            $this->durationAsli = $schedule->duration;
            $this->showNoSpp = $schedule['no_spp'] ?? '';
            $this->showCatatan = $schedule['keterangan'] ?? '';
            $this->editDuration = 0; // id worktype
            $this->editStatus = $schedule->status;
            $this->editPlat = $schedule->plat;
            $this->editScheduleId = $schedule->id;
            $this->showModal = true;
        }
    }


    // UPDATE TABEL SCHEDULE
    public function updateSchedule()
{
    $this->validate([
        'editStatus' => 'required|in:belum dimulai,proses,selesai',
        'editDuration' => 'required|integer|min:15',
    ]);

    // Ambil schedule berdasarkan ID (lebih aman)
    $schedule = Schedule::find($this->editScheduleId);

    if ($schedule) {
        $schedule->plat = $this->editPlat;
        $schedule->status = $this->editStatus;

        // Tambah durasi ke waktu selesai lama
        $waktuSelesaiLama = $schedule->waktu_selesai;
        $waktuBaru = \Carbon\Carbon::createFromFormat('H:i:s', $waktuSelesaiLama)
            ->addMinutes((int)$this->editDuration)
            ->format('H:i:s');
        $schedule->waktu_selesai = $waktuBaru;

        // Optional: update duration jika ingin simpan total durasi baru
        // $schedule->duration = ($schedule->duration ?? 0) + (int)$this->editDuration;

        $schedule->save();
    }
    $this->mount(); 
    return redirect()->route('dashboard');

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

        $worktype = WorkTypes::find($this->newWorktype);

        $waktuMulai = $this->newTime . ':00';
        $durasiMenit = $worktype->flatrate ?? 0;
        $waktuSelesai = date('H:i:s', strtotime($waktuMulai) + $durasiMenit * 60);

        // Cek apakah sudah ada jadwal yang sama (worker, tanggal, plat)
        $existing = Schedule::where('id_worker', $this->newWorker)
            ->where('date', $this->newDate)
            ->where('plat', $this->newPlat)
            ->first();

        if ($existing) {
            // Jika waktu selesai baru lebih besar, update waktu_selesai
            if (strtotime($waktuSelesai) > strtotime($existing->waktu_selesai)) {
                $existing->waktu_selesai = $waktuSelesai;
                $existing->save();
            }
        } else {
            // Jika belum ada, buat entri baru
            Schedule::create([
                'id_worker' => $this->newWorker,
                'date' => $this->newDate,
                'no_spp' => $this->newNoSpp,
                'waktu_mulai' => $waktuMulai,
                'waktu_selesai' => $waktuSelesai,
                'duration' => $this->newWorktype,
                'plat' => $this->newPlat,
                'keterangan' => $this->newKeterangan,
                'id_worktype' => $this->newWorktype,
                'nama_mobil' => $this->newNamaMobil,
            ]);
        }

        //reset input form 
        $this->reset(['newWorker', 'newDate','newNoSpp', 'newTime','newKeterangan','newWorktype', 'newPlat','newNamaMobil']);

        // Refresh page
        $this->mount();
        return redirect()->route('dashboard');
    }

    // RENDER
    public function render()
    {
        return view('livewire.schedule-board');
    }

    // START TIMER
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
            $schedule->timer = $now; // simpan timestamp start
            $schedule->save();
        }
    }

    // STOP TIMER
    public function stopTimer($scheduleId)
    {
        $schedule = Schedule::find($scheduleId);
        if ($schedule && $schedule->status === 'proses') {
            $start = $schedule->timer; // timestamp saat start
            $elapsed = now()->timestamp - $start;
            $schedule->timer = $elapsed; // replace dengan hasil stopwatch (detik)
            $schedule->status = 'selesai';
            $schedule->save();
        }
        unset($this->timers[$scheduleId]);
    }

    // UPDATE TIMER
    public function updateTimer()
    {
        foreach ($this->timers as $id => $timer) {
            $elapsed = now()->timestamp - $timer['start'];
            $this->timers[$id]['value'] = $elapsed;
        }
    }

    // EXPORT TO EXCEL
    public function exportExcel()
    {
        return Excel::download(new SchedulesExport, 'jadwal.xlsx');
    }

    // EDIT WORKER
    public function showEditWorker($workerId)
    {
        $worker = \App\Models\Workers::find($workerId);
        if ($worker) {
            $this->editWorkerId = $worker->id;
            $this->editWorkerNama = $worker->nama;
            $this->editWorkerStatus = $worker->status;
            $this->dispatch('showEditWorkerModal');
        }
    }

    public function updateWorker()
    {
        $worker = \App\Models\Workers::find($this->editWorkerId);
        if ($worker) {
            $worker->nama = $this->editWorkerNama;
            $worker->status = $this->editWorkerStatus;
            $worker->save();
            $this->editWorkerId = null;
            $this->editWorkerNama = '';
            $this->editWorkerStatus = '';
            $this->dispatch('closeEditWorkerModal');
            $this->mount();
        }
    }


}