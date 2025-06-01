<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
        'name' => 'admin1',
        'email' => 'admin1@gmail.com',
        'password' => Hash::make('asdqwert1234'),
        ]);
        
        User::factory()->create([
            'name' => 'admin2',
            'email' => 'admin2@gmail.com',
            'password' => Hash::make('asdqwert1234'),
        ]);

        // User::factory()->create([
        //     'name' => 'cici',
        //     'email' => 'cici@example.com',
        //     'password' => Hash::make('12341234'),
        // ]);

        // User::factory()->create([
        //     'name' => 'dedi',
        //     'email' => 'dedi@example.com',
        //     'password' => Hash::make('12341234'),
        // ]);

        // User::factory()->create([
        //     'name' => 'eka',
        //     'email' => 'eka@example.com',
        //     'password' => Hash::make('12341234'),
        // ]);

        // $this->call([
        //     WorkerSeeder::class,
        //     WorkTypesSeeder::class,
        //     ScheduleSeeder::class,
        //     // Add other seeders here if needed
        // ]);
    }
}
