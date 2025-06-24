<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
      public function run(): void
    {
        User::create([
            'name'     => 'agung',
            'username' => 'agung',
            'email'    => 'agung@mail.com',
            'password' => Hash::make('password'),
        ]);

        // Jika kamu juga ingin user dengan nama "rouf":
        User::create([
            'name'     => 'rouf',
            'username' => 'rouf',
            'email'    => 'rouf@mail.com',
            'password' => Hash::make('password'),
        ]);
    }
}
