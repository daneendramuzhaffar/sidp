<?php
namespace App\Exports;

use App\Models\Schedule;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SchedulesExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        // Export semua jadwal beserta relasi
        return Schedule::with(['worker', 'worktype'])->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'No. SPP',
            'date',
            'plat',
            'Nama Mobil',
            'Nama Worker',
            'Pekerjaan',
            'Durasi (menit)',
            'Jam Mulai',
            'Estimasi Selesai',
            'Waktu Aktual/Timer',
            'catatan',
            'Status',
        ];
    }

    public function map($schedule): array
    {
        return [
            $schedule->id,
            $schedule->no_spp,
            $schedule->date,
            $schedule->plat,
            $schedule->nama_mobil,
            // Jika relasi worker many-to-one
            $schedule->worker ? $schedule->worker->nama : '',
            $schedule->worktype ? $schedule->worktype->nama_pekerjaan : '',
            $schedule->worktype ? $schedule->worktype->flatrate : '',
            $schedule->waktu_mulai, 
            $schedule->waktu_selesai,
            // $schedule->timer ?? '', // atau ganti dengan field waktu aktual yang sesuai
            gmdate('H:i:s', $schedule->timer) ?? '', // atau ganti dengan field waktu aktual yang sesuai
            $schedule->keterangan,
            $schedule->status,
        ];
    }
}