<?php

namespace Database\Factories;

use App\Models\Workers;
use App\Models\WorkTypes;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Queue\Worker;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Schedule>
 */
class ScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $worker = Workers::inRandomOrder()->first();
        $worktype = WorkTypes::inRandomOrder()->first();

        // Slot waktu mulai (08:00, 08:15, ..., 17:45)
        $hours = range(8, 17);
        $minutes = ['00', '15', '30', '45'];
        $slots = [];
        foreach ($hours as $h) {
            foreach ($minutes as $m) {
                $slots[] = sprintf('%02d:%s:00', $h, $m);
            }
        }
        $waktu_mulai = $this->faker->randomElement($slots);

        // Durasi dari worktype (dalam menit)
        $flatrate = $worktype ? $worktype->flatrate : 15; // default 15 menit

        // Hitung waktu selesai
        $mulai = \Carbon\Carbon::createFromFormat('H:i:s', $waktu_mulai);
        $waktu_selesai = $mulai->copy()->addMinutes($flatrate)->format('H:i:s');

        $statuses = ['belum dimulai', 'proses', 'selesai'];

        return [
            'no_spp'        => $this->faker->unique()->bothify('SPP-####'),
            'date'          => $this->faker->dateTimeBetween('-3 days', '+3 days')->format('Y-m-d'),
            'id_worker'     => $worker ? $worker->id : null,
            'duration'      => $worktype ? $worktype->id : null, // foreign key ke worktypes
            'plat'          => strtoupper($this->faker->randomLetter) . '-' . $this->faker->numberBetween(1000,9999) . '-' . strtoupper($this->faker->lexify('???')),
            'waktu_mulai'   => $waktu_mulai,
            'waktu_selesai' => $waktu_selesai,
            'status'        => $statuses[0],
            'keterangan'    => $this->faker->optional()->sentence(),
        ];
    }
}
