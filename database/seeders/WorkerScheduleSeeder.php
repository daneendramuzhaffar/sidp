<?php

namespace Database\Seeders;

use App\Models\WorkTypes;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class WorkerScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Contoh: Hubungkan schedule id 1 ke worker id 1 dan 2
        DB::table('worker_schedules')->insert([
            [
                'schedule_id' => 1,
                'worker_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'schedule_id' => 1,
                'worker_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
