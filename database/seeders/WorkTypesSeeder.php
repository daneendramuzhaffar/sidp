<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class WorkTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('work_types')->insert([
            [
                'nama_pekerjaan' => 'Ganti Oli',
                'flatrate' => 15,
            ],
            [
                'nama_pekerjaan' => 'Servis Ringan',
                'flatrate' => 30,
            ],
            [
                'nama_pekerjaan' => 'Servis Besar',
                'flatrate' => 60,
            ],
            [
                'nama_pekerjaan' => 'Tune Up',
                'flatrate' => 45,
            ],
            [
                'nama_pekerjaan' => 'Ganti Ban',
                'flatrate' => 20,
            ],
            [
                'nama_pekerjaan' => 'Ganti Kampas Rem',
                'flatrate' => 25,
            ],
            [
                'nama_pekerjaan' => 'Cuci Motor',
                'flatrate' => 10,
            ],
            [
                'nama_pekerjaan' => 'Ganti Busi',
                'flatrate' => 10,
            ],
        ]);
    }
}
