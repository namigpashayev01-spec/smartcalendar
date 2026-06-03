<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Admin',  'username' => 'admin', 'password' => 'admin123',  'role' => 'admin'],
            ['name' => 'Nigar',  'username' => 'nigar', 'password' => 'nigar123',  'role' => 'editor'],
            ['name' => 'Murad',  'username' => 'murad', 'password' => 'murad123',  'role' => 'editor'],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(
                ['username' => $u['username']],
                ['name' => $u['name'], 'password' => Hash::make($u['password']), 'role' => $u['role'], 'is_active' => true]
            );
        }
    }
}
