<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@hocky.com'],
            [
                'name'     => 'Admin Hocky',
                'password' => Hash::make('admin123'),
            ]
        );
    }
}
