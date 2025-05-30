<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Workers>
 */
class WorkersFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $names = ['Asep', 'Budi', 'Cici', 'Dedi', 'Eka'];
        $statuses = ['aktif','sedang memperbaiki','izin','sakit','cuti','training','off'];
        $startHour = $this->faker->randomElement([8, 9]);
        $startTime = sprintf('%02d:00:00', $startHour);

        // Tentukan waktu selesai sesuai aturan
        $endHour = $startHour === 8 ? 16 : 17;
        $endTime = sprintf('%02d:00:00', $endHour);

        return [
            'nama' => $this->faker->randomElement($names),
            'status' => $this->faker->randomElement($statuses),
            'mulai' => $startTime,
            'selesai' => $endTime,
        ];
    }
}
