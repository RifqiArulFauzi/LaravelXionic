<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        user::create([
            'nis' => '11111',
            'nama' => 'admin',
            'password' => 'admin',
            'role' => 'Admin',
            'created_at' => now()
        ]);

        User::factory()->count(50)->create();
    }
}
