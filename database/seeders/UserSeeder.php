<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name'              => 'Admin Doxie',
            'email'             => 'admin@doxie.ai',
            'password'          => Hash::make('password'),
        ]);
 
        User::create([
            'name'              => 'Test',
            'email'             => 'test@doxie.ai',
            'password'          => Hash::make('password'),
        ]);
 
        User::create([
            'name'              => 'Jane Doe',
            'email'             => 'jane@example.com',
            'password'          => Hash::make('password'),
        ]);
    }
}
