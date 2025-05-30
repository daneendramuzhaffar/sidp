<?php

namespace Database\Seeders;

use App\Models\Workers;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class WorkerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('workers')->insert([
            [
                'nama' => 'Asep',
                'status' => 'aktif',
                'mulai' => '08:00:00',
                'selesai' => '16:00:00',
            ],
            [
                'nama' => 'Budi',
                'status' => 'sedang memperbaiki',
                'mulai' => '09:00:00',
                'selesai' => '17:00:00',
            ],
            [
                'nama' => 'Cici',
                'status' => 'izin',
                'mulai' => '08:00:00',
                'selesai' => '16:00:00',
            ],
            [
                'nama' => 'Dedi',
                'status' => 'sakit',
                'mulai' => '09:00:00',
                'selesai' => '17:00:00',
            ],
            [
                'nama' => 'Eka',
                'status' => 'cuti',
                'mulai' => '08:00:00',
                'selesai' => '16:00:00',
            ],
            [
                'nama' => 'Fajar',
                'status' => 'training',
                'mulai' => '09:00:00',
                'selesai' => '17:00:00',
            ],
            [
                'nama' => 'Gina',
                'status' => 'off',
                'mulai' => '08:00:00',
                'selesai' => '16:00:00',
            ],
            [
                'nama' => 'Hadi',
                'status' => 'aktif',
                'mulai' => '09:00:00',
                'selesai' => '17:00:00',
            ],
            [
                'nama' => 'Ika',
                'status' => 'aktif',
                'mulai' => '08:00:00',
                'selesai' => '16:00:00',
            ],
            [
                'nama' => 'Joko',
                'status' => 'cuti',
                'mulai' => '09:00:00',
                'selesai' => '17:00:00',
            ],
        ]);
    }
}
