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
     *
     * Memakai updateOrCreate (bukan factory) supaya:
     * - Tidak butuh Faker, jadi aman dijalankan di produksi (composer --no-dev).
     * - Idempoten: bisa dijalankan ulang tanpa menduplikasi user.
     */
    public function run(): void
    {
        // Admin — bisa akses panel /admin (role 'admin').
        User::updateOrCreate(
            ['email' => 'admin@lapakgimid.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // User biasa untuk testing marketplace.
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'role' => 'user',
                'email_verified_at' => now(),
            ]
        );
    }
}
