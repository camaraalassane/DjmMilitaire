<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'id' => 1,
                'name' => 'Super Administrateur',
                'email' => 'admin@fama.ml',
                'email_verified_at' => null,
                'password' => Hash::make('12345678'),
                'role' => 1,
                'remember_token' => 'Sqp6yeMhhYG4huHp5uqB0x37TLijmu2wYzgKGOOaeOjjo13qBOz0qBiPszwQ',
                'created_at' => Carbon::parse('2026-03-07 02:41:21'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:21')
            ],
            [
                'id' => 2,
                'name' => 'TRAORE',
                'email' => 'ctt@gmail.com',
                'email_verified_at' => null,
                'password' => Hash::make('12345678'),
                'role' => 2,
                'remember_token' => null,
                'created_at' => Carbon::parse('2026-03-17 09:12:35'),
                'updated_at' => Carbon::parse('2026-03-17 09:12:35')
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['id' => $user['id']],
                $user
            );
        }
    }
}