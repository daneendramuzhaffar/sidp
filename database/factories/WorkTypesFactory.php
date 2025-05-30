<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkTypes>
 */
class WorkTypesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = [
            ['nama_pekerjaan' => 'Ganti Oli', 'flatrate' => 15],
            ['nama_pekerjaan' => 'Servis Ringan', 'flatrate' => 30],
            ['nama_pekerjaan' => 'Servis Besar', 'flatrate' => 60],
            ['nama_pekerjaan' => 'Tune Up', 'flatrate' => 45],
            ['nama_pekerjaan' => 'Ganti Ban', 'flatrate' => 20],
            ['nama_pekerjaan' => 'Ganti Kampas Rem', 'flatrate' => 25],
            ['nama_pekerjaan' => 'Cuci Motor', 'flatrate' => 10],
            ['nama_pekerjaan' => 'Ganti Busi', 'flatrate' => 10],
        ];
        $type = $this->faker->randomElement($types);

        return [
            'nama_pekerjaan' => $type['nama_pekerjaan'],
            'flatrate' => $type['flatrate'],
        ];
    }
}
