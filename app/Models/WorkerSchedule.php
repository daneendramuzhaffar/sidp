<?php

namespace App\Models;

use App\Models\Workers;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Model;

class WorkerSchedule extends Model
{
    protected $table = 'worker_schedules';

    protected $fillable = [
        'schedule_id',
        'worker_id',
    ];

    public function schedules()
    {
        return $this->belongsToMany(Schedule::class);
    }

    public function workers()
    {
        return $this->belongsToMany(Workers::class, 'worker_id');
    }
}
