<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Default Admin User
        User::updateOrCreate(
            ['email' => 'admin@zadex.com'],
            [
                'name' => 'Zadex Admin',
                'password' => Hash::make('zadex@2026'),
            ]
        );

        $this->call([
            TicketSeeder::class,
        ]);
    }
}
