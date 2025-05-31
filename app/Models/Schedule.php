<?php

namespace App\Models;

use App\Models\Workers;
use App\Models\WorkTypes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Schedule extends Model
{
    /** @use HasFactory<\Database\Factories\ScheduleFactory> */
    use HasFactory;

    protected $fillable = [
        'no_spp',
        'date',
        'id_worker',
        'duration',
        'plat',
        'waktu_mulai',
        'waktu_selesai',
        'status',
        'keterangan',
    ];

    // Relasi ke Workers
    public function worker()
    {
        return $this->belongsTo(Workers::class, 'id_worker');
    }

    public function workers()
    {
        return $this->belongsToMany(Workers::class, 'worker_schedules', 'schedule_id', 'worker_id');
    }

    // Relasi ke WorkTypes
    public function worktype()
    {
        return $this->belongsTo(WorkTypes::class, 'duration');
    }
}
