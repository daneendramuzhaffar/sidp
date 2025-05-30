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
    public $schedules = [];
    public $worktypes = [];
    public $showModal = false;
    public $timerScheduleId = null;
    public $timerStart = null;
    public $timerValue = 0;
    public $editScheduleId;
    

    // Untuk edit
    public $editDate, $editWorker, $editTime, $editDuration, $editPlat;

    public function mount()
    {
        $today = date('Y-m-d');
        $this->dates = [$today];

        // Ambil semua pekerja yang punya jadwal hari ini
        $this->workers = Workers::whereHas('schedules', function($q) use ($today) {
            $q->where('date', $today);
        })->get();

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
            $this->editPlat = $schedule->plat;
            $this->editScheduleId = $schedule->id;
            $this->showModal = true;
        }
    }

    public function updateSchedule()
    {
        $schedule = Schedule::where('date', $this->editDate)
            ->where('id_worker', $this->editWorker)
            ->where('waktu_mulai', $this->editTime . ':00')
            ->first();

        if ($schedule) {
            $schedule->duration = $this->editDuration; // id worktype baru
            $schedule->plat = $this->editPlat;
            $schedule->save();
        }

        $this->mount();
        $this->reset(['editDate', 'editWorker', 'editTime', 'editDuration', 'editPlat', 'showModal']);
    }

    public function render()
    {
        return view('livewire.schedule-board');
    }

    public function startTimer($scheduleId)
    {
        $this->timerScheduleId = $scheduleId;
        $this->timerStart = time();
        $this->timerValue = 0;

        // Update status ke 'proses'
        $schedule = Schedule::find($scheduleId);
        if ($schedule) {
            $schedule->status = 'proses';
            $schedule->save();
        }
    }

    public function stopTimer()
    {
    if ($this->timerScheduleId) {
        $elapsed = $this->timerValue;
        $schedule = Schedule::find($this->timerScheduleId);
        if ($schedule) {
            $schedule->timer = $elapsed;
            $schedule->status = 'selesai'; // Update status ke selesai
            $schedule->save();
        }
        $this->timerScheduleId = null;
        $this->timerStart = null;
        $this->timerValue = 0;
        $this->mount();
    }
}

    public function updateTimer()
    {
        if ($this->timerStart) {
            $this->timerValue = time() - $this->timerStart;
        }
    }
}