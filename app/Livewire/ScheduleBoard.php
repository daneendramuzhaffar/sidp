<?php

namespace App\Livewire;

use App\Models\Schedule;
use App\Models\Workers;
use App\Models\WorkTypes;
use Livewire\Component;

class ScheduleBoard extends Component
{
    public $dates = [];
    public $workers = [];
    public $times = [];
    public $statuses = [];
    public $schedules = [];
    public $worktypes = [];
    public $showModal = false;

    // Untuk edit
    public $editDate, $editWorker, $editTime, $editDuration, $editPlat, $editStatus;
    public $newWorker, $newDate, $newTime, $newWorktype, $newPlat, $newNoSpp, $newKeterangan;

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
        $this->schedules = [];
        foreach ($schedules as $schedule) {
            $date = $schedule->date;
            $workerId = $schedule->id_worker;
            if (!isset($this->schedules[$date])) $this->schedules[$date] = [];
            if (!isset($this->schedules[$date][$workerId])) $this->schedules[$date][$workerId] = [];
            $this->schedules[$date][$workerId][] = [
                'start'    => substr($schedule->waktu_mulai, 0, 5),
                'duration' => $schedule->worktype->flatrate ?? 0,
                'plat'     => $schedule->plat,
                'id'       => $schedule->id,
            ];
        }
    }

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
            $this->editDuration = $schedule->duration; // id worktype
            $this->editStatus = $schedule->status;
            $this->editPlat = $schedule->plat;
            $this->showModal = true;
        }
    }

    public function updateSchedule()
    {
        $this->validate([
        'editStatus' => 'required|in:belum dimulai,proses,selesai',
        ]);
        $schedule = Schedule::where('date', $this->editDate)
            ->where('id_worker', $this->editWorker)
            ->where('waktu_mulai', $this->editTime . ':00')
            ->first();

        if ($schedule) {
            $schedule->duration = $this->editDuration; // id worktype baru
            $schedule->plat = $this->editPlat;
            $schedule->status = $this->editStatus; // update status jika perlu
            $schedule->save();
        }

        $this->mount();
        $this->reset(['editDate', 'editWorker', 'editTime', 'editDuration','editStatus', 'editPlat', 'showModal']);
    }

    public function tambahSchedule()
    {
        $this->validate([
            'newWorker' => 'required|exists:workers,id',
            'newNoSpp' => 'required|string|max:20',
            'newDate' => 'required|date',
            'newTime' => 'required',
            'newWorktype' => 'required|exists:work_types,id',
            'newPlat' => 'required|string|max:11',
        ]);

        $worktype = WorkTypes::find($this->newWorktype);

        $waktuMulai = $this->newTime . ':00';
        $durasiMenit = $worktype->flatrate ?? 0;
        $waktuSelesai = date('H:i:s', strtotime($waktuMulai) + $durasiMenit * 60);

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
        ]);

        //reset input form 
        $this->reset(['newWorker', 'newDate','newNoSpp', 'newTime','newKeterangan','newWorktype', 'newPlat']);

        // Refresh data
        $this->mount();
    }

    public function render()
    {
        return view('livewire.schedule-board');
    }
}