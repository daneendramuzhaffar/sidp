<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkTypes extends Model
{
    /** @use HasFactory<\Database\Factories\WorkTypesFactory> */
    use HasFactory;

    protected $fillable = [
        'nama_pekerjaan',
        'flatrate',
    ];
    
    public $timestamps = false;
    // Relasi ke Schedule
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'duration');
    }
}
