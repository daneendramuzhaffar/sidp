<?php

namespace App\Models;

use App\Models\Schedule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Workers extends Model
{
    /** @use HasFactory<\Database\Factories\WorkersFactory> */
    use HasFactory;
    protected $fillable = [
        'nama',
        'status',
        'mulai',
        'selesai',
    ];

    // Relasi ke Schedule
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'id_worker');
    }
}
