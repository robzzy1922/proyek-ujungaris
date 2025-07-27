<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        Admin::create([
            'namaAdmin' => 'Robi Permana',
            'nip' => '12345',
            'email' => 'Robipermana@gmail.com',
            'noHp' => '081234567890',
            'password' => Hash::make('12345'),
            'profile' => null
        ]);
    }
}
