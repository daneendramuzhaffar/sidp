<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

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
        $workers = ['Asep', 'Budi', 'Cici', 'Dedi', 'Eka'];
        $startHour = $this->faker->numberBetween(8, 16);
        $startMinute = $this->faker->randomElement(['00', '15', '30', '45']);
        $duration = $this->faker->randomElement([15, 30, 45, 60]);
        return [
            'date' => $this->faker->dateTimeBetween('-3 days', '+3 days')->format('Y-m-d'),
            'worker' => $this->faker->randomElement($workers),
            'start' => sprintf('%02d:%s:00', $startHour, $startMinute),
            'duration' => $duration,
            'plat' => strtoupper($this->faker->randomLetter) . '-' . $this->faker->numberBetween(1000,9999) . '-' . strtoupper($this->faker->lexify('???')),
        ];
    }
}
