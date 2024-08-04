<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'User A',
                'email' => 'usera@gmail.com',
                'password' => Hash::make(123123123),
            ],
            [
                'name' => 'User B',
                'email' => 'userb@gmail.com',
                'password' => Hash::make(123123123),
            ]
        ]);
    }
}
