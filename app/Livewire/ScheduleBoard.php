<?php

namespace App\Livewire;

use App\Models\Schedule;
use Livewire\Component;

class ScheduleBoard extends Component
{
     public $dates = [];
    public $workers = ['Asep', 'Budi', 'Cici', 'Dedi', 'Eka'];
    public $times = [];

    public $schedules = [];
    public $showModal = false;
    public $editDate;
    public $editWorker;
    public $editTime;
    public $editDuration;
    public $editPlat;

    public function mount()
    {
        // Generate time dari 08:00 sampai 17:45 per 15 menit
        $this->times = collect(range(8, 17))->flatMap(function ($hour) {
            return collect(['00', '15', '30', '45'])->map(fn($m) => sprintf('%02d:%s', $hour, $m));
        })->toArray();

        // Ambil semua tanggal unik dari tabel schedules
        $this->dates = Schedule::query()
            ->orderBy('date')
            ->pluck('date')
            ->unique()
            ->values()
            ->toArray();

        // Ambil semua data schedules dari tabel schedules
        $this->schedules = [];
        foreach (Schedule::all() as $schedule) {
            $date = $schedule->date;
            $worker = $schedule->worker;
            if (!isset($this->schedules[$date])) $this->schedules[$date] = [];
            if (!isset($this->schedules[$date][$worker])) $this->schedules[$date][$worker] = [];
            $this->schedules[$date][$worker][] = [
                'start' => substr($schedule->start, 0, 5), // format HH:MM
                'duration' => $schedule->duration,
                'plat' => $schedule->plat,
            ];
        }
    }

    public function editSchedule($date, $worker, $time)
    {
        // Ambil schedule dari database
        $schedule = Schedule::where('date', $date)
            ->where('worker', $worker)
            ->where('start', $time . ':00')
            ->first();

        if ($schedule) {
            $this->editDate = $schedule->date;
            $this->editWorker = $schedule->worker;
            $this->editTime = substr($schedule->start, 0, 5);
            $this->editDuration = $schedule->duration;
            $this->editPlat = $schedule->plat;
            $this->showModal = true;
        }
    }

    public function updateSchedule()
    {
        // Update schedule di database
        $schedule = Schedule::where('date', $this->editDate)
            ->where('worker', $this->editWorker)
            ->where('start', $this->editTime . ':00')
            ->first();

        if ($schedule) {
            $schedule->duration = $this->editDuration;
            $schedule->plat = $this->editPlat;
            $schedule->save();
        }

        // Refresh data schedules dari database
        $this->mount();

        $this->showModal = false;
    }


    public function render()
    {
        return view('livewire.schedule-board');
    }
}
