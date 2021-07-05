<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'email' => 'admin@test.com',
                'name' => 'Admin',
                'user_name' => 'admin',
                'password' => Hash::make('password123'),
                'user_role' => 'admin',
                'registered_at' => now(),
                'is_verified' => 1,
            ],
            [
                'email' => 'user@test.com',
                'name' => 'User',
                'user_name' => 'user',
                'password' => Hash::make('password123'),
                'user_role' => 'user',
                'registered_at' => now(),
                'is_verified' => 1,
            ]
        ];


        foreach ($users as $key => $user) {
            if(User::where('email', $user['email'])->count() == 0){
                User::create($user);
            }
        }
    }
}
